<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    // ── Allowed filter keys ────────────────────────────────────────────
    private const DATE_FILTERS = [
        'this_week',
        'this_month',
        'this_year',
        'custom',
    ];

    // ── Directory where PDFs are saved on the server ───────────────────
    private const PDF_SAVE_DIR = 'C:\\pvas_file';

    // ── Role display labels ────────────────────────────────────────────
    private const ROLE_LABELS = [
        'admin'          => 'Admin',
        'receptionist'   => 'Receptionist',
        'veterinarian'   => 'Veterinarian',
        'vet'            => 'Veterinarian',
        'staff'          => 'Staff',
        'super_admin'    => 'Super Admin',
    ];

    // ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        [$dateFilter, $customDate] = $this->sanitiseInput($request);

        [$startDate, $endDate] = $this->resolveDateRange($dateFilter, $customDate);

        $records   = $this->fetchRecords($startDate, $endDate);
        $dateLabel = $this->resolveDateLabel($dateFilter, $customDate, $startDate, $endDate);

        return view('admin.reports', compact(
            'records',
            'dateFilter',
            'customDate',
            'dateLabel',
            'startDate',
            'endDate'
        ));
    }

    // ─────────────────────────────────────────────────────────────────

    public function show(Request $request, int $id)
    {
        // ── Main appointment record ────────────────────────────────────
        $record = DB::table('appointments')
            ->join('customers', 'appointments.customer_id', '=', 'customers.id')
            ->leftJoin('pets',  'appointments.pet_id',      '=', 'pets.id')
            ->leftJoin('users as vets', 'appointments.veterinarian_id', '=', 'vets.id')
            ->where('appointments.id', $id)
            ->where('appointments.status', Appointment::STATUS_COMPLETED)
            ->select(
                'appointments.id                                        as appointment_id',
                'appointments.scheduled_date                            as appointment_date',
                'appointments.scheduled_time                            as appointment_time',
                'appointments.reason_for_visit',
                'appointments.status',
                'customers.id                                           as customer_id',
                DB::raw("CONCAT(customers.first_name, ' ', customers.last_name) as customer_name"),
                'customers.contact_number',
                'customers.address',
                DB::raw("COALESCE(pets.pet_name, '—')    as pet_name"),
                DB::raw("COALESCE(pets.species, '—')     as pet_species"),
                DB::raw("COALESCE(pets.breed, '—')       as pet_breed"),
                DB::raw("COALESCE(vets.name, '—')        as vet_name")
            )
            ->first();

        if (! $record) {
            abort(404, 'Record not found.');
        }

        // ── Status history with role (not username) ────────────────────
        $rawHistory = DB::table('appointment_status_histories as ash')
            ->leftJoin('users', 'ash.changed_by', '=', 'users.id')
            ->where('ash.appointment_id', $id)
            ->orderBy('ash.changed_at')
            ->select(
                'ash.status',
                'ash.changed_at',
                // Use role column; fall back to a generic label if no user
                DB::raw("COALESCE(users.role, 'system') as changed_by_role")
            )
            ->get();

        // Normalise role labels for display
        $roleLabels = self::ROLE_LABELS;

        $statusHistory = $rawHistory->map(function ($h) use ($roleLabels) {
            $rawRole    = strtolower(trim($h->changed_by_role));
            $roleLabel  = $roleLabels[$rawRole] ?? ucfirst($rawRole);

            return [
                'status'     => $h->status,
                'changed_at' => $h->changed_at,
                'role'       => $roleLabel,
            ];
        })->values()->all();

        $record->status_history = $statusHistory;

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($record);
        }

        return back();
    }

    // ─────────────────────────────────────────────────────────────────

    public function destroy(Request $request, int $id)
    {
        $deleted = DB::table('appointments')
            ->where('id', $id)
            ->where('status', Appointment::STATUS_COMPLETED)
            ->delete();

        if (! $deleted) {
            return redirect()
                ->route('reports.index', array_filter([
                    'date_filter' => $request->input('date_filter', 'this_week'),
                    'custom_date' => $request->input('custom_date', ''),
                ]))
                ->with('error', 'Record not found or could not be deleted.');
        }

        return redirect()
            ->route('reports.index', array_filter([
                'date_filter' => $request->input('date_filter', 'this_week'),
                'custom_date' => $request->input('custom_date', ''),
            ]))
            ->with('success', 'Appointment record #' . $id . ' has been deleted.');
    }

    // ─────────────────────────────────────────────────────────────────

    public function downloadPdf(Request $request)
    {
        [$dateFilter, $customDate] = $this->sanitiseInput($request);
        [$startDate, $endDate]     = $this->resolveDateRange($dateFilter, $customDate);

        $records   = $this->fetchRecords($startDate, $endDate);
        $dateLabel = $this->resolveDateLabel($dateFilter, $customDate, $startDate, $endDate);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'admin.reports-pdf',
            compact('records', 'dateFilter', 'dateLabel', 'startDate', 'endDate')
        )->setPaper('a4', 'landscape');

        $filename = 'pvas-reports-' . now()->format('Y-m-d') . '.pdf';

        $saveDir = self::PDF_SAVE_DIR;
        if (! is_dir($saveDir)) {
            mkdir($saveDir, 0755, true);
        }
        file_put_contents($saveDir . DIRECTORY_SEPARATOR . $filename, $pdf->output());

        return $pdf->download($filename);
    }

    // ─────────────────────────────────────────────────────────────────

    public function downloadCsv(Request $request)
    {
        [$dateFilter, $customDate] = $this->sanitiseInput($request);
        [$startDate, $endDate]     = $this->resolveDateRange($dateFilter, $customDate);

        $records   = $this->fetchRecords($startDate, $endDate);
        $dateLabel = $this->resolveDateLabel($dateFilter, $customDate, $startDate, $endDate);

        $filename = 'pvas-reports-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $completedLabel = ucfirst(Appointment::STATUS_COMPLETED);

        $callback = function () use ($records, $dateLabel, $completedLabel) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['PVAS - Appointment Report']);
            fputcsv($handle, ['Period:', $dateLabel]);
            fputcsv($handle, ['Generated:', now()->format('F d, Y h:i A')]);
            fputcsv($handle, ['Total Records:', count($records)]);
            fputcsv($handle, []);

            fputcsv($handle, [
                '#',
                'Appt. ID',
                'Appointment Date',
                'Appointment Time',
                'Customer Name',
                'Customer ID',
                'Contact Number',
                'Address',
                'Pet Name',
                'Species',
                'Breed',
                'Veterinarian',
                'Reason for Visit',
                'Status',
            ]);

            foreach ($records as $i => $record) {
                if ($record->status !== Appointment::STATUS_COMPLETED) {
                    continue;
                }

                fputcsv($handle, [
                    $i + 1,
                    '#' . $record->appointment_id,
                    Carbon::parse($record->appointment_date)->format('F d, Y'),
                    Carbon::parse($record->appointment_time)->format('h:i A'),
                    $record->customer_name,
                    $record->customer_id,
                    $record->contact_number ?? '',
                    $record->address ?? '',
                    $record->pet_name,
                    $record->pet_species,
                    $record->pet_breed,
                    $record->vet_name,
                    $record->reason_for_visit ?? '',
                    $completedLabel,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────

    private function sanitiseInput(Request $request): array
    {
        $dateFilter = $request->input('date_filter', 'this_week');

        if (! in_array($dateFilter, self::DATE_FILTERS)) {
            $dateFilter = 'this_week';
        }

        $customDate = null;
        if ($dateFilter === 'custom') {
            $raw = $request->input('custom_date', '');
            try {
                $parsed = Carbon::createFromFormat('Y-m-d', $raw);
                if ($parsed && $parsed->lte(Carbon::today())) {
                    $customDate = $parsed->toDateString();
                }
            } catch (\Exception) {
                $customDate = Carbon::today()->toDateString();
            }

            if (! $customDate) {
                $customDate = Carbon::today()->toDateString();
            }
        }

        return [$dateFilter, $customDate];
    }

    // ─────────────────────────────────────────────────────────────────

    private function fetchRecords(?string $startDate, ?string $endDate)
    {
        $query = DB::table('appointments')
            ->join('customers', 'appointments.customer_id', '=', 'customers.id')
            ->leftJoin('pets',  'appointments.pet_id',      '=', 'pets.id')
            ->leftJoin('users as vets', 'appointments.veterinarian_id', '=', 'vets.id')
            ->where('appointments.status', Appointment::STATUS_COMPLETED)
            ->orderByDesc('appointments.scheduled_date')
            ->orderByDesc('appointments.scheduled_time')
            ->select(
                'appointments.status',
                'customers.id                                           as customer_id',
                DB::raw("CONCAT(customers.first_name, ' ', customers.last_name) as customer_name"),
                'customers.contact_number',
                'customers.address',
                'appointments.id                                        as appointment_id',
                'appointments.scheduled_date                            as appointment_date',
                'appointments.scheduled_time                            as appointment_time',
                'appointments.reason_for_visit',
                DB::raw("COALESCE(pets.pet_name, '—')    as pet_name"),
                DB::raw("COALESCE(pets.species, '—')     as pet_species"),
                DB::raw("COALESCE(pets.breed, '—')       as pet_breed"),
                DB::raw("COALESCE(vets.name, '—')        as vet_name")
            );

        if ($startDate && $endDate) {
            $query->whereBetween('appointments.scheduled_date', [$startDate, $endDate]);
        }

        return $query->get();
    }

    // ─────────────────────────────────────────────────────────────────

    private function resolveDateRange(string $filter, ?string $customDate): array
    {
        return match ($filter) {
            'this_month' => [
                Carbon::now()->startOfMonth()->toDateString(),
                Carbon::now()->endOfMonth()->toDateString(),
            ],
            'this_year'  => [
                Carbon::now()->startOfYear()->toDateString(),
                Carbon::now()->endOfYear()->toDateString(),
            ],
            'custom'     => [
                $customDate ?? Carbon::today()->toDateString(),
                $customDate ?? Carbon::today()->toDateString(),
            ],
            default      => [
                Carbon::now()->startOfWeek()->toDateString(),
                Carbon::now()->endOfWeek()->toDateString(),
            ],
        };
    }

    // ─────────────────────────────────────────────────────────────────

    private function resolveDateLabel(
        string  $filter,
        ?string $customDate,
        ?string $start,
        ?string $end
    ): string {
        return match ($filter) {
            'this_month' => 'This Month (' . Carbon::now()->format('F Y') . ')',
            'this_year'  => 'This Year (' . Carbon::now()->format('Y') . ')',
            'custom'     => 'Custom Date (' . Carbon::parse($customDate)->format('F d, Y') . ')',
            default      => 'This Week ('
                . Carbon::now()->startOfWeek()->format('M d')
                . ' – '
                . Carbon::now()->endOfWeek()->format('M d, Y')
                . ')',
        };
    }
}