<?php

namespace App\Http\Controllers\Vet;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VetAppointmentController extends Controller
{
    private const ALLOWED_ROLES = [
        'veterinarian',
        'vet_nurse',
        'vet_assistant',
        'groomer',
    ];

    // Only veterinarians see their own appointments; other vet-role staff see all clinic appointments
    private const SCOPED_ROLES = ['veterinarian'];

    // Statuses a vet-side user can SET (cancelling is receptionist/admin only)
    private const VET_STATUSES = [
        Appointment::STATUS_SCHEDULED,
        Appointment::STATUS_CONFIRMED,
        Appointment::STATUS_NO_SHOW,
        Appointment::STATUS_COMPLETED,
    ];

    // Valid forward transitions for each current status
    private const STATUS_TRANSITIONS = [
        'scheduled' => [
            Appointment::STATUS_CONFIRMED,
            Appointment::STATUS_NO_SHOW,
            Appointment::STATUS_COMPLETED,
        ],
        'confirmed' => [
            Appointment::STATUS_NO_SHOW,
            Appointment::STATUS_COMPLETED,
        ],
        'no_show'   => [],   // terminal
        'completed' => [],   // terminal
        'canceled'  => [],   // terminal (vet cannot touch cancelled)
        'cancelled' => [],
    ];

    /**
     * Which appointment types each role is allowed to see and update.
     *
     * - veterinarian : clinical work only
     * - groomer      : grooming only
     * - vet_nurse    : vaccination only
     * - vet_assistant: assists everyone → all types
     */
    private const ROLE_TYPE_MAP = [
        'veterinarian'  => ['Checkup', 'Vaccination', 'Surgery'],
        'groomer'       => ['Grooming'],
        'vet_nurse'     => ['Vaccination'],
        'vet_assistant' => ['Checkup', 'Vaccination', 'Surgery', 'Grooming'],
    ];

    // -------------------------------------------------------------------------

    private function requireAllowedRole(): void
    {
        $role = auth()->user()?->role;
        if (! auth()->check() || ! in_array($role, self::ALLOWED_ROLES, true)) {
            abort(403, 'Access denied.');
        }
    }

    /**
     * Return the appointment types this role may access.
     * Returns null if the role has no restriction (shouldn't happen with current map,
     * but acts as a safe fallback so nothing breaks for unknown roles).
     *
     * @return string[]|null
     */
    private function allowedTypesForRole(string $role): ?array
    {
        return self::ROLE_TYPE_MAP[$role] ?? null;
    }

