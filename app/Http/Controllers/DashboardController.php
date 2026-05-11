<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // ── Status definitions (single source of truth) ───────────────────
    public const STATUSES = [
        'scheduled' => [
            'label'  => 'Scheduled',
            'accent' => '#2196f3',
            'bg'     => '#dbeeff',
            'text'   => '#004085',
            'border' => '#90c8ff',
            'dot'    => '#2196f3',
        ],
        'confirmed' => [
            'label'  => 'Confirmed',
            'accent' => '#7c3aed',
            'bg'     => '#ede9fe',
            'text'   => '#3b0764',
            'border' => '#c4b5fd',
            'dot'    => '#7c3aed',
        ],
        'completed' => [
            'label'  => 'Completed',
            'accent' => '#27ae60',
            'bg'     => '#e8f5e9',
            'text'   => '#155724',
            'border' => '#81c784',
            'dot'    => '#27ae60',
        ],
        'no_show' => [
            'label'  => 'No Show',
            'accent' => '#f59e0b',
            'bg'     => '#fffbeb',
            'text'   => '#78350f',
            'border' => '#fcd34d',
            'dot'    => '#f59e0b',
        ],
        // FIX: key must be 'canceled' to match the DB enum (single L)
        'canceled' => [
            'label'  => 'Cancelled',
            'accent' => '#e53935',
            'bg'     => '#fde8e8',
            'text'   => '#842029',
            'border' => '#f5a5a5',
            'dot'    => '#e53935',
        ],
    ];

    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        // ── Date Filter ───────────────────────────────────────────────
        $selectedDate = $request->input('date');

        if ($selectedDate && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate = null;
        }

        // ── Helper: apply optional date constraint ────────────────────
        $ranged = function () use ($selectedDate) {
            $q = DB::table('appointments');
            if ($selectedDate) {
                $q->where('scheduled_date', $selectedDate);
            }
            return $q;
        };

        // ── Stat counts ───────────────────────────────────────────────
        $totalAppointments = $ranged()->count();
        $scheduledCount    = $ranged()->where('status', 'scheduled')->count();
        $confirmedCount    = $ranged()->where('status', 'confirmed')->count();
        $completedCount    = $ranged()->where('status', 'completed')->count();
        $noShowCount       = $ranged()->where('status', 'no_show')->count();
        // FIX: DB enum is 'canceled' (one L) — 'cancelled' always returned 0
        $cancelledCount    = $ranged()->where('status', 'canceled')->count();

        // ── Monthly summary (always current calendar month) ───────────
        $now = Carbon::now();

        $monthlyAppointments = DB::table('appointments')
            ->whereMonth('scheduled_date', $now->month)
            ->whereYear('scheduled_date',  $now->year)
            ->count();

        // ── Recent appointments (filtered, latest 10) ─────────────────
        $recentQuery = DB::table('appointments')
            ->join('customers', 'appointments.customer_id', '=', 'customers.id')
            ->leftJoin('pets', 'appointments.pet_id', '=', 'pets.id')
            ->leftJoin('users as vets', 'appointments.veterinarian_id', '=', 'vets.id')
            ->orderByDesc('appointments.scheduled_date')
            ->orderByDesc('appointments.scheduled_time')
            ->limit(10)
            ->select(
                'appointments.id',
                'appointments.customer_id',
                'appointments.pet_id',
                'appointments.status',
                'appointments.scheduled_date',
                'appointments.scheduled_time',
                'appointments.reason_for_visit',
                DB::raw("CONCAT(customers.first_name, ' ', customers.last_name) AS customer_name"),
                DB::raw("COALESCE(pets.pet_name, '—') AS pet_name"),
                DB::raw("COALESCE(vets.name, '—') AS vet_name")
            );

        if ($selectedDate) {
            $recentQuery->where('appointments.scheduled_date', $selectedDate);
        }

        $recentAppointments = $recentQuery->get();

        // ── Logged-in user info ───────────────────────────────────────
        $authUser = Auth::user();

        $roleLabels = [
            'admin'         => 'Administrator',
            'veterinarian'  => 'Veterinarian',
            'receptionist'  => 'Receptionist',
            'vet_nurse'     => 'Vet Nurse',
            'vet_assistant' => 'Vet Assistant',
            'groomer'       => 'Groomer',
            'staff'         => 'Staff',
        ];

        $roleColors = [
            'admin'         => ['bg' => '#fdecea', 'border' => '#f5a5a5', 'text' => '#b71c1c', 'dot' => '#e53935'],
            'veterinarian'  => ['bg' => '#e8f5e9', 'border' => '#81c784', 'text' => '#1b5e20', 'dot' => '#27ae60'],
            'receptionist'  => ['bg' => '#e3f2fd', 'border' => '#90caf9', 'text' => '#0d47a1', 'dot' => '#2196f3'],
            'vet_nurse'     => ['bg' => '#fff8e1', 'border' => '#ffe082', 'text' => '#6d4c00', 'dot' => '#f9a825'],
            'vet_assistant' => ['bg' => '#f3e5f5', 'border' => '#ce93d8', 'text' => '#4a148c', 'dot' => '#8e24aa'],
            'groomer'       => ['bg' => '#fce4ec', 'border' => '#f48fb1', 'text' => '#880e4f', 'dot' => '#e91e63'],
            'staff'         => ['bg' => '#f1f8e9', 'border' => '#aed581', 'text' => '#33691e', 'dot' => '#7cb342'],
        ];

        $userRole  = $authUser?->role ?? 'staff';
        $roleLabel = $roleLabels[$userRole] ?? ucfirst($userRole);
        $roleColor = $roleColors[$userRole] ?? $roleColors['staff'];
        $userName  = $authUser?->name      ?? 'User';

        // ── Date label for display ────────────────────────────────────
        $dateLabel = $selectedDate
            ? Carbon::parse($selectedDate)->format('F d, Y')
            : 'All Dates';

        return view('dashboard', compact(
            'selectedDate',
            'dateLabel',
            'totalAppointments',
            'scheduledCount',
            'confirmedCount',
            'completedCount',
            'noShowCount',
            'cancelledCount',
            'monthlyAppointments',
            'recentAppointments',
            'userName',
            'roleLabel',
            'roleColor'
        ));
    }
}