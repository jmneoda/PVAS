<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ── Date Filter ──────────────────────────────────────────────────
        $selectedDate = $request->input('date');

        if ($selectedDate && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate = null;
        }

        // ── Base query ───────────────────────────────────────────────────
        $base = Appointment::query();

        if ($selectedDate) {
            $base->where('scheduled_date', $selectedDate);
        }

        // ── Stat counts ──────────────────────────────────────────────────
        // DB ENUM: enum('scheduled','confirmed','completed','no_show','canceled')
        // 'canceled' = ONE 'l' — must match exactly
        $totalAppointments = (clone $base)->count();
        $scheduledCount    = (clone $base)->where('status', 'scheduled')->count();
        $confirmedCount    = (clone $base)->where('status', 'confirmed')->count();
        $completedCount    = (clone $base)->where('status', 'completed')->count();
        $noShowCount       = (clone $base)->where('status', 'no_show')->count();
        $canceledCount     = (clone $base)->where('status', 'canceled')->count();
        //  ^^^^^^^^^^^                                              ^^^^^^^
        //  one 'l' — matches DB ENUM and the Blade variable $canceledCount

        // ── Monthly total (always current month, regardless of filter) ───
        $now = Carbon::now();

        $monthlyAppointments = Appointment::whereMonth('scheduled_date', $now->month)
            ->whereYear('scheduled_date', $now->year)
            ->count();

        // ── Recent appointments (latest 10, date-filtered) ───────────────
        $recentAppointments = (clone $base)
            ->with(['customer', 'pet'])
            ->orderByDesc('scheduled_date')
            ->orderByDesc('scheduled_time')
            ->limit(10)
            ->get();

        // ── Date label ───────────────────────────────────────────────────
        $dateLabel = $selectedDate
            ? Carbon::parse($selectedDate)->format('F d, Y')
            : 'All Dates';

        return view('receptionist.dashboard', compact(
            'selectedDate',
            'dateLabel',
            'totalAppointments',
            'scheduledCount',
            'confirmedCount',
            'completedCount',
            'noShowCount',
            'canceledCount',       // one 'l' — matches $canceledCount above and the Blade view
            'monthlyAppointments',
            'recentAppointments',
        ));
    }
}