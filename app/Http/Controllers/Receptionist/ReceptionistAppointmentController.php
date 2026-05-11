<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReceptionistAppointmentController extends Controller
{
    /* ──────────────────────────────────────────────────────────
       INDEX  –  GET /receptionist/appointments
       Completed appointments are EXCLUDED here — they live in
       Reports only (same rule as admin AppointmentController).
    ────────────────────────────────────────────────────────── */
    public function index(Request $request)
    {
        // Redirect "completed" filter to Reports (mirrors admin behaviour)
        if ($request->input('status') === 'completed') {
            return redirect()
                ->route('receptionist.reports.index')
                ->with('info', 'Completed appointments are available in Reports.');
        }

        $selectedStatus = $request->input('status');
        $selectedDate   = $request->input('date');

        // Sanitise date
        if ($selectedDate && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate = null;
        }

        // Only these statuses are valid on this page
        $allowedStatuses = ['scheduled', 'confirmed', 'no_show', 'canceled'];
        if ($selectedStatus && ! in_array($selectedStatus, $allowedStatuses)) {
            $selectedStatus = null;
        }

        $query = Appointment::with(['customer', 'pet', 'veterinarian'])
            ->where('status', '!=', 'completed')
            ->orderByDesc('scheduled_date')
            ->orderByDesc('scheduled_time');

        if ($selectedStatus) {
            $query->where('status', $selectedStatus);
        }

        if ($selectedDate) {
            $query->whereDate('scheduled_date', $selectedDate);
        }

        $appointments      = $query->get();
        $totalAppointments = $appointments->count();

        $customers = Customer::orderBy('first_name')->get();
        $staffList = User::whereIn('role', [
                        'veterinarian', 'vet_nurse', 'vet_assistant', 'groomer', 'staff',
                     ])
                     ->where('is_active', 1)
                     ->orderBy('name')
                     ->get();

        return view('receptionist.appointments.index', compact(
            'appointments', 'totalAppointments',
            'selectedStatus', 'selectedDate',
            'customers', 'staffList'
        ));
    }

    /* ──────────────────────────────────────────────────────────
       STORE  –  POST /receptionist/appointments
    ────────────────────────────────────────────────────────── */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'pet_name'         => 'required|string|max:255',
            'species'          => 'required|in:Dog,Cat,Bird,Rabbit,Hamster,Other',
            'breed'            => 'nullable|string|max:255',
            'gender'           => 'nullable|in:Male,Female',
            'color'            => 'nullable|string|max:255',
            'weight'           => 'nullable|numeric|min:0|max:999.99',
            'veterinarian_id'  => 'required|exists:users,id',
            'scheduled_date'   => 'required|date',
            'scheduled_time'   => 'required',
            'type'             => 'nullable|in:Checkup,Vaccination,Surgery,Grooming',
            'reason_for_visit' => 'nullable|string',
        ]);

        $pet = Pet::create([
            'customer_id' => $validated['customer_id'],
            'pet_name'    => $validated['pet_name'],
            'species'     => $validated['species'],
            'breed'       => $validated['breed']  ?? null,
            'gender'      => $validated['gender'] ?? null,
            'color'       => $validated['color']  ?? null,
            'weight'      => $validated['weight'] ?? null,
        ]);

        $appointment = Appointment::create([
            'customer_id'      => $validated['customer_id'],
            'pet_id'           => $pet->id,
            'veterinarian_id'  => $validated['veterinarian_id'],
            'scheduled_date'   => $validated['scheduled_date'],
            'scheduled_time'   => $validated['scheduled_time'],
            'type'             => $validated['type'] ?? null,
            'reason_for_visit' => $validated['reason_for_visit'] ?? null,
            'status'           => 'scheduled',
            'created_by'       => Auth::id(),
            'updated_by'       => Auth::id(),
        ]);

        // Record initial status history
        $appointment->recordStatusHistory(Auth::id());

        return redirect()
            ->route('receptionist.appointments.index')
            ->with('success', 'Appointment created successfully.');
    }

    /* ──────────────────────────────────────────────────────────
       SHOW  –  GET /receptionist/appointments/{appointment}
       Returns JSON for the View modal (AJAX).

       History "changed_by" now returns the role label
       (e.g. "Receptionist", "Admin") — same as admin controller.
    ────────────────────────────────────────────────────────── */
    public function show(Request $request, Appointment $appointment)
    {
        $appointment->load([
            'customer',
            'pet',
            'veterinarian',
            'statusHistories' => fn ($q) => $q->orderBy('changed_at', 'asc')->orderBy('id', 'asc'),
            'statusHistories.changedBy',
        ]);

        $statusHistories = $appointment->statusHistories->map(function ($h) {
            // Return role label, not personal name (mirrors admin controller)
            $roleLabel = $h->changedBy
                ? ucwords(str_replace('_', ' ', $h->changedBy->role))
                : 'System';

            return [
                'status'       => $h->status,
                'status_label' => $this->formatStatusLabel($h->status),
                'changed_at'   => $h->changed_at ? $h->changed_at->toIso8601String() : null,
                // JS key "changed_by" carries the role label
                'changed_by'   => $roleLabel,
            ];
        })->values();

        return response()->json([
            'id'               => $appointment->id,
            'scheduled_date'   => $appointment->scheduled_date
                                    ? $appointment->scheduled_date->format('M d, Y')
                                    : '—',
            'scheduled_time'   => $appointment->scheduled_time
                                    ? Carbon::parse($appointment->scheduled_time)->format('h:i A')
                                    : '—',
            'type'             => $appointment->type ?? '—',
            'status'           => $appointment->status,
            'status_label'     => $this->formatStatusLabel($appointment->status),
            'reason_for_visit' => $appointment->reason_for_visit ?? '—',
            'is_locked'        => $appointment->isLocked(),

            'customer_id'    => $appointment->customer?->id   ?? '—',
            'customer_name'  => $appointment->customer
                                    ? $appointment->customer->first_name . ' ' . $appointment->customer->last_name
                                    : '—',
            'address'        => $appointment->customer?->address        ?? '—',
            'contact_number' => $appointment->customer?->contact_number ?? '—',

            'pet_id'   => $appointment->pet?->id       ?? '—',
            'pet_name' => $appointment->pet?->pet_name ?? '—',
            'species'  => $appointment->pet?->species  ?? '—',
            'breed'    => $appointment->pet?->breed    ?? '—',
            'gender'   => $appointment->pet?->gender   ?? '—',
            'color'    => $appointment->pet?->color    ?? '—',
            'weight'   => $appointment->pet?->weight   ?? '—',

            'vet_name'   => $appointment->veterinarian?->name ?? '—',
            'staff_name' => $appointment->veterinarian?->name ?? '—',

            // Full history — consumed by buildModalContent() in JS
            'status_history'   => $statusHistories,
            'status_histories' => $statusHistories,
        ]);
    }

    /* ──────────────────────────────────────────────────────────
       UPDATE STATUS  –  PATCH /receptionist/appointments/{id}/status

       Business rules (mirrors admin):
         • Completed → fully locked.
         • Confirmed → cannot move to Canceled.
         • When set to Completed → remove_from_list: true so JS
           fades the row out without a page reload.
    ────────────────────────────────────────────────────────── */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:scheduled,confirmed,completed,no_show,canceled',
        ]);

        // Lock: completed cannot be touched
        if (strtolower($appointment->status) === 'completed') {
            return response()->json([
                'error' => 'This appointment is completed and cannot be modified.',
            ], 422);
        }

        // Rule: confirmed → canceled is not allowed
        if (strtolower($appointment->status) === 'confirmed' && $request->status === 'canceled') {
            return response()->json([
                'error' => 'A confirmed appointment cannot be cancelled.',
            ], 422);
        }

        // Rule: no_show → only canceled is allowed
        if (strtolower($appointment->status) === 'no_show' && $request->status !== 'canceled') {
            return response()->json([
                'error' => 'A no-show appointment can only be moved to Cancelled.',
            ], 422);
        }

        $newStatus = $request->status;

        $appointment->update([
            'status'     => $newStatus,
            'updated_by' => Auth::id(),
        ]);

        $appointment->recordStatusHistory(Auth::id());

        $label       = $this->formatStatusLabel($newStatus);
        $isCompleted = strtolower($newStatus) === 'completed';

        return response()->json([
            'success'          => true,
            'status'           => $newStatus,
            'label'            => $label,
            // JS uses this key to animate row removal (same as admin)
            'remove_from_list' => $isCompleted,
        ]);
    }

    /* ──────────────────────────────────────────────────────────
       UPDATE  –  PUT /receptionist/appointments/{appointment}
    ────────────────────────────────────────────────────────── */
    public function update(Request $request, Appointment $appointment)
    {
        if ($appointment->isLocked()) {
            return redirect()
                ->route('receptionist.appointments.index')
                ->with('error', 'Completed appointments cannot be edited.');
        }

        $validated = $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'pet_name'         => 'required|string|max:255',
            'species'          => 'required|in:Dog,Cat,Bird,Rabbit,Hamster,Other',
            'breed'            => 'nullable|string|max:255',
            'gender'           => 'nullable|in:Male,Female',
            'color'            => 'nullable|string|max:255',
            'weight'           => 'nullable|numeric|min:0|max:999.99',
            'veterinarian_id'  => 'required|exists:users,id',
            'scheduled_date'   => 'required|date',
            'scheduled_time'   => 'required',
            'type'             => 'nullable|in:Checkup,Vaccination,Surgery,Grooming',
            'reason_for_visit' => 'nullable|string',
        ]);

        if ($appointment->pet) {
            $appointment->pet->update([
                'customer_id' => $validated['customer_id'],
                'pet_name'    => $validated['pet_name'],
                'species'     => $validated['species'],
                'breed'       => $validated['breed']  ?? null,
                'gender'      => $validated['gender'] ?? null,
                'color'       => $validated['color']  ?? null,
                'weight'      => $validated['weight'] ?? null,
            ]);
        }

        $appointment->update([
            'customer_id'      => $validated['customer_id'],
            'veterinarian_id'  => $validated['veterinarian_id'],
            'scheduled_date'   => $validated['scheduled_date'],
            'scheduled_time'   => $validated['scheduled_time'],
            'type'             => $validated['type'] ?? null,
            'reason_for_visit' => $validated['reason_for_visit'] ?? null,
            'updated_by'       => Auth::id(),
        ]);

        return redirect()
            ->route('receptionist.appointments.index')
            ->with('success', 'Appointment updated successfully.');
    }

    /* ──────────────────────────────────────────────────────────
       DESTROY  –  DELETE /receptionist/appointments/{appointment}
    ────────────────────────────────────────────────────────── */
    public function destroy(Appointment $appointment)
    {
        if ($appointment->isLocked()) {
            return redirect()
                ->route('receptionist.appointments.index')
                ->with('error', 'Completed appointments cannot be deleted.');
        }

        $appointment->delete();

        return redirect()
            ->route('receptionist.appointments.index')
            ->with('success', 'Appointment deleted.');
    }

    /* ──────────────────────────────────────────────────────────
       PRIVATE HELPERS
    ────────────────────────────────────────────────────────── */
    private function formatStatusLabel(string $status): string
    {
        return match (strtolower($status)) {
            'no_show'             => 'No Show',
            'canceled','cancelled' => 'Cancelled',
            default               => ucfirst($status),
        };
    }
}