    /**
     * Normalise spelling: "cancelled" (two l's) → "canceled" (one l, matches DB ENUM).
     */
    private function normaliseStatus(string $status): string
    {
        $lower = strtolower(trim($status));
        return $lower === 'cancelled' ? 'canceled' : $lower;
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX  –  GET /vet/appointments
    |--------------------------------------------------------------------------
    | Appointments are filtered by BOTH:
    |   1. Scope  — veterinarians see only their own; other roles see all.
    |   2. Type   — each role may only see the appointment types it handles.
    |
    | Completed appointments are redirected to Reports (mirrors receptionist).
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $this->requireAllowedRole();

        $user = auth()->user();

        // Redirect "completed" filter straight to Reports (mirrors receptionist controller)
        if ($request->input('status') === 'completed') {
            return redirect()->route('vet.reports.index')
                ->with('info', 'Completed appointments are available in Reports.');
        }

        // ── Filters ──────────────────────────────────────────────────────────
        $selectedDate   = $request->input('date');
        $selectedType   = $request->input('type');
        $selectedStatus = $request->input('status', '');

        // Sanitise date
        if ($selectedDate && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate = null;
        }

        // Only these statuses are valid on this page (completed → Reports)
        $allowedStatusFilters = ['scheduled', 'confirmed', 'no_show', 'canceled'];
        if ($selectedStatus && ! in_array($selectedStatus, $allowedStatusFilters, true)) {
            $selectedStatus = '';
        }

        // Role-allowed types for this user
        $roleAllowedTypes = $this->allowedTypesForRole($user->role);

        // Validate that any user-supplied type filter is within their role's allowed types
        if ($selectedType) {
            $validTypes = $roleAllowedTypes ?? Appointment::TYPES;
            if (! in_array($selectedType, $validTypes, true)) {
                $selectedType = null;
            }
        }

        // ── Eloquent query ────────────────────────────────────────────────────
        $query = Appointment::with(['customer', 'pet', 'veterinarian'])
            ->where('status', '!=', 'completed')   // completed → Reports only
            ->orderByDesc('scheduled_date')
            ->orderByDesc('scheduled_time')
            ->orderByDesc('created_at');

        // Veterinarian role: scoped to their own appointments
        if (in_array($user->role, self::SCOPED_ROLES, true)) {
            $query->where('veterinarian_id', $user->id);
        }

        // ── Role-based type restriction ───────────────────────────────────────
        // Each role only sees the appointment types it is responsible for.
        if ($roleAllowedTypes !== null) {
            $query->whereIn('type', $roleAllowedTypes);
        }

        // ── User-supplied filters ─────────────────────────────────────────────
        if ($selectedDate) {
            $query->whereDate('scheduled_date', $selectedDate);
        }

        if ($selectedStatus !== '') {
            $statusFilter = $this->normaliseStatus($selectedStatus);
            $query->whereRaw('LOWER(status) IN (?, ?)', [
                $statusFilter,
                $statusFilter === 'canceled' ? 'cancelled' : $statusFilter,
            ]);
        }

        if ($selectedType) {
            $query->where('type', $selectedType);
        }

        $appointments      = $query->get();
        $totalAppointments = $appointments->count();

        return view('vet.appointments.index', compact(
            'appointments',
            'totalAppointments',
            'selectedDate',
            'selectedStatus',
            'selectedType',
            'user',
            'roleAllowedTypes'   // passed to Blade so the type-filter dropdown is scoped
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW  –  GET /vet/appointments/{appointment}
    | Returns JSON for the View/Details modal (AJAX).
    | Response shape mirrors receptionist controller show() exactly.
    |--------------------------------------------------------------------------
    */
    public function show(Request $request, $appointment)
    {
        $this->requireAllowedRole();

        $user   = auth()->user();
        $record = Appointment::with([
            'customer',
            'pet',
            'veterinarian',
            'statusHistories' => fn ($q) => $q->orderBy('changed_at', 'asc')->orderBy('id', 'asc'),
            'statusHistories.changedBy',
        ])->find($appointment);

        if (! $record) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Appointment not found.'], 404);
            }
            abort(404, 'Appointment not found.');
        }

        // Scope check for veterinarians
        if (
            in_array($user->role, self::SCOPED_ROLES, true)
            && (int) $record->veterinarian_id !== (int) $user->id
        ) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Access denied.'], 403);
            }
            abort(403);
        }

