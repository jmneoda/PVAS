<?php

namespace App\Http\Controllers\Vet;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VetDashboardController extends Controller
{
    private const ALLOWED_ROLES = [
        'veterinarian',
        'vet_nurse',
        'vet_assistant',
        'groomer',
    ];

    /**
     * Which appointment types each role is allowed to see and manage.
     * Mirrors VetAppointmentController::ROLE_TYPE_MAP exactly.
     *
     * - veterinarian  → Checkup, Vaccination, Surgery  (clinical work)
     * - groomer       → Grooming only
     * - vet_nurse     → Vaccination only
     * - vet_assistant → All four types
     */
    private const ROLE_TYPE_MAP = [
        'veterinarian'  => ['Checkup', 'Vaccination', 'Surgery'],
        'groomer'       => ['Grooming'],
        'vet_nurse'     => ['Vaccination'],
        'vet_assistant' => ['Checkup', 'Vaccination', 'Surgery', 'Grooming'],
    ];

    /**
     * Return the appointment types this role may access.
     *
     * @return string[]
     */
    private function allowedTypesForRole(string $role): array
    {
        return self::ROLE_TYPE_MAP[$role] ?? ['Checkup', 'Vaccination', 'Surgery', 'Grooming'];
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! in_array($user->role, self::ALLOWED_ROLES)) {
            abort(403, 'Access denied.');
        }

        // ── Role-allowed types for this user ──────────────────────────────
        // ALL stat counts and queries below are restricted to these types
        // so the dashboard numbers always match what the role can actually see.
        $roleAllowedTypes = $this->allowedTypesForRole($user->role);

        // ── Calendar date filter ──────────────────────────────────────────
        $selectedDate = $request->input('date');
        if ($selectedDate && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate = null;
        }

        // ── Base query ────────────────────────────────────────────────────
        // Applied to ALL stat counts so every number on the dashboard
        // reflects only appointments this role is responsible for.
        $base = Appointment::query()
            ->whereIn('type', $roleAllowedTypes);   // ← ROLE-TYPE GATE (applied globally)

        // Veterinarian scope: only their own assigned appointments
        if ($user->role === 'veterinarian') {
            $base->where('veterinarian_id', $user->id);
        }

        // Optional date filter (for historical browsing)
        if ($selectedDate) {
            $base->whereDate('scheduled_date', $selectedDate);
        }

        // ── Stat counts ───────────────────────────────────────────────────
        // Each count inherits the role-type gate and scope from $base.
        $totalAppointments = (clone $base)->count();

        $scheduledCount = (clone $base)
            ->where('status', 'scheduled')
            ->count();

        $confirmedCount = (clone $base)
            ->where('status', 'confirmed')
            ->count();

        $completedCount = (clone $base)
            ->where('status', 'completed')
            ->count();

        $noShowCount = (clone $base)
            ->where('status', 'no_show')
            ->count();

        // DB ENUM stores 'canceled' (one 'l'); guard against legacy two-l rows too
        $canceledCount = (clone $base)
            ->whereRaw('LOWER(status) IN (?, ?)', ['canceled', 'cancelled'])
            ->count();

        // ── Monthly total (always current month, ignores date filter) ─────
        // Role-type gate still applies; date filter does not.
        $now = Carbon::now();

        $monthlyBase = Appointment::query()
            ->whereIn('type', $roleAllowedTypes);   // ← ROLE-TYPE GATE

        if ($user->role === 'veterinarian') {
            $monthlyBase->where('veterinarian_id', $user->id);
        }

        $monthlyAppointments = (clone $monthlyBase)
            ->whereMonth('scheduled_date', $now->month)
            ->whereYear('scheduled_date',  $now->year)
            ->count();

        // ── Today's active appointments (scheduled + confirmed) ───────────
        // Role-type gate still applies.
        $todayAppointments = (clone $monthlyBase)
            ->whereDate('scheduled_date', $now->toDateString())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->count();

        // ── New appointments created in the last 24 hours ─────────────────
        // "Newly assigned by admin or receptionist that this staff member
        //  may not have seen yet." Uses a fresh query (no date filter)
        // so it always reflects the true "new since yesterday" count.
        // Role-type gate still applies.
        $newAppointmentsBase = Appointment::query()
            ->whereIn('type', $roleAllowedTypes);   // ← ROLE-TYPE GATE

        if ($user->role === 'veterinarian') {
            $newAppointmentsBase->where('veterinarian_id', $user->id);
        }

        $newAppointments = (clone $newAppointmentsBase)
            ->where('status', 'scheduled')
            ->where('created_at', '>=', $now->copy()->subHours(24))
            ->count();

        // ── Recent appointments (latest 10, date-filtered) ────────────────
        // Role-type gate still applies via $base.
        $recentAppointments = (clone $base)
            ->with(['customer', 'pet', 'veterinarian'])
            ->orderByDesc('scheduled_date')
            ->orderByDesc('scheduled_time')
            ->limit(10)
            ->get();

        // ── Date label ────────────────────────────────────────────────────
        $dateLabel = $selectedDate
            ? Carbon::parse($selectedDate)->format('F d, Y')
            : 'All Dates';

        // ── Completed → Reports URL ───────────────────────────────────────
        $reportsBase = \Route::has('vet.reports.index') ? route('vet.reports.index') : '#';

        $completedReportsHref = $selectedDate
            ? $reportsBase . '?' . http_build_query([
                'date_filter' => 'custom',
                'custom_date' => $selectedDate,
              ])
            : $reportsBase;

        // ── Role display info ─────────────────────────────────────────────
        $roleLabels = [
            'veterinarian'  => 'Veterinarian',
            'vet_nurse'     => 'Vet Nurse',
            'vet_assistant' => 'Vet Assistant',
            'groomer'       => 'Groomer',
        ];

        $roleColors = [
            'veterinarian'  => ['bg' => '#e8f5e9', 'border' => '#81c784', 'text' => '#1b5e20', 'dot' => '#27ae60'],
            'vet_nurse'     => ['bg' => '#fff8e1', 'border' => '#ffe082', 'text' => '#6d4c00', 'dot' => '#f9a825'],
            'vet_assistant' => ['bg' => '#f3e5f5', 'border' => '#ce93d8', 'text' => '#4a148c', 'dot' => '#8e24aa'],
            'groomer'       => ['bg' => '#fce4ec', 'border' => '#f48fb1', 'text' => '#880e4f', 'dot' => '#e91e63'],
        ];

        $roleLabel = $roleLabels[$user->role] ?? ucfirst($user->role);
        $roleColor = $roleColors[$user->role]  ?? $roleColors['veterinarian'];
        $userName  = $user->name;

        return view('vet.dashboard', compact(
            'selectedDate',
            'dateLabel',
            'totalAppointments',
            'scheduledCount',
            'confirmedCount',
            'completedCount',
            'noShowCount',
            'canceledCount',
            'monthlyAppointments',
            'todayAppointments',
            'newAppointments',
            'recentAppointments',
            'completedReportsHref',
            'userName',
            'roleLabel',
            'roleColor',
            'roleAllowedTypes',     // passed to Blade for the role-scope info banner
        ));
    }
}