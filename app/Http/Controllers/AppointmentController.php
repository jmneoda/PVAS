<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    private const ALLOWED_ROLES = ['admin'];

    private function requireAllowedRole(): void
    {
        $role = auth()->user()?->role;
        if (! auth()->check() || ! in_array($role, self::ALLOWED_ROLES, true)) {
            abort(403, 'Access denied.');
        }
    }

    // ─────────────────────────────────────────────────────────────────

    /**
     * List appointments.
     *
     * Completed appointments are excluded here — they belong exclusively to
     * the Reports page. Showing them here would duplicate data and let staff
     * accidentally interact with locked records.
     *
     * Valid status filters: scheduled, confirmed, no_show, canceled.
     * "completed" is intentionally omitted.
     */
    public function index(Request $request)
    {
        $this->requireAllowedRole();

        if ($request->input('status') === 'completed') {
            return redirect()->route('reports.index')
                ->with('info', 'Completed appointments are available in Reports.');
        }

        $selectedDate   = $request->input('date');
        $selectedStatus = $request->input('status');

        if ($selectedDate && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate = null;
        }

        $allowedStatuses = ['scheduled', 'confirmed', 'no_show', 'canceled'];
        if ($selectedStatus && ! in_array($selectedStatus, $allowedStatuses, true)) {
            $selectedStatus = null;
        }

        $query = DB::table('appointments')
            ->join('customers',       'appointments.customer_id',    '=', 'customers.id')
            ->leftJoin('pets',        'appointments.pet_id',         '=', 'pets.id')
            ->leftJoin('users as vets','appointments.veterinarian_id','=', 'vets.id')
            ->whereRaw('LOWER(appointments.status) != ?', ['completed'])
            ->orderByDesc('appointments.scheduled_date')
            ->orderByDesc('appointments.scheduled_time')
            ->select(
                'appointments.id',
                'appointments.customer_id',
                'appointments.pet_id',
                'appointments.veterinarian_id',
                'appointments.scheduled_date',
                'appointments.scheduled_time',
                'appointments.status',
                'appointments.reason_for_visit',
                'appointments.type',
                DB::raw("CONCAT(customers.first_name, ' ', customers.last_name) AS customer_name"),
                DB::raw("COALESCE(pets.pet_name, '—') AS pet_name"),
                DB::raw("COALESCE(pets.species, '—') AS pet_species"),
                DB::raw("COALESCE(pets.breed, '—') AS pet_breed"),
                DB::raw("COALESCE(pets.gender, '—') AS pet_gender"),
                DB::raw("COALESCE(pets.color, '—') AS pet_color"),
                DB::raw("COALESCE(pets.weight, '—') AS pet_weight"),
                DB::raw("COALESCE(vets.name, '—') AS vet_name"),
                'customers.address',
                'customers.contact_number'
            );

        if ($selectedDate) {
            $query->where('appointments.scheduled_date', $selectedDate);
        }

        if ($selectedStatus) {
            $query->where('appointments.status', $selectedStatus);
        }

        $appointments = $query->get();

        $customers = DB::table('customers')
            ->orderBy('first_name')
            ->select('id', 'first_name', 'last_name')
            ->get();

        $staffList = DB::table('users')
            ->whereIn('role', ['veterinarian', 'vet_nurse', 'vet_assistant', 'groomer', 'staff'])
            ->where('is_active', 1)
            ->orderBy('name')
            ->select('id', 'name', 'role')
            ->get();

        return view('appointments.index', compact(
            'appointments',
            'selectedDate',
            'selectedStatus',
            'customers',
            'staffList'
        ));
    }

    // ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $this->requireAllowedRole();

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
            'type'             => 'nullable|in:' . implode(',', Appointment::TYPES),
            'reason_for_visit' => 'nullable|string',
        ]);

        $petId = DB::table('pets')->insertGetId([
            'customer_id' => $validated['customer_id'],
            'pet_name'    => $validated['pet_name'],
            'species'     => $validated['species'],
            'breed'       => $validated['breed']  ?? null,
            'gender'      => $validated['gender'] ?? null,
            'color'       => $validated['color']  ?? null,
            'weight'      => $validated['weight'] ?? null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $appointmentId = DB::table('appointments')->insertGetId([
            'customer_id'      => $validated['customer_id'],
            'pet_id'           => $petId,
            'veterinarian_id'  => $validated['veterinarian_id'],
            'scheduled_date'   => $validated['scheduled_date'],
            'scheduled_time'   => $validated['scheduled_time'],
            'type'             => $validated['type'] ?? null,
            'reason_for_visit' => $validated['reason_for_visit'] ?? null,
            'status'           => Appointment::STATUS_SCHEDULED,
            'created_by'       => auth()->id(),
            'updated_by'       => auth()->id(),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        DB::table('appointment_status_histories')->insert([
            'appointment_id' => $appointmentId,
            'status'         => Appointment::STATUS_SCHEDULED,
            'changed_by'     => auth()->id(),
            'changed_at'     => now(),
        ]);

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Appointment created successfully.');
    }

    // ─────────────────────────────────────────────────────────────────

    /**
     * Update an existing appointment (admin only).
     *
     * - Completed appointments are fully locked and cannot be edited.
     * - Edits the appointment row AND its linked pet row.
     * - Status is intentionally NOT changed here; use updateStatus() for that.
     */
    public function update(Request $request, $id)
    {
        $this->requireAllowedRole();

        $appointment = DB::table('appointments')->where('id', $id)->first();

        if (! $appointment) {
            return redirect()->route('appointments.index')
                ->with('error', 'Appointment not found.');
        }

        // Completed appointments are fully locked
        if (strtolower($appointment->status) === 'completed') {
            return redirect()->route('appointments.index')
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
            'type'             => 'nullable|in:' . implode(',', Appointment::TYPES),
            'reason_for_visit' => 'nullable|string',
        ]);

        // Update the linked pet row if it exists
        if ($appointment->pet_id) {
            DB::table('pets')->where('id', $appointment->pet_id)->update([
                'customer_id' => $validated['customer_id'],
                'pet_name'    => $validated['pet_name'],
                'species'     => $validated['species'],
                'breed'       => $validated['breed']  ?? null,
                'gender'      => $validated['gender'] ?? null,
                'color'       => $validated['color']  ?? null,
                'weight'      => $validated['weight'] ?? null,
                'updated_at'  => now(),
            ]);
        }

        // Update the appointment row (status intentionally untouched)
        DB::table('appointments')->where('id', $id)->update([
            'customer_id'      => $validated['customer_id'],
            'veterinarian_id'  => $validated['veterinarian_id'],
            'scheduled_date'   => $validated['scheduled_date'],
            'scheduled_time'   => $validated['scheduled_time'],
            'type'             => $validated['type'] ?? null,
            'reason_for_visit' => $validated['reason_for_visit'] ?? null,
            'updated_by'       => auth()->id(),
            'updated_at'       => now(),
        ]);

        return redirect()
            ->route('appointments.index')
            ->with('success', "Appointment #{$id} updated successfully.");
    }

    // ─────────────────────────────────────────────────────────────────

    public function show(Request $request, $id)
    {
        $this->requireAllowedRole();

        $appointment = DB::table('appointments')
            ->join('customers',          'appointments.customer_id',    '=', 'customers.id')
            ->leftJoin('pets',           'appointments.pet_id',         '=', 'pets.id')
            ->leftJoin('users as vets',  'appointments.veterinarian_id','=', 'vets.id')
            ->leftJoin('users as creator','appointments.created_by',    '=', 'creator.id')
            ->leftJoin('users as updater','appointments.updated_by',    '=', 'updater.id')
            ->where('appointments.id', $id)
            ->select(
                'appointments.id',
                'appointments.customer_id',
                'appointments.pet_id',
                'appointments.veterinarian_id',
                'appointments.scheduled_date',
                'appointments.scheduled_time',
                'appointments.status',
                'appointments.reason_for_visit',
                'appointments.type',
                'appointments.created_at',
                'appointments.updated_at',
                DB::raw("CONCAT(customers.first_name, ' ', customers.last_name) AS customer_name"),
                DB::raw("COALESCE(pets.pet_name, '—') AS pet_name"),
                DB::raw("COALESCE(pets.species, '—') AS species"),
                DB::raw("COALESCE(pets.breed, '—') AS breed"),
                DB::raw("COALESCE(pets.gender, '—') AS gender"),
                DB::raw("COALESCE(pets.color, '—') AS color"),
                DB::raw("COALESCE(pets.weight, '—') AS weight"),
                DB::raw("COALESCE(vets.name, '—') AS vet_name"),
                DB::raw("COALESCE(creator.name, '—') AS created_by_name"),
                DB::raw("COALESCE(updater.name, '—') AS updated_by_name"),
                'customers.address',
                'customers.contact_number',
                'customers.email as customer_email'
            )
            ->first();

        if (! $appointment) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Not found'], 404);
            }
            abort(404, 'Appointment not found.');
        }

        if ($request->ajax() || $request->wantsJson()) {
            $history = DB::table('appointment_status_histories as h')
                ->leftJoin('users as u', 'h.changed_by', '=', 'u.id')
                ->where('h.appointment_id', $id)
                ->orderBy('h.changed_at')
                ->select(
                    'h.status',
                    'h.changed_at',
                    DB::raw("COALESCE(u.role, 'system') AS changer_role")
                )
                ->get()
                ->map(function ($item) {
                    $roleLabel = $item->changer_role === 'system'
                        ? 'System'
                        : ucwords(str_replace('_', ' ', $item->changer_role));

                    return [
                        'status'       => $item->status,
                        'status_label' => $this->formatStatusLabel($item->status),
                        'changed_at'   => $item->changed_at,
                        'changed_by'   => $roleLabel,
                    ];
                });

            $data                   = (array) $appointment;
            $data['status_history'] = $history;
            $data['pet_id']         = $appointment->pet_id ?? '—';

            return response()->json($data);
        }

        return view('appointments.show', compact('appointment'));
    }

    // ─────────────────────────────────────────────────────────────────

    /**
     * Update the status of an appointment.
     *
     * Business rules:
     *  - Completed → fully locked (no changes at all).
     *  - Confirmed → cannot be moved to Cancelled.
     *  - No-show   → can only move to Cancelled.
     *
     * When status becomes COMPLETED:
     *  - The row is excluded from this list automatically (query excludes it).
     *  - AJAX response includes remove_from_list: true so JS can animate the row out.
     */
    public function updateStatus(Request $request, $id)
    {
        $this->requireAllowedRole();

        $request->validate([
            'status' => ['required', 'in:' . implode(',', Appointment::STATUSES)],
        ]);

        $appointment = DB::table('appointments')->where('id', $id)->first();

        if (! $appointment) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Appointment not found.'], 404);
            }
            return back()->with('error', 'Appointment not found.');
        }

        if (strtolower($appointment->status) === 'completed') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'This appointment is completed and cannot be modified.'], 422);
            }
            return back()->with('error', 'Completed appointments cannot be modified.');
        }

        if (strtolower($appointment->status) === 'confirmed' && $request->status === 'canceled') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'A confirmed appointment cannot be cancelled.'], 422);
            }
            return back()->with('error', 'A confirmed appointment cannot be cancelled.');
        }

        if (strtolower($appointment->status) === 'no_show' && $request->status !== 'canceled') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'A no-show appointment can only be moved to Cancelled.'], 422);
            }
            return back()->with('error', 'A no-show appointment can only be moved to Cancelled.');
        }

        DB::table('appointments')->where('id', $id)->update([
            'status'     => $request->status,
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);

        DB::table('appointment_status_histories')->insert([
            'appointment_id' => $id,
            'status'         => $request->status,
            'changed_by'     => auth()->id(),
            'changed_at'     => now(),
        ]);

        $label       = $this->formatStatusLabel($request->status);
        $isCompleted = strtolower($request->status) === 'completed';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'          => true,
                'status'           => $request->status,
                'label'            => $label,
                'remove_from_list' => $isCompleted,
            ]);
        }

        $redirectTo = $request->input('redirect_to');
        if ($redirectTo === 'dashboard') {
            return redirect()->route('dashboard')
                ->with('success', "Appointment #{$id} updated to {$label}.");
        }

        if ($isCompleted) {
            return redirect()->route('appointments.index')
                ->with('success', "Appointment #{$id} marked as Completed and moved to Reports.");
        }

        return redirect()->route('appointments.show', $id)
            ->with('success', "Status updated to {$label}.");
    }

    // ─────────────────────────────────────────────────────────────────

    public function destroy($id)
    {
        if (! auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators can delete appointments.');
        }

        $appointment = DB::table('appointments')->where('id', $id)->first();

        if (! $appointment) {
            return redirect()->route('appointments.index')
                ->with('error', 'Appointment not found.');
        }

        if (strtolower($appointment->status) === 'completed') {
            return redirect()->route('appointments.index')
                ->with('error', 'Completed appointments cannot be deleted.');
        }

        DB::table('appointments')->where('id', $id)->delete();

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment deleted.');
    }

    // ─────────────────────────────────────────────────────────────────

    private function formatStatusLabel(string $status): string
    {
        return match (strtolower($status)) {
            'no_show'            => 'No Show',
            'canceled','cancelled' => 'Cancelled',
            default              => ucfirst($status),
        };
    }
}