        // Role-type access check: the appointment type must be in the role's allowed list
        $roleAllowedTypes = $this->allowedTypesForRole($user->role);
        if ($roleAllowedTypes !== null && ! in_array($record->type, $roleAllowedTypes, true)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Access denied: this appointment type is outside your role.'], 403);
            }
            abort(403);
        }

        // ── Build status history ──────────────────────────────────────────────
        $statusHistories = $record->statusHistories->map(function ($h) {
            $roleLabel = $h->changedBy
                ? ucwords(str_replace('_', ' ', $h->changedBy->role))
                : 'System';

            return [
                'status'       => $h->status,
                'status_label' => $this->formatStatusLabel($h->status),
                'changed_at'   => $h->changed_at ? $h->changed_at->toIso8601String() : null,
                'changed_by'   => $roleLabel,
            ];
        })->values();

        $currentNorm = $this->normaliseStatus($record->status);
        $allowedNext = self::STATUS_TRANSITIONS[$currentNorm] ?? [];

        return response()->json([
            'id'               => $record->id,
            'scheduled_date'   => $record->scheduled_date
                                    ? $record->scheduled_date->format('M d, Y')
                                    : '—',
            'scheduled_time'   => $record->scheduled_time
                                    ? Carbon::parse($record->scheduled_time)->format('h:i A')
                                    : '—',
            'status'           => $record->status,
            'status_label'     => $this->formatStatusLabel($record->status),
            'type'             => $record->type             ?? '—',
            'reason_for_visit' => $record->reason_for_visit ?? '—',
            'is_locked'        => $record->isLocked(),

            'customer_id'    => $record->customer?->id   ?? '—',
            'customer_name'  => $record->customer
                                    ? trim($record->customer->first_name . ' ' . $record->customer->last_name)
                                    : '—',
            'address'        => $record->customer?->address        ?? '—',
            'contact_number' => $record->customer?->contact_number ?? '—',

            'pet_id'   => $record->pet?->id       ?? '—',
            'pet_name' => $record->pet?->pet_name ?? '—',
            'species'  => $record->pet?->species  ?? '—',
            'breed'    => $record->pet?->breed    ?? '—',
            'gender'   => $record->pet?->gender   ?? '—',
            'color'    => $record->pet?->color    ?? '—',
            'weight'   => $record->pet?->weight   ?? '—',

            'vet_name'   => $record->veterinarian?->name ?? '—',
            'staff_name' => $record->veterinarian?->name ?? '—',

            'status_history'   => $statusHistories,
            'status_histories' => $statusHistories,

            'allowed_next' => $allowedNext,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS  –  PATCH /vet/appointments/{appointment}/status
    |
    | Business rules (mirrors admin & receptionist) plus role-type gate:
    |   • The appointment's type must be within the user's role-allowed types.
    |   • completed → fully locked.
    |   • confirmed → cannot move to canceled.
    |   • no_show   → terminal for vet (cannot move anywhere).
    |   • canceled  → vet staff cannot touch.
    |   • completed → remove_from_list: true (JS fades row out → Reports).
    |--------------------------------------------------------------------------
    */
    public function updateStatus(Request $request, $appointment)
    {
        $this->requireAllowedRole();

        $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', self::VET_STATUSES)],
        ]);

        $record = Appointment::find($appointment);

        if (! $record) {
            return $this->statusError($request, 'Appointment not found.', 404);
        }

        $user = auth()->user();

        // Scope check for veterinarians
        if (
            in_array($user->role, self::SCOPED_ROLES, true)
            && (int) $record->veterinarian_id !== (int) $user->id
        ) {
            return $this->statusError($request, 'Access denied.', 403);
        }

        // ── Role-type gate: user may only update types they are responsible for ──
        $roleAllowedTypes = $this->allowedTypesForRole($user->role);
        if ($roleAllowedTypes !== null && ! in_array($record->type, $roleAllowedTypes, true)) {
            return $this->statusError(
                $request,
                'You are not permitted to update a ' . ($record->type ?? 'this') . ' appointment.',
                403
            );
        }

        $currentNorm = $this->normaliseStatus($record->status);
        $newStatus   = $request->status;
        $allowed     = self::STATUS_TRANSITIONS[$currentNorm] ?? [];

        // Lock: completed appointments cannot be touched
        if ($currentNorm === 'completed') {
            return $this->statusError(
                $request,
                'This appointment is completed and cannot be modified.',
                422
            );
        }

        // Vet staff cannot cancel appointments (receptionist/admin only)
        if (in_array($newStatus, ['canceled', 'cancelled'], true)) {
            return $this->statusError(
                $request,
                'Vet staff are not permitted to cancel appointments. Please ask reception.',
                422
            );
        }

        // no_show → terminal for vet (cannot move anywhere)
        if ($currentNorm === 'no_show') {
            return $this->statusError(
                $request,
                'A no-show appointment cannot be changed further.',
                422
            );
        }

        // Enforce transition rules
        if (! in_array($newStatus, $allowed, true)) {
            return $this->statusError(
                $request,
                $this->transitionErrorMessage($currentNorm, $newStatus),
                422
            );
        }

        $record->status     = $newStatus;
        $record->updated_by = auth()->id();
        $record->save();

        $record->recordStatusHistory(auth()->id());

        $label       = $this->formatStatusLabel($newStatus);
        $isCompleted = strtolower($newStatus) === 'completed';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'          => true,
                'status'           => $newStatus,
                'label'            => $label,
                'remove_from_list' => $isCompleted,
            ]);
        }

        if ($isCompleted) {
            return redirect()->route('vet.appointments.index')
                ->with('success', "Appointment #{$appointment} marked Completed and moved to Reports.");
        }

        return redirect()->route('vet.appointments.index')
            ->with('success', "Appointment #{$appointment} updated to {$label}.");
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function transitionErrorMessage(string $current, string $attempted): string
    {
        if ($current === 'completed') {
            return 'This appointment is already completed and cannot be modified.';
        }
        if ($current === 'no_show') {
            return 'This appointment is marked No Show and cannot be changed further.';
        }
        if ($current === 'confirmed' && $attempted === 'scheduled') {
            return 'A confirmed appointment cannot be reverted to Scheduled.';
        }
        if (in_array($current, ['canceled', 'cancelled'], true)) {
            return 'Cancelled appointments cannot be modified by vet staff.';
        }
        return sprintf(
            'Changing from "%s" to "%s" is not permitted.',
            $this->formatStatusLabel($current),
            $this->formatStatusLabel($attempted)
        );
    }

    private function statusError(Request $request, string $message, int $code = 422)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['error' => $message], $code);
        }
        return back()->with('error', $message);
    }

    private function formatStatusLabel(string $status): string
    {
        return match (strtolower($status)) {
            'no_show'               => 'No Show',
            'canceled', 'cancelled' => 'Cancelled',
            default                 => ucfirst($status),
        };
    }
}