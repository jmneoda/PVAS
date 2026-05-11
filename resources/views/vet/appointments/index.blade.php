{{-- resources/views/vet/appointments/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PVAS – Appointments</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body { font-family: 'Figtree', sans-serif; background: #a0a0a0; min-height: 100vh; display: flex; }

        /* ════ SIDEBAR ════ */
        .sidebar {
            width: 160px; min-width: 160px; background: #fff;
            border-right: 2px solid #888; display: flex; flex-direction: column;
            align-items: center; padding: 20px 0 0; position: sticky; top: 0; height: 100vh;
        }
        .sidebar-logo { width: 130px; height: 130px; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; }
        .sidebar-logo img { width: 100%; height: 100%; object-fit: contain; }
        .sidebar-nav { width: 100%; display: flex; flex-direction: column; flex: 1; }
        .sidebar-nav a {
            display: block; width: 100%; padding: 15px 0; text-align: center;
            font-size: 15px; font-weight: 700; color: #111; text-decoration: none;
            border-top: 1.5px solid #bbb; transition: background 0.15s;
        }
        .sidebar-nav a:hover  { background: #e0e0e0; }
        .sidebar-nav a.active { background: #d0d0d0; }
        .sidebar-nav a.logout { color: #cc0000; border-top: 1.5px solid #bbb; border-bottom: 1.5px solid #bbb; margin-top: auto; }
        .sidebar-spacer { flex: 1; }

        /* ════ MAIN ════ */
        .main { flex: 1; display: flex; flex-direction: column; background: #a8a8a8; min-height: 100vh; overflow-x: hidden; }

        /* ── Page Header ── */
        .page-header {
            background: #b8b8b8; border-bottom: 2px solid #888;
            padding: 10px 20px; display: flex; align-items: center;
            gap: 8px; flex-wrap: wrap; min-height: 58px;
        }
        .page-header h1 { font-size: 20px; font-weight: 800; color: #111; white-space: nowrap; margin-right: auto; }

        /* ── Status filter pills ── */
        .status-filters { display: flex; align-items: center; gap: 5px; flex-wrap: wrap; }
        .filter-pill {
            display: inline-flex; align-items: center; padding: 3px 11px;
            border-radius: 999px; font-size: 12px; font-weight: 700; border: 1.5px solid #bbb;
            background: #e4e4e4; color: #444; cursor: pointer; text-decoration: none;
            transition: background 0.15s, color 0.15s; white-space: nowrap; line-height: 1.6;
        }
        .filter-pill:hover              { background: #d0d0d0; }
        .filter-pill.active-all         { background: #111;    color: #fff; border-color: #111; }
        .filter-pill.active-scheduled   { background: #dbeafe; color: #1d4ed8; border-color: #93c5fd; }
        .filter-pill.active-confirmed   { background: #ede9fe; color: #6d28d9; border-color: #c4b5fd; }
        .filter-pill.active-no_show     { background: #fef9c3; color: #854d0e; border-color: #fde047; }
        .filter-pill.active-canceled    { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }

        /* ── Type filter ── */
        .type-filter-wrapper {
            display: flex; align-items: center; gap: 6px;
            background: #fff; border: 1.5px solid #aaa; border-radius: 6px; padding: 5px 12px;
        }
        .type-filter-wrapper label { font-size: 13px; font-weight: 700; color: #444; white-space: nowrap; }
        .type-filter-wrapper select {
            border: none; background: transparent; font-size: 13px;
            font-family: 'Figtree', sans-serif; font-weight: 600; color: #111;
            outline: none; cursor: pointer; appearance: auto;
        }
        .type-clear-btn {
            background: none; border: none; cursor: pointer;
            font-size: 14px; color: #888; padding: 0 2px; line-height: 1; transition: color 0.15s;
        }
        .type-clear-btn:hover { color: #c0392b; }

        /* ── Date picker ── */
        .date-filter-wrapper {
            display: flex; align-items: center; gap: 6px;
            background: #fff; border: 1.5px solid #aaa; border-radius: 6px; padding: 5px 12px; white-space: nowrap;
        }
        .date-filter-wrapper label { font-size: 13px; font-weight: 700; color: #444; }
        .date-filter-wrapper input[type="date"] {
            border: none; background: transparent; font-size: 13px;
            font-family: 'Figtree', sans-serif; font-weight: 600; color: #111; outline: none; cursor: pointer;
        }
        .date-clear-btn {
            background: none; border: none; cursor: pointer;
            font-size: 14px; color: #888; padding: 0 2px; line-height: 1; transition: color 0.15s;
        }
        .date-clear-btn:hover { color: #c0392b; }

        /* ════ PAGE BODY ════ */
        .page-body { flex: 1; padding: 16px 20px; display: flex; flex-direction: column; gap: 12px; }

        /* ── Flash messages ── */
        .flash { display: flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 4px; font-size: 13px; font-weight: 700; }
        .flash-success { background: #e8f5e9; border: 1px solid #81c784; color: #155724; }
        .flash-error   { background: #fde8e8; border: 1px solid #f5a5a5; color: #842029; }
        .flash-info    { background: #e3f2fd; border: 1px solid #90caf9; color: #0d47a1; }

        /* ── New-appointment alert banner (vet-only) ── */
        .new-appt-alert {
            display: flex; align-items: center; gap: 10px;
            background: #fff8e1; border: 1.5px solid #ffe082; border-radius: 6px;
            padding: 10px 16px; font-size: 13px; font-weight: 700; color: #6d4c00;
        }
        .new-appt-alert svg { flex-shrink: 0; color: #f59e0b; }
        .new-appt-alert a {
            margin-left: auto; font-size: 11px; font-weight: 800; color: #92400e;
            border: 1.5px solid #fcd34d; background: #fef9c3; border-radius: 4px;
            padding: 4px 12px; text-decoration: none; white-space: nowrap; transition: background 0.15s;
        }
        .new-appt-alert a:hover { background: #fde68a; }

        /* ── Role-scope info banner ── */
        .role-scope-banner {
            display: flex; align-items: center; gap: 10px;
            background: #f0f4ff; border: 1.5px solid #a5b4fc; border-radius: 6px;
            padding: 9px 16px; font-size: 12px; font-weight: 700; color: #3730a3;
        }
        .role-scope-banner svg { flex-shrink: 0; }
        .role-scope-banner .type-chips { display: flex; gap: 5px; flex-wrap: wrap; }
        .type-chip {
            display: inline-block; font-size: 11px; font-weight: 800;
            padding: 2px 9px; border-radius: 999px; border: 1.5px solid transparent;
        }
        .type-chip-Checkup     { background: #d1ecf1; color: #0c5460; border-color: #99d6e3; }
        .type-chip-Vaccination { background: #d4edda; color: #155724; border-color: #8fd4a0; }
        .type-chip-Surgery     { background: #f8d7da; color: #842029; border-color: #f0a0a8; }
        .type-chip-Grooming    { background: #fde8d8; color: #7b3a0e; border-color: #f5bfa0; }

        /* ── Filter banner ── */
        .filter-banner {
            display: flex; align-items: center; gap: 8px;
            background: #d4d4d4; border: 1px solid #bbb; border-radius: 4px;
            padding: 6px 14px; font-size: 12px; font-weight: 700; color: #555;
        }
        .filter-banner strong { color: #222; }

        /* ── Top action bar ── */
        .top-action-bar { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .list-header    { display: flex; align-items: center; gap: 10px; }
        .list-title     { font-size: 16px; font-weight: 800; color: #111; }
        .record-badge {
            display: inline-flex; align-items: center;
            background: #e8e8e8; border: 1.5px solid #bbb; border-radius: 999px;
            padding: 2px 12px; font-size: 12px; font-weight: 700; color: #444;
        }

        /* ── Role info pill ── */
        .role-info-pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: #f0f4ff; border: 1.5px solid #a5b4fc; border-radius: 6px;
            padding: 6px 14px; font-size: 12px; font-weight: 700; color: #3730a3;
        }

        /* ════ TABLE ════ */
        .table-card {
            background: #fff; border: 1.5px solid #ccc; border-radius: 10px;
            overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .table-scroll { overflow-x: auto; }

        .appointments-table { width: 100%; border-collapse: collapse; min-width: 860px; }
        .appointments-table thead th {
            padding: 11px 16px; text-align: left; font-size: 11px; font-weight: 800; color: #555;
            background: #f7f7f7; border-bottom: 1.5px solid #e0e0e0;
            white-space: nowrap; letter-spacing: 0.04em; text-transform: uppercase;
        }
        .appointments-table tbody td {
            padding: 13px 16px; font-size: 13px; color: #222;
            border-bottom: 1px solid #efefef; background: #fff;
            white-space: nowrap; vertical-align: middle;
        }
        .appointments-table tbody tr:last-child td { border-bottom: none; }
        .appointments-table tbody tr:hover td { background: #f9f9f9; }

        /* NEW row highlight (scheduled + created within last 24 h) */
        .appointments-table tbody tr.row-new td { background: #fffde7; }
        .appointments-table tbody tr.row-new:hover td { background: #fff9c4; }

        .appt-id   { font-weight: 800; color: #333; font-size: 14px; }
        .dt-date   { font-weight: 700; color: #111; font-size: 13px; }
        .dt-time   { font-weight: 500; color: #777; font-size: 12px; margin-top: 2px; }
        .cust-name { font-weight: 700; color: #111; font-size: 13px; }
        .cust-id   { font-weight: 500; color: #999; font-size: 11px; margin-top: 1px; }

        .new-tag {
            display: inline-block; font-size: 9px; font-weight: 800;
            background: #fef9c3; border: 1px solid #fcd34d; color: #92400e;
            border-radius: 4px; padding: 1px 5px; margin-left: 4px;
            vertical-align: middle; letter-spacing: 0.04em; text-transform: uppercase;
        }

        /* ── Status badges ── */
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 800; border: 1px solid transparent;
        }
        .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
        .badge-scheduled { background: #dbeeff; color: #004085; border-color: #90c8ff; }
        .badge-scheduled::before { background: #2196f3; }
        .badge-confirmed { background: #ede9fe; color: #3b0764; border-color: #c4b5fd; }
        .badge-confirmed::before { background: #7c3aed; }
        .badge-no-show   { background: #fffbeb; color: #78350f; border-color: #fcd34d; }
        .badge-no-show::before   { background: #f59e0b; }
        .badge-cancelled { background: #fde8e8; color: #842029; border-color: #f5a5a5; }
        .badge-cancelled::before { background: #e53935; }

        /* ── Type pills ── */
        .type-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .type-Checkup     { background: #d1ecf1; color: #0c5460; }
        .type-Vaccination { background: #d4edda; color: #155724; }
        .type-Surgery     { background: #f8d7da; color: #842029; }
        .type-Grooming    { background: #fde8d8; color: #7b3a0e; }

        /* ── Action buttons ── */
        .action-btns { display: flex; gap: 6px; align-items: center; }
        .btn-view-action {
            background: #fff; border: 1.5px solid #bbb; border-radius: 6px;
            padding: 5px 14px; font-size: 12px; font-weight: 700; color: #333;
            cursor: pointer; font-family: 'Figtree', sans-serif; transition: background 0.15s;
        }
        .btn-view-action:hover { background: #f0f0f0; border-color: #888; }

        .empty-row td {
            text-align: center; color: #aaa; font-style: italic;
            padding: 50px 0; font-size: 14px; background: #fff !important;
        }

        /* ── Row removal animation ── */
        @keyframes rowFadeOut {
            from { opacity: 1; transform: translateX(0); }
            to   { opacity: 0; transform: translateX(40px); }
        }
        .row-removing { animation: rowFadeOut 0.35s ease forwards; pointer-events: none; }

        /* ════ MODAL ════ */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.48); z-index: 1000;
            align-items: flex-start; justify-content: center;
            padding: 40px 16px; overflow-y: auto;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: #e8e8e8; border: 2px solid #aaa; border-radius: 8px;
            width: 100%; max-width: 700px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3); overflow: hidden;
            margin-bottom: 40px; display: flex; flex-direction: column;
        }
        .modal-sm { max-width: 560px; }
        .modal-header {
            background: #d8d8d8; border-bottom: 1.5px solid #bbb;
            padding: 13px 18px; display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
        }
        .modal-header h2 {
            display: flex; align-items: center; gap: 8px;
            font-size: 15px; font-weight: 800; color: #111;
        }
        .btn-close {
            background: none; border: none; cursor: pointer;
            font-size: 20px; color: #555; line-height: 1;
            padding: 0 4px; border-radius: 3px; transition: background 0.15s;
        }
        .btn-close:hover { background: rgba(0,0,0,0.1); color: #111; }

        .modal-body   { padding: 16px 18px; display: flex; flex-direction: column; gap: 10px; overflow-y: auto; }
        .modal-footer { padding: 10px 18px 14px; display: flex; justify-content: flex-end; gap: 8px; flex-shrink: 0; border-top: 1.5px solid #ccc; background: #e0e0e0; }
        .modal-loading { padding: 40px; text-align: center; color: #aaa; font-size: 13px; font-weight: 700; }

        /* ── Detail rows ── */
        .detail-row   { display: flex; gap: 8px; margin-bottom: 12px; font-size: 13px; line-height: 1.5; }
        .detail-label { font-weight: 800; color: #111; min-width: 150px; flex-shrink: 0; }
        .detail-value { color: #444; }

        /* ── Lock / warning notices ── */
        .lock-notice        { display: flex; align-items: center; gap: 8px; background: #f9f9f9; border: 1.5px solid #ddd; border-radius: 4px; padding: 10px 14px; font-size: 12px; font-weight: 700; color: #777; }
        .lock-notice-warn   { display: flex; align-items: center; gap: 8px; background: #fffbeb; border: 1.5px solid #fcd34d; border-radius: 4px; padding: 10px 14px; font-size: 12px; font-weight: 700; color: #78350f; }
        .lock-notice-danger { display: flex; align-items: center; gap: 8px; background: #fde8e8; border: 1.5px solid #f5a5a5; border-radius: 4px; padding: 10px 14px; font-size: 12px; font-weight: 700; color: #842029; }

        /* ── Timeline ── */
        .timeline-section { border-top: 1.5px solid #eee; padding: 16px 18px 4px; }
        .timeline-section h4 { font-size: 11px; font-weight: 800; color: #555; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 14px; }
        .timeline { display: flex; flex-direction: column; }
        .timeline-item { display: flex; align-items: flex-start; gap: 12px; position: relative; padding-bottom: 14px; }
        .timeline-item:last-child { padding-bottom: 0; }
        .timeline-item:not(:last-child)::before {
            content: ''; position: absolute; left: 11px; top: 22px; bottom: 0;
            width: 2px; background: #e0e0e0; z-index: 0;
        }
        .timeline-dot-wrap { position: relative; z-index: 1; flex-shrink: 0; padding-top: 2px; }
        .timeline-dot {
            width: 22px; height: 22px; border-radius: 50%;
            border: 2.5px solid var(--dot-color, #aaa); background: var(--dot-bg, #f5f5f5);
            display: flex; align-items: center; justify-content: center;
        }
        .timeline-dot .dot-inner { width: 8px; height: 8px; border-radius: 50%; background: var(--dot-color, #aaa); }
        .timeline-content { flex: 1; }
        .timeline-status-label {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 12px; font-weight: 800; padding: 2px 10px;
            border-radius: 10px; border: 1px solid transparent; margin-bottom: 3px;
        }
        .timeline-timestamp { font-size: 11px; color: #888; font-weight: 600; }
        .timeline-timestamp span { color: #555; font-weight: 700; }
        .timeline-role-badge {
            display: inline-block; font-size: 10px; font-weight: 800;
            padding: 1px 7px; border-radius: 8px;
            background: #e8e8e8; border: 1px solid #ccc; color: #555;
            vertical-align: middle; margin-left: 2px;
        }
        .timeline-empty { font-size: 12px; color: #aaa; font-style: italic; }

        .tl-scheduled .timeline-dot { --dot-color: #2196f3; --dot-bg: #dbeeff; }
        .tl-scheduled .timeline-status-label { background: #dbeeff; color: #004085; border-color: #90c8ff; }
        .tl-confirmed .timeline-dot { --dot-color: #7c3aed; --dot-bg: #ede9fe; }
        .tl-confirmed .timeline-status-label { background: #ede9fe; color: #3b0764; border-color: #c4b5fd; }
        .tl-completed .timeline-dot { --dot-color: #27ae60; --dot-bg: #e8f5e9; }
        .tl-completed .timeline-status-label { background: #e8f5e9; color: #155724; border-color: #81c784; }
        .tl-no_show   .timeline-dot { --dot-color: #f59e0b; --dot-bg: #fffbeb; }
        .tl-no_show   .timeline-status-label { background: #fffbeb; color: #78350f; border-color: #fcd34d; }
        .tl-canceled  .timeline-dot { --dot-color: #e53935; --dot-bg: #fde8e8; }
        .tl-canceled  .timeline-status-label { background: #fde8e8; color: #842029; border-color: #f5a5a5; }
        .tl-cancelled .timeline-dot { --dot-color: #e53935; --dot-bg: #fde8e8; }
        .tl-cancelled .timeline-status-label { background: #fde8e8; color: #842029; border-color: #f5a5a5; }

        /* ── Status update section ── */
        .status-update-section { border-top: 1.5px solid #eee; padding: 14px 18px 16px; }
        .status-update-section > label {
            font-size: 12px; font-weight: 800; color: #333; display: block;
            margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.04em;
        }
        .status-row { display: flex; align-items: center; gap: 8px; }
        .status-select-modal {
            flex: 1; font-size: 13px; font-weight: 700; font-family: 'Figtree', sans-serif;
            padding: 8px 12px; border: 1.5px solid #ccc; border-radius: 6px;
            background: #f8f8f8; color: #222; cursor: pointer; outline: none;
            transition: border-color 0.15s, background 0.15s; appearance: auto;
        }
        .status-select-modal:focus    { border-color: #2196f3; background: #fff; }
        .status-select-modal:disabled { background: #eee; color: #aaa; cursor: not-allowed; }
        .btn-save-status {
            font-size: 12px; font-weight: 800; padding: 8px 20px; border-radius: 6px;
            border: 1.5px solid #81c784; background: #e8f5e9; color: #155724;
            cursor: pointer; transition: background 0.15s; white-space: nowrap; font-family: 'Figtree', sans-serif;
        }
        .btn-save-status:hover:not(:disabled) { background: #c8e6c9; }
        .btn-save-status:disabled { background: #eee; border-color: #ccc; color: #aaa; cursor: not-allowed; }
        #saving-indicator { font-size: 12px; font-weight: 700; color: #2196f3; display: none; }

        .btn-close-modal {
            background: #e0e0e0; color: #333; border: 1.5px solid #bbb; border-radius: 5px;
            padding: 9px 24px; font-size: 13px; font-family: 'Figtree', sans-serif;
            font-weight: 700; cursor: pointer; transition: background 0.15s;
        }
        .btn-close-modal:hover { background: #ccc; }

        @media (max-width: 700px) {
            .sidebar { width: 130px; min-width: 130px; }
            .modal   { max-width: 96vw; }
        }
    </style>
</head>
<body>

{{-- ══════ SIDEBAR ══════ --}}
<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="{{ asset('images/PVAS.png') }}" alt="PVAS Logo">
    </div>
    <div class="sidebar-nav">
        <a href="{{ route('vet.dashboard') }}"
           class="{{ request()->routeIs('vet.dashboard') ? 'active' : '' }}">Dashboard</a>

        <a href="{{ route('vet.appointments.index') }}"
           class="{{ request()->routeIs('vet.appointments.*') ? 'active' : '' }}">Appointments</a>

        <a href="{{ \Route::has('vet.reports.index') ? route('vet.reports.index') : '#' }}"
           class="{{ request()->routeIs('vet.reports.*') ? 'active' : '' }}">Reports</a>

        <div class="sidebar-spacer"></div>

        <a href="{{ route('logout') }}" class="logout"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
    </div>
    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>
</aside>

{{-- ══════ MAIN ══════ --}}
<main class="main">

    {{-- ── Page Header ── --}}
    <div class="page-header">
        <h1>Appointments</h1>

        @php
            $base = route('vet.appointments.index');

            $statusMap = [
                ''          => 'All',
                'scheduled' => 'Scheduled',
                'confirmed' => 'Confirmed',
                'no_show'   => 'No Show',
                'canceled'  => 'Cancelled',
            ];

            $currentStatus       = $selectedStatus    ?? '';
            $currentDate         = $selectedDate      ?? '';
            $currentType         = $selectedType      ?? '';
            // $roleAllowedTypes is passed from the controller; null means all types
            $roleTypes           = $roleAllowedTypes  ?? \App\Models\Appointment::TYPES;
        @endphp

        {{-- Status filter pills --}}
        <div class="status-filters">
            @foreach($statusMap as $key => $label)
                @php
                    $isActive = $currentStatus === $key;
                    $pillKey  = $key === '' ? 'all' : $key;
                    $href     = $base . '?' . http_build_query(array_filter([
                        'status' => $key ?: null,
                        'date'   => $currentDate ?: null,
                        'type'   => $currentType  ?: null,
                    ]));
                @endphp
                <a href="{{ $href }}"
                   class="filter-pill {{ $isActive ? 'active-' . $pillKey : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Type filter — only shows types the current role is allowed to access --}}
        @if(count($roleTypes) > 1)
            <form method="GET" action="{{ $base }}" id="type-form">
                @if($currentStatus) <input type="hidden" name="status" value="{{ $currentStatus }}"> @endif
                @if($currentDate)   <input type="hidden" name="date"   value="{{ $currentDate }}">   @endif
                <div class="type-filter-wrapper">
                    <label for="type-input">Type:</label>
                    <select id="type-input" name="type"
                            onchange="document.getElementById('type-form').submit()">
                        <option value="">All</option>
                        @foreach($roleTypes as $t)
                            <option value="{{ $t }}" {{ $currentType === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                    @if($currentType)
                        <button type="button" class="type-clear-btn"
                                onclick="document.getElementById('type-input').value='';
                                         document.getElementById('type-form').submit();"
                                title="Clear">&#10005;</button>
                    @endif
                </div>
            </form>
        @endif

        {{-- Date picker --}}
        <form method="GET" action="{{ $base }}" id="date-filter-form">
            @if($currentStatus) <input type="hidden" name="status" value="{{ $currentStatus }}"> @endif
            @if($currentType)   <input type="hidden" name="type"   value="{{ $currentType }}">   @endif
            <div class="date-filter-wrapper">
                <label for="date-picker">Date:</label>
                <input type="date" id="date-picker" name="date"
                       value="{{ $currentDate }}"
                       onchange="this.form.submit()">
                @if($currentDate)
                    <button type="button" class="date-clear-btn"
                            onclick="document.getElementById('date-picker').value='';
                                     document.getElementById('date-filter-form').submit();"
                            title="Clear">&#10005;</button>
                @endif
            </div>
        </form>
    </div>

    {{-- ── Page Body ── --}}
    <div class="page-body">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="flash flash-success">✓ {{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="flash flash-info">ℹ {{ session('info') }}</div>
        @endif
        @if(session('error'))
            <div class="flash flash-error">⚠ {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="flash flash-error">
                @foreach($errors->all() as $e){{ $e }}<br>@endforeach
            </div>
        @endif

        {{-- ── Role-scope info banner ──
             Tells the user exactly which appointment types they can see and manage.
        ── --}}
        @php
            $roleScopeLabel = match($user->role) {
                'veterinarian'  => 'You can view and manage',
                'groomer'       => 'You can view and manage',
                'vet_nurse'     => 'You can view and manage',
                'vet_assistant' => 'As an assistant you can view and manage',
                default         => 'You can view and manage',
            };
        @endphp
        <div class="role-scope-banner">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/>
            </svg>
            <span>
                <strong>{{ ucwords(str_replace('_', ' ', $user->role)) }}</strong>
                — {{ $roleScopeLabel }}:
            </span>
            <div class="type-chips">
                @foreach($roleTypes as $t)
                    <span class="type-chip type-chip-{{ $t }}">{{ $t }}</span>
                @endforeach
            </div>
            @if($user->role === 'veterinarian')
                &nbsp;<span style="opacity:.7;font-size:11px;">(your assignments only)</span>
            @endif
        </div>

        {{-- ── New-appointment alert banner ── --}}
        @php
            $newCount = $appointments->filter(function ($a) {
                return strtolower($a->status) === 'scheduled'
                    && \Carbon\Carbon::parse($a->created_at)->isAfter(now()->subHours(24));
            })->count();
        @endphp

        @if($newCount > 0 && !$currentStatus && !$currentDate)
            <div class="new-appt-alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <span>
                    <strong>{{ $newCount }} new appointment{{ $newCount !== 1 ? 's' : '' }}</strong>
                    assigned in the last 24 hours — rows highlighted in yellow below.
                </span>
                <a href="{{ $base }}?status=scheduled">View Scheduled only →</a>
            </div>
        @endif

        {{-- Filter banner --}}
        @if($currentStatus || $currentDate || $currentType)
            @php
                $filterStatusLabel = match($currentStatus) {
                    'no_show'  => 'No Show',
                    'canceled' => 'Cancelled',
                    default    => ucfirst($currentStatus),
                };
            @endphp
            <div class="filter-banner">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                Filtered by:
                @if($currentStatus)<strong>{{ $filterStatusLabel }}</strong>@endif
                @if($currentType)@if($currentStatus) &nbsp;·&nbsp; @endif<strong>{{ $currentType }}</strong>@endif
                @if($currentDate)@if($currentStatus || $currentType) &nbsp;·&nbsp; @endif<strong>{{ \Carbon\Carbon::parse($currentDate)->format('F d, Y') }}</strong>@endif
                &nbsp;—&nbsp;<a href="{{ $base }}" style="color:#2196f3;font-weight:800;text-decoration:none;">Clear all</a>
            </div>
        @endif

        {{-- Top action bar --}}
        <div class="top-action-bar">
            <div class="list-header">
                <span class="list-title">Appointment List</span>
                <span class="record-badge" id="record-count-badge">
                    {{ $totalAppointments }} {{ $totalAppointments === 1 ? 'record' : 'records' }}
                </span>
            </div>

            <div class="role-info-pill">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                @if($user->role === 'veterinarian')
                    Showing your assigned appointments
                @else
                    {{ ucwords(str_replace('_', ' ', $user->role)) }} — all clinic appointments
                @endif
            </div>
        </div>

        {{-- Appointments table --}}
        <div class="table-card">
            <div class="table-scroll">
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Date &amp; Time</th>
                            <th>Customer</th>
                            <th>Pet</th>
                            <th>Veterinarian</th>
                            <th>Type</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="appointments-tbody">
                        @forelse($appointments as $appt)
                            @php
                                $normSt = strtolower($appt->status);
                                $normSt = $normSt === 'cancelled' ? 'canceled' : $normSt;

                                $badgeClass = match($normSt) {
                                    'scheduled'            => 'badge-scheduled',
                                    'confirmed'            => 'badge-confirmed',
                                    'no_show'              => 'badge-no-show',
                                    'canceled','cancelled' => 'badge-cancelled',
                                    default                => 'badge-scheduled',
                                };
                                $statusLabel = match($normSt) {
                                    'no_show'              => 'No Show',
                                    'canceled','cancelled' => 'Cancelled',
                                    default                => ucfirst($appt->status),
                                };

                                $isNew = strtolower($appt->status) === 'scheduled'
                                      && \Carbon\Carbon::parse($appt->created_at)->isAfter(now()->subHours(24));
                            @endphp
                            <tr data-appt-id="{{ $appt->id }}" class="{{ $isNew ? 'row-new' : '' }}">
                                <td>
                                    <span class="appt-id">#{{ $appt->id }}</span>
                                    @if($isNew)
                                        <span class="new-tag">New</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dt-date">{{ $appt->scheduled_date->format('M d, Y') }}</div>
                                    <div class="dt-time">{{ \Carbon\Carbon::parse($appt->scheduled_time)->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div class="cust-name">
                                        {{ $appt->customer?->first_name }} {{ $appt->customer?->last_name ?? '—' }}
                                    </div>
                                    <div class="cust-id">ID: {{ $appt->customer?->id ?? '—' }}</div>
                                </td>
                                <td>{{ $appt->pet?->pet_name ?? '—' }}</td>
                                <td>{{ $appt->veterinarian?->name ?? '—' }}</td>
                                <td>
                                    @if(!empty($appt->type))
                                        <span class="type-pill type-{{ $appt->type }}">{{ $appt->type }}</span>
                                    @else
                                        <span style="color:#ccc;">—</span>
                                    @endif
                                </td>
                                <td style="max-width:160px; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $appt->reason_for_visit ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }}" id="badge-{{ $appt->id }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td>
                                    {{-- Vet-side: View only. Add / Edit / Delete belongs to Receptionist/Admin. --}}
                                    <div class="action-btns">
                                        <button class="btn-view-action"
                                                onclick="openDetail({{ $appt->id }})">View</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-row" id="empty-row">
                                <td colspan="9">
                                    No appointments found
                                    @if($currentStatus) with status <strong>{{ $filterStatusLabel ?? ucfirst($currentStatus) }}</strong>@endif
                                    @if($currentType) of type <strong>{{ $currentType }}</strong>@endif
                                    @if($currentDate) on <strong>{{ \Carbon\Carbon::parse($currentDate)->format('M d, Y') }}</strong>@endif.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- /.page-body --}}
</main>

{{-- ══════ VIEW / DETAILS MODAL ══════ --}}
<div class="modal-overlay" id="detailModal">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h2>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/>
                </svg>
                Appointment Details
            </h2>
            <button class="btn-close" onclick="closeModal('detailModal')">&#10005;</button>
        </div>

        <div id="modal-content" style="overflow-y:auto; flex:1;">
            <div class="modal-loading">Loading…</div>
        </div>

        <div class="modal-footer">
            <button class="btn-close-modal" onclick="closeModal('detailModal')">Close</button>
        </div>
    </div>
</div>

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    let activeId = null;

    /* ════════════════════════════════════════════════
       Role-allowed types injected from PHP.
       Used in JS only for display hints in the modal;
       the server enforces the real gate.
    ════════════════════════════════════════════════ */
    const ROLE_ALLOWED_TYPES = @json($roleTypes);

    /* ════════════════════════════════════════════════
       MODAL HELPERS
    ════════════════════════════════════════════════ */
    function openModal(id)  { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => {
            if (e.target === overlay) overlay.classList.remove('active');
        });
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(el => closeModal(el.id));
        }
    });

    /* ════════════════════════════════════════════════
       STATUS CONFIG  (matches receptionist and admin JS)
    ════════════════════════════════════════════════ */
    const statusConfig = {
        scheduled : { label: 'Scheduled', bg: '#dbeeff', color: '#004085', border: '#90c8ff', dot: '#2196f3' },
        confirmed : { label: 'Confirmed', bg: '#ede9fe', color: '#3b0764', border: '#c4b5fd', dot: '#7c3aed' },
        completed : { label: 'Completed', bg: '#e8f5e9', color: '#155724', border: '#81c784', dot: '#27ae60' },
        no_show   : { label: 'No Show',   bg: '#fffbeb', color: '#78350f', border: '#fcd34d', dot: '#f59e0b' },
        canceled  : { label: 'Cancelled', bg: '#fde8e8', color: '#842029', border: '#f5a5a5', dot: '#e53935' },
        cancelled : { label: 'Cancelled', bg: '#fde8e8', color: '#842029', border: '#f5a5a5', dot: '#e53935' },
    };

    const badgeClassMap = {
        scheduled : 'badge-scheduled',
        confirmed : 'badge-confirmed',
        no_show   : 'badge-no-show',
        canceled  : 'badge-cancelled',
        cancelled : 'badge-cancelled',
    };

    function normStatus(s) {
        if (!s) return s;
        const l = s.toLowerCase();
        return l === 'cancelled' ? 'canceled' : l;
    }

    function escHtml(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ════════════════════════════════════════════════
       BUILD MODAL CONTENT

       Vet-specific differences from receptionist modal:
         1. "Cancelled" is never in the dropdown (vets cannot cancel).
         2. no_show is terminal — dropdown is hidden entirely (same as locked).
         3. Cancelled appointments show a danger notice.
         4. allowed_next from the server drives which options are selectable.
    ════════════════════════════════════════════════ */
    function buildModalContent(data) {
        const currentNorm = normStatus(data.status);
        const isLocked    = data.is_locked || currentNorm === 'completed';
        const isCancelled = currentNorm === 'canceled' || currentNorm === 'cancelled';
        const isConfirmed = currentNorm === 'confirmed';
        const isNoShow    = currentNorm === 'no_show';
        const history     = data.status_history || data.status_histories || [];

        // Allowed next statuses from the server (vet transition rules)
        const allowedNext = (data.allowed_next || []).map(s => normStatus(s));

        /* ── Detail rows ── */
        const details = [
            ['Appointment ID',   '#' + data.id],
            ['Date & Time',      (data.scheduled_date || '—') + (data.scheduled_time ? '  ' + data.scheduled_time : '')],
            ['Customer',         (data.customer_name || '—') + '  (ID: ' + (data.customer_id || '—') + ')'],
            ['Pet',              (data.pet_name || '—') + (data.pet_id && data.pet_id !== '—' ? '  (ID: ' + data.pet_id + ')' : '')],
            ['Species / Breed',  [data.species, data.breed].filter(v => v && v !== '—').join(' / ') || '—'],
            ['Gender / Color',   [data.gender,  data.color ].filter(v => v && v !== '—').join(' / ') || '—'],
            ['Weight',           data.weight && data.weight !== '—' ? data.weight + ' kg' : '—'],
            ['Veterinarian',     data.vet_name || data.staff_name || '—'],
            ['Type',             data.type || '—'],
            ['Address',          data.address || '—'],
            ['Contact Number',   data.contact_number || '—'],
            ['Reason for Visit', data.reason_for_visit || '—'],
        ];

        const detailHTML = details.map(([label, value]) =>
            `<div class="detail-row">
                <span class="detail-label">${escHtml(label)}</span>
                <span class="detail-value">${escHtml(value)}</span>
            </div>`
        ).join('');

        /* ── Lock / warning notices ── */
        let lockNoticeHTML = '';
        if (isLocked) {
            lockNoticeHTML = `<div class="lock-notice">🔒 This appointment is <strong>completed</strong> and cannot be modified. View full details in Reports.</div>`;
        } else if (isCancelled) {
            lockNoticeHTML = `<div class="lock-notice-danger">✕ This appointment has been <strong>Cancelled</strong> by admin or reception and cannot be modified.</div>`;
        } else if (isConfirmed) {
            lockNoticeHTML = `<div class="lock-notice-warn">⚠ This appointment is <strong>confirmed</strong>. You may mark it No Show or Completed.</div>`;
        } else if (isNoShow) {
            lockNoticeHTML = `<div class="lock-notice-warn">⚠ This appointment is marked <strong>No Show</strong>. It cannot be updated further.</div>`;
        }

        /* ── Timeline ── */
        let timelineHTML = '';
        if (history.length > 0) {
            timelineHTML = history.map(item => {
                const norm = normStatus(item.status);
                const sc   = statusConfig[norm] || { label: item.status, bg: '#f5f5f5', color: '#555', border: '#ccc', dot: '#aaa' };
                const lbl  = item.status_label || sc.label;
                let dateDisplay = '—', timeDisplay = '';
                if (item.changed_at) {
                    try {
                        const dt = new Date(item.changed_at);
                        if (!isNaN(dt)) {
                            dateDisplay = dt.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                            timeDisplay = dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                        } else { dateDisplay = item.changed_at; }
                    } catch(e) { dateDisplay = item.changed_at; }
                }
                const roleLabel = item.changed_by ?? 'System';
                return `<div class="timeline-item tl-${norm}">
                    <div class="timeline-dot-wrap">
                        <div class="timeline-dot"><div class="dot-inner"></div></div>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-status-label"
                             style="background:${sc.bg};color:${sc.color};border-color:${sc.border};">
                            ${escHtml(lbl)}
                        </div>
                        <div class="timeline-timestamp">
                            <span>${escHtml(dateDisplay)}</span>
                            ${timeDisplay ? ' &nbsp;·&nbsp; <span>' + escHtml(timeDisplay) + '</span>' : ''}
                            &nbsp;·&nbsp; by <span class="timeline-role-badge">${escHtml(roleLabel)}</span>
                        </div>
                    </div>
                </div>`;
            }).join('');
        } else {
            timelineHTML = `<div class="timeline-empty">No status history recorded.</div>`;
        }

        /*
         * ── Status update dropdown ─────────────────────────────────────────
         * Hidden entirely when appointment is terminal (locked/cancelled/no_show).
         *
         * "Cancelled" is intentionally excluded — vet staff cannot cancel;
         * that action belongs to receptionist/admin only.
         *
         * Options disabled when:
         *   - Already used (✗ prefix)
         *   - Current status (● prefix)
         *   - Not in server-supplied allowed_next list (✗ prefix)
         * ──────────────────────────────────────────────────────────────────
         */
        const isTerminal = isLocked || isCancelled || isNoShow;
        const usedStatuses = new Set(history.map(h => normStatus(h.status)));

        // Vet-visible statuses: "canceled" deliberately excluded
        const vetStatuses = [
            { val: 'scheduled', label: 'Scheduled' },
            { val: 'confirmed', label: 'Confirmed' },
            { val: 'no_show',   label: 'No Show' },
            { val: 'completed', label: 'Completed → Reports' },
        ];

        let statusUpdateHTML = '';
        if (!isTerminal) {
            let optionsHTML = `<option value="" disabled selected>— Select new status —</option>`;
            vetStatuses.forEach(s => {
                const isUsed    = usedStatuses.has(s.val);
                const isCurrent = s.val === currentNorm;
                // Not in allowed_next means the server-side transition rules block it
                const isBlocked = !isUsed && !isCurrent && !allowedNext.includes(s.val);
                const disabled  = (isUsed || isCurrent || isBlocked) ? 'disabled' : '';
                const prefix    = isCurrent ? '● ' : (isUsed || isBlocked ? '✗ ' : '');
                optionsHTML += `<option value="${s.val}" ${disabled}>${prefix}${escHtml(s.label)}</option>`;
            });

            statusUpdateHTML =
                `<div class="status-update-section">
                    <label>Update Status</label>
                    <div class="status-row">
                        <select class="status-select-modal" id="modal-status-select"
                                onchange="onStatusDropdownChange(this)">
                            ${optionsHTML}
                        </select>
                        <button class="btn-save-status" id="btn-save-status"
                                onclick="saveStatus()" disabled>Save</button>
                        <span id="saving-indicator">Saving…</span>
                    </div>
                </div>`;
        }

        return `<div class="modal-body">${detailHTML}${lockNoticeHTML}</div>` +
               `<div class="timeline-section"><h4>Status History</h4><div class="timeline">${timelineHTML}</div></div>` +
               statusUpdateHTML;
    }

    /* Enable Save button only when a non-placeholder option is chosen */
    function onStatusDropdownChange(sel) {
        const btn = document.getElementById('btn-save-status');
        if (btn) btn.disabled = !sel.value;
    }

    /* ════════════════════════════════════════════════
       OPEN DETAIL MODAL  (AJAX)
    ════════════════════════════════════════════════ */
    function openDetail(id) {
        activeId = id;
        document.getElementById('modal-content').innerHTML = '<div class="modal-loading">Loading…</div>';
        openModal('detailModal');

        fetch(`/vet/appointments/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept'          : 'application/json',
                'X-CSRF-TOKEN'    : CSRF,
            }
        })
        .then(r => {
            if (!r.ok) throw new Error('Server error ' + r.status);
            return r.json();
        })
        .then(data => {
            if (data.error) {
                document.getElementById('modal-content').innerHTML =
                    `<div class="modal-loading" style="color:#e53935;">${escHtml(data.error)}</div>`;
                return;
            }
            document.getElementById('modal-content').innerHTML = buildModalContent(data);
        })
        .catch(() => {
            document.getElementById('modal-content').innerHTML =
                '<div class="modal-loading" style="color:#e53935;">Could not load appointment details. Please try again.</div>';
        });
    }

    /* ════════════════════════════════════════════════
       SAVE STATUS  (AJAX PATCH)
    ════════════════════════════════════════════════ */
    function saveStatus() {
        const sel       = document.getElementById('modal-status-select');
        const saveBtn   = document.getElementById('btn-save-status');
        const saving    = document.getElementById('saving-indicator');
        const newStatus = sel?.value;
        if (!newStatus || !saveBtn) return;

        saveBtn.disabled     = true;
        saving.style.display = 'inline';

        fetch(`/vet/appointments/${activeId}/status`, {
            method : 'PATCH',
            headers: {
                'Content-Type'    : 'application/json',
                'Accept'          : 'application/json',
                'X-CSRF-TOKEN'    : CSRF,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ status: newStatus }),
        })
        .then(r => r.json())
        .then(data => {
            saving.style.display = 'none';

            if (data.error) {
                alert(data.error);
                if (saveBtn) saveBtn.disabled = false;
                return;
            }

            if (data.success) {
                closeModal('detailModal');
                const row = document.querySelector(`tr[data-appt-id="${activeId}"]`);

                if (data.remove_from_list) {
                    // Completed → animate row out then update count
                    if (row) {
                        row.classList.add('row-removing');
                        row.addEventListener('animationend', () => {
                            row.remove();
                            updateRecordCount();
                            showEmptyRowIfNeeded();
                        }, { once: true });
                    }
                } else {
                    // Just update the badge in-place
                    if (row) {
                        const badge  = row.querySelector(`#badge-${activeId}`);
                        const norm   = normStatus(data.status);
                        const sc     = statusConfig[norm] || {};
                        const bClass = badgeClassMap[norm] || 'badge-scheduled';
                        if (badge) {
                            badge.className   = 'badge ' + bClass;
                            badge.textContent = sc.label || data.label;
                        }
                        // Clear the NEW highlight once the vet has interacted with the appointment
                        row.classList.remove('row-new');
                        const newTag = row.querySelector('.new-tag');
                        if (newTag) newTag.remove();
                    }
                }
            }
        })
        .catch(() => {
            saving.style.display = 'none';
            if (saveBtn) saveBtn.disabled = false;
            alert('Failed to update status. Please try again.');
        });
    }

    /* ════════════════════════════════════════════════
       RECORD COUNT
    ════════════════════════════════════════════════ */
    function updateRecordCount() {
        const count = document.querySelectorAll('#appointments-tbody tr[data-appt-id]').length;
        const badge = document.getElementById('record-count-badge');
        if (badge) badge.textContent = count + ' ' + (count === 1 ? 'record' : 'records');
    }

    function showEmptyRowIfNeeded() {
        const count    = document.querySelectorAll('#appointments-tbody tr[data-appt-id]').length;
        const existing = document.getElementById('empty-row');
        if (count === 0 && !existing) {
            const tbody = document.getElementById('appointments-tbody');
            const tr    = document.createElement('tr');
            tr.id        = 'empty-row';
            tr.className = 'empty-row';
            tr.innerHTML = '<td colspan="9">No appointments found.</td>';
            tbody.appendChild(tr);
        }
    }
</script>

</body>
</html>