<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PVAS - Appointments</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body { font-family: 'Figtree', sans-serif; background: #d0d0d0; min-height: 100vh; display: flex; }

        /* ── Sidebar ── */
        .sidebar {
            width: 180px; min-width: 180px; background: #ffffff;
            border-right: 2px solid #808080; display: flex; flex-direction: column;
            align-items: center; padding: 24px 0 16px; position: sticky; top: 0; height: 100vh;
        }
        .sidebar-logo { width: 120px; height: 100px; margin-bottom: 28px; display: flex; align-items: center; justify-content: center; }
        .sidebar-logo img { width: 100%; height: 100%; object-fit: contain; }
        .sidebar nav { width: 100%; display: flex; flex-direction: column; }
        .sidebar nav a {
            display: block; width: 100%; padding: 14px 0; text-align: center;
            font-size: 15px; font-weight: 700; color: #111; text-decoration: none;
            border-top: 1.5px solid #bbb; transition: background 0.15s;
        }
        .sidebar nav a:hover  { background: #e0e0e0; }
        .sidebar nav a.active { background: #d0d0d0; }
        .sidebar nav a.logout { color: #cc0000; border-top: 1.5px solid #bbb; border-bottom: 1.5px solid #bbb; }
        .sidebar-spacer { flex: 1; }

        /* ── Main ── */
        .main { flex: 1; display: flex; flex-direction: column; background: #c8c8c8; min-height: 100vh; }

        .page-header {
            background: #c8c8c8; border-bottom: 2px solid #999; padding: 10px 20px;
            display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 56px;
            flex-wrap: wrap;
        }
        .page-header h1 { font-size: 20px; font-weight: 800; color: #111; }
        .header-filters { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

        .filter-wrapper {
            display: flex; align-items: center; gap: 6px;
            background: #d8d8d8; border: 1.5px solid #999; border-radius: 4px; padding: 4px 10px;
        }
        .filter-wrapper label { font-size: 13px; font-weight: 700; color: #333; white-space: nowrap; }
        .filter-wrapper input[type="date"] {
            border: none; background: transparent; padding: 2px 4px;
            font-size: 13px; font-family: 'Figtree', sans-serif; font-weight: 700;
            cursor: pointer; color: #111; outline: none;
        }
        .filter-wrapper input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0.7; }
        .btn-clear {
            font-size: 11px; font-weight: 700; color: #888; background: none; border: none;
            cursor: pointer; padding: 2px 4px; border-radius: 3px; transition: color 0.15s, background 0.15s;
        }
        .btn-clear:hover { color: #e53935; background: #fde8e8; }

        /* ── Status tabs ── */
        .status-tabs { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
        .status-tab {
            font-size: 11px; font-weight: 800; padding: 4px 12px; border-radius: 12px;
            border: 1.5px solid #bbb; background: #d8d8d8; color: #555;
            cursor: pointer; text-decoration: none; transition: background 0.15s, border-color 0.15s, color 0.15s; white-space: nowrap;
        }
        .status-tab:hover { background: #ccc; }
        .status-tab.active-all               { background: #333; border-color: #333; color: #fff; }
        .status-tab.active.tab-scheduled     { background: #2196f3; border-color: #2196f3; color: #fff; }
        .status-tab.active.tab-confirmed     { background: #7c3aed; border-color: #7c3aed; color: #fff; }
        .status-tab.active.tab-no-show       { background: #f59e0b; border-color: #f59e0b; color: #fff; }
        .status-tab.active.tab-cancelled     { background: #e53935; border-color: #e53935; color: #fff; }

        .body-area { padding: 16px; display: flex; flex-direction: column; gap: 12px; }

        .flash { display: flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 4px; font-size: 13px; font-weight: 700; }
        .flash-success { background: #e8f5e9; border: 1px solid #81c784; color: #155724; }
        .flash-error   { background: #fde8e8; border: 1px solid #f5a5a5; color: #842029; }
        .flash-info    { background: #e3f2fd; border: 1px solid #90caf9; color: #0d47a1; }

        .filter-banner {
            display: flex; align-items: center; gap: 8px;
            background: #d4d4d4; border: 1px solid #bbb; border-radius: 4px;
            padding: 6px 14px; font-size: 12px; font-weight: 700; color: #555;
        }
        .filter-banner strong { color: #222; }

        .top-action-bar { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .list-header    { display: flex; align-items: center; gap: 10px; }
        .list-title     { font-size: 16px; font-weight: 800; color: #111; }

        .btn-add-appointment {
            display: inline-flex; align-items: center; gap: 6px;
            background: #d4d4d4; border: 1.5px solid #888; border-radius: 6px;
            padding: 9px 18px; font-size: 14px; font-weight: 800; color: #111;
            cursor: pointer; transition: background 0.15s; font-family: 'Figtree', sans-serif; white-space: nowrap;
        }
        .btn-add-appointment:hover { background: #c0c0c0; }
        .btn-add-appointment .plus-icon { font-size: 18px; font-weight: 400; line-height: 1; }

        .table-card { background: #fff; border: 1.5px solid #ccc; border-radius: 6px; overflow: hidden; }
        .count-tag {
            font-size: 11px; font-weight: 700; color: #666;
            background: #e4e4e4; border: 1px solid #ccc; border-radius: 10px; padding: 2px 10px;
        }

        table.appt-table { width: 100%; border-collapse: collapse; }
        table.appt-table thead th {
            padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 800; color: #333;
            background: #f8f8f8; border-bottom: 1.5px solid #ddd;
            text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;
        }
        table.appt-table tbody td {
            padding: 10px 14px; font-size: 13px; color: #333;
            border-bottom: 1px solid #eee; white-space: nowrap; vertical-align: middle;
        }
        table.appt-table tbody tr:last-child td { border-bottom: none; }
        table.appt-table tbody tr:hover td { background: #f5f5f5; }

        /* ── Status badge ── */
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

        /* ── Type pill ── */
        .type-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .type-Checkup     { background: #d1ecf1; color: #0c5460; }
        .type-Vaccination { background: #d4edda; color: #155724; }
        .type-Surgery     { background: #f8d7da; color: #842029; }
        .type-Grooming    { background: #fde8d8; color: #7b3a0e; }

        /* ── Action buttons ── */
        .action-cell { display: flex; align-items: center; gap: 6px; }
        .btn-view {
            font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 4px;
            border: 1.5px solid #90c8ff; background: #dbeeff; color: #004085;
            cursor: pointer; text-decoration: none; display: inline-block; transition: background 0.15s;
            font-family: 'Figtree', sans-serif;
        }
        .btn-view:hover { background: #bfddff; }
        .btn-edit {
            font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 4px;
            border: 1.5px solid #93c5fd; background: #dbeafe; color: #1d4ed8;
            cursor: pointer; display: inline-block; transition: background 0.15s; font-family: 'Figtree', sans-serif;
        }
        .btn-edit:hover { background: #bfdbfe; }
        .btn-delete {
            font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 4px;
            border: 1.5px solid #f5a5a5; background: #fde8e8; color: #842029;
            cursor: pointer; display: inline-block; transition: background 0.15s; font-family: 'Figtree', sans-serif;
        }
        .btn-delete:hover { background: #fcc8c8; }

        .empty-row td { text-align: center; color: #888; font-style: italic; padding: 36px 0; font-size: 13px; }

        /* ── Row removal animation ── */
        @keyframes rowFadeOut {
            from { opacity: 1; transform: translateX(0); }
            to   { opacity: 0; transform: translateX(40px); }
        }
        .row-removing { animation: rowFadeOut 0.35s ease forwards; pointer-events: none; }

        /* ════════════════════════════
           MODALS
        ════════════════════════════ */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.48); z-index: 999;
            align-items: flex-start; justify-content: center;
            padding: 40px 16px; overflow-y: auto;
        }
        .modal-overlay.open { display: flex; }

        .modal-box {
            background: #e8e8e8; border: 2px solid #aaa; border-radius: 8px;
            width: 560px; max-width: 95vw;
            box-shadow: 0 8px 40px rgba(0,0,0,0.28); overflow: hidden; margin-bottom: 40px;
            display: flex; flex-direction: column; max-height: 90vh;
        }
        .modal-box-lg { width: 700px; }
        .modal-box-sm { width: 400px; }

        .modal-head {
            background: #d8d8d8; border-bottom: 1.5px solid #bbb;
            padding: 13px 18px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
        }
        .modal-head h3 { font-size: 15px; font-weight: 800; color: #111; display: flex; align-items: center; gap: 8px; }
        .modal-close-btn { background: none; border: none; font-size: 22px; cursor: pointer; color: #555; line-height: 1; padding: 0 4px; border-radius: 3px; transition: background 0.15s; }
        .modal-close-btn:hover { color: #000; background: rgba(0,0,0,0.08); }

        .modal-body { padding: 16px 18px; display: flex; flex-direction: column; gap: 10px; overflow-y: auto; flex: 1; }
        .modal-foot { padding: 10px 18px 14px; display: flex; justify-content: flex-end; gap: 8px; flex-shrink: 0; border-top: 1.5px solid #ccc; background: #e0e0e0; }

        /* ── Form elements ── */
        .form-section-label {
            font-size: 11px; font-weight: 800; color: #555;
            text-transform: uppercase; letter-spacing: 0.06em;
            border-bottom: 1.5px solid #bbb; padding-bottom: 4px; margin-bottom: -2px;
        }
        .form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .form-row-1 { display: grid; grid-template-columns: 1fr; gap: 10px; }
        .form-input, .form-select, .form-textarea {
            width: 100%; padding: 9px 12px;
            background: #d4d4d4; border: 1.5px solid #aaa; border-radius: 4px;
            font-size: 13px; font-family: 'Figtree', sans-serif; font-weight: 600;
            color: #111; outline: none; transition: border-color 0.15s;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: #666; background: #ccc; }
        .form-input::placeholder, .form-textarea::placeholder { color: #666; font-weight: 600; }
        .form-select { cursor: pointer; appearance: auto; }
        .form-textarea { resize: vertical; min-height: 72px; }
        .field-error { color: #c0392b; font-size: 11px; font-weight: 600; margin-top: 2px; display: block; }
        .btn-save-form {
            background: #2196f3; color: #fff; border: none; border-radius: 5px;
            padding: 9px 32px; font-size: 14px; font-family: 'Figtree', sans-serif;
            font-weight: 700; cursor: pointer; transition: background 0.15s;
        }
        .btn-save-form:hover { background: #1976d2; }

        /* ── View modal — detail rows ── */
        .detail-row { display: flex; gap: 8px; margin-bottom: 12px; font-size: 13px; line-height: 1.5; }
        .detail-label { font-weight: 800; color: #111; min-width: 150px; flex-shrink: 0; }
        .detail-value { color: #444; }

        /* ── Lock / warning notices ── */
        .lock-notice      { display: flex; align-items: center; gap: 8px; background: #f9f9f9; border: 1.5px solid #ddd; border-radius: 4px; padding: 10px 14px; font-size: 12px; font-weight: 700; color: #777; }
        .lock-notice-warn { display: flex; align-items: center; gap: 8px; background: #fffbeb; border: 1.5px solid #fcd34d; border-radius: 4px; padding: 10px 14px; font-size: 12px; font-weight: 700; color: #78350f; }

        /* ── Timeline ── */
        .timeline-section { border-top: 1.5px solid #eee; padding: 16px 18px 4px; }
        .timeline-section h4 { font-size: 11px; font-weight: 800; color: #555; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 14px; }
        .timeline { display: flex; flex-direction: column; }
        .timeline-item { display: flex; align-items: flex-start; gap: 12px; position: relative; padding-bottom: 14px; }
        .timeline-item:last-child { padding-bottom: 0; }
        .timeline-item:not(:last-child)::before { content: ''; position: absolute; left: 11px; top: 22px; bottom: 0; width: 2px; background: #e0e0e0; z-index: 0; }
        .timeline-dot-wrap { position: relative; z-index: 1; flex-shrink: 0; padding-top: 2px; }
        .timeline-dot { width: 22px; height: 22px; border-radius: 50%; border: 2.5px solid var(--dot-color, #aaa); background: var(--dot-bg, #f5f5f5); display: flex; align-items: center; justify-content: center; }
        .timeline-dot .dot-inner { width: 8px; height: 8px; border-radius: 50%; background: var(--dot-color, #aaa); }
        .timeline-content { flex: 1; }
        .timeline-status-label { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 800; padding: 2px 10px; border-radius: 10px; border: 1px solid transparent; margin-bottom: 3px; }
        .timeline-timestamp { font-size: 11px; color: #888; font-weight: 600; }
        .timeline-timestamp span { color: #555; font-weight: 700; }
        .timeline-role-badge { display: inline-block; font-size: 10px; font-weight: 800; padding: 1px 7px; border-radius: 8px; background: #e8e8e8; border: 1px solid #ccc; color: #555; vertical-align: middle; margin-left: 2px; }

        .tl-scheduled .timeline-dot { --dot-color: #2196f3; --dot-bg: #dbeeff; }
        .tl-scheduled .timeline-status-label { background: #dbeeff; color: #004085; border-color: #90c8ff; }
        .tl-confirmed .timeline-dot { --dot-color: #7c3aed; --dot-bg: #ede9fe; }
        .tl-confirmed .timeline-status-label { background: #ede9fe; color: #3b0764; border-color: #c4b5fd; }
        .tl-completed .timeline-dot { --dot-color: #27ae60; --dot-bg: #e8f5e9; }
        .tl-completed .timeline-status-label { background: #e8f5e9; color: #155724; border-color: #81c784; }
        .tl-no_show   .timeline-dot { --dot-color: #f59e0b; --dot-bg: #fffbeb; }
        .tl-no_show   .timeline-status-label { background: #fffbeb; color: #78350f; border-color: #fcd34d; }
        .tl-cancelled .timeline-dot { --dot-color: #e53935; --dot-bg: #fde8e8; }
        .tl-cancelled .timeline-status-label { background: #fde8e8; color: #842029; border-color: #f5a5a5; }
        .tl-canceled  .timeline-dot { --dot-color: #e53935; --dot-bg: #fde8e8; }
        .tl-canceled  .timeline-status-label { background: #fde8e8; color: #842029; border-color: #f5a5a5; }
        .timeline-empty { font-size: 12px; color: #aaa; font-style: italic; }

        /* ── Status update dropdown ── */
        .status-update-section { border-top: 1.5px solid #eee; padding: 14px 18px 16px; }
        .status-update-section > label { font-size: 12px; font-weight: 800; color: #333; display: block; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.04em; }
        .status-row { display: flex; align-items: center; gap: 8px; }
        .status-select-modal {
            flex: 1; font-size: 13px; font-weight: 700; font-family: 'Figtree', sans-serif;
            padding: 8px 12px; border: 1.5px solid #ccc; border-radius: 6px;
            background: #f8f8f8; color: #222; cursor: pointer; outline: none;
            transition: border-color 0.15s, background 0.15s; appearance: auto;
        }
        .status-select-modal:focus { border-color: #2196f3; background: #fff; }
        .status-select-modal:disabled { background: #eee; color: #aaa; cursor: not-allowed; }
        .btn-save-status {
            font-size: 12px; font-weight: 800; padding: 8px 20px; border-radius: 6px;
            border: 1.5px solid #81c784; background: #e8f5e9; color: #155724;
            cursor: pointer; transition: background 0.15s; white-space: nowrap; font-family: 'Figtree', sans-serif;
        }
        .btn-save-status:hover:not(:disabled) { background: #c8e6c9; }
        .btn-save-status:disabled { background: #eee; border-color: #ccc; color: #aaa; cursor: not-allowed; }
        #saving-indicator { font-size: 12px; font-weight: 700; color: #2196f3; display: none; }
        .modal-loading { padding: 40px; text-align: center; color: #aaa; font-size: 13px; font-weight: 700; }

        .btn-close-modal {
            font-size: 13px; font-weight: 700; padding: 7px 20px; border-radius: 4px;
            border: 1.5px solid #bbb; background: #e8e8e8; color: #444;
            cursor: pointer; transition: background 0.15s; font-family: 'Figtree', sans-serif;
        }
        .btn-close-modal:hover { background: #d8d8d8; }

        /* ── Delete modal ── */
        .delete-modal-body { text-align: center; font-size: 14px; color: #444; padding: 28px 26px 10px; }
        .btn-cancel {
            background: #888; color: #fff; border: none; border-radius: 4px;
            padding: 8px 20px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Figtree', sans-serif;
        }
        .btn-cancel:hover { background: #666; }
        .btn-delete-confirm {
            background: #e53935; color: #fff; border: none; border-radius: 4px;
            padding: 8px 20px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Figtree', sans-serif;
        }
        .btn-delete-confirm:hover { background: #c62828; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="{{ asset('images/PVAS.png') }}" alt="PVAS Logo">
    </div>
    <nav>
        <a href="{{ route('dashboard') }}"            class="{{ request()->routeIs('dashboard')        ? 'active' : '' }}">Dashboard</a>
        <a href="{{ \Route::has('customers.index') ? route('customers.index') : '#' }}"
                                                      class="{{ request()->routeIs('customers.*')      ? 'active' : '' }}">Customers</a>
        <a href="{{ route('appointments.index') }}"   class="{{ request()->routeIs('appointments.*')   ? 'active' : '' }}">Appointment</a>
        <a href="{{ \Route::has('staff.index') ? route('staff.index') : '#' }}"
                                                      class="{{ request()->routeIs('staff.*')          ? 'active' : '' }}">Staff</a>
        <a href="{{ \Route::has('reports.index') ? route('reports.index') : '#' }}"
                                                      class="{{ request()->routeIs('reports.*')        ? 'active' : '' }}">Reports</a>
    </nav>
    <div class="sidebar-spacer"></div>
    <nav>
        <a href="#" class="logout"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
    </nav>
    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>
</aside>

<main class="main">

    {{-- ── Page Header ── --}}
    <div class="page-header">
        <h1>Appointments</h1>
        <div class="header-filters">

            @php
                $base = route('appointments.index');
                $tabs = [
                    ''          => 'All',
                    'scheduled' => 'Scheduled',
                    'confirmed' => 'Confirmed',
                    'no_show'   => 'No Show',
                    'canceled'  => 'Cancelled',
                ];
            @endphp

            <div class="status-tabs">
                @foreach($tabs as $val => $label)
                    @php
                        $isActive = ($selectedStatus ?? '') === $val;
                        $cssKey   = $val ? 'tab-' . str_replace(['_', 'canceled'], ['-', 'cancelled'], $val) : '';
                        $href     = $base . '?' . http_build_query(array_filter(['status' => $val ?: null, 'date' => $selectedDate]));
                    @endphp
                    <a href="{{ $href }}"
                       class="status-tab {{ $cssKey }} {{ $isActive ? ($val ? 'active' : 'active-all') : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('appointments.index') }}" id="date-form">
                @if($selectedStatus)
                    <input type="hidden" name="status" value="{{ $selectedStatus }}">
                @endif
                <div class="filter-wrapper">
                    <label for="date-input">Date:</label>
                    <input type="date" id="date-input" name="date" value="{{ $selectedDate ?? '' }}"
                           onchange="document.getElementById('date-form').submit()">
                    @if($selectedDate)
                        <button type="button" class="btn-clear"
                                onclick="document.getElementById('date-input').value=''; document.getElementById('date-form').submit();">✕ Clear</button>
                    @endif
                </div>
            </form>

        </div>
    </div>

    {{-- ── Body ── --}}
    <div class="body-area">

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

        @if($selectedDate || $selectedStatus)
            <div class="filter-banner">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filtered by:
                @if($selectedStatus)<strong>{{ $selectedStatus === 'no_show' ? 'No Show' : ($selectedStatus === 'canceled' ? 'Cancelled' : ucfirst($selectedStatus)) }}</strong>@endif
                @if($selectedDate)@if($selectedStatus) &nbsp;·&nbsp; @endif<strong>{{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</strong>@endif
            </div>
        @endif

        <div class="top-action-bar">
            <div class="list-header">
                <span class="list-title">Appointment List</span>
                <span class="count-tag" id="record-count-tag">{{ count($appointments) }} record{{ count($appointments) !== 1 ? 's' : '' }}</span>
            </div>
            <button class="btn-add-appointment" onclick="openModal('addModal')">
                <span class="plus-icon">&#43;</span> Add Appointment
            </button>
        </div>

        <div class="table-card">
            <table class="appt-table">
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
                <tbody id="appt-tbody">
                    @forelse($appointments as $appt)
                        @continue(strtolower($appt->status) === 'completed')
                        @php
                            $badgeClass = match($appt->status) {
                                'scheduled'            => 'badge-scheduled',
                                'confirmed'            => 'badge-confirmed',
                                'no_show'              => 'badge-no-show',
                                'cancelled','canceled' => 'badge-cancelled',
                                default                => 'badge-scheduled',
                            };
                            $statusLabel = match($appt->status) {
                                'no_show'  => 'No Show',
                                'canceled' => 'Cancelled',
                                default    => ucfirst($appt->status),
                            };
                        @endphp
                        <tr data-appt-id="{{ $appt->id }}">
                            <td style="font-weight:700;color:#555;">#{{ $appt->id }}</td>
                            <td>
                                <div style="font-weight:700;">{{ \Carbon\Carbon::parse($appt->scheduled_date)->format('M d, Y') }}</div>
                                <div style="font-size:11px;color:#888;margin-top:1px;">{{ \Carbon\Carbon::parse($appt->scheduled_time)->format('h:i A') }}</div>
                            </td>
                            <td>
                                <div style="font-weight:700;">{{ $appt->customer_name }}</div>
                                <div style="font-size:11px;color:#888;">ID: {{ $appt->customer_id }}</div>
                            </td>
                            <td>{{ $appt->pet_name }}</td>
                            <td>{{ $appt->vet_name }}</td>
                            <td>
                                @if(!empty($appt->type))
                                    <span class="type-pill type-{{ $appt->type }}">{{ $appt->type }}</span>
                                @else
                                    <span style="color:#ccc;">—</span>
                                @endif
                            </td>
                            <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;">
                                {{ $appt->reason_for_visit ?? '—' }}
                            </td>
                            <td>
                                <span class="badge {{ $badgeClass }}" id="badge-{{ $appt->id }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                <div class="action-cell">
                                    {{-- View --}}
                                    <button class="btn-view" onclick="openDetail({{ $appt->id }})">View</button>

                                    {{-- Edit — hidden for locked/completed appointments --}}
                                    @if(strtolower($appt->status) !== 'completed')
                                        <button class="btn-edit"
                                                onclick="openEditModal(
                                                    {{ $appt->id }},
                                                    {{ $appt->customer_id }},
                                                    '{{ addslashes($appt->pet_name !== '—' ? $appt->pet_name : '') }}',
                                                    '{{ addslashes($appt->pet_species !== '—' ? $appt->pet_species : '') }}',
                                                    '{{ addslashes($appt->pet_breed   !== '—' ? $appt->pet_breed   : '') }}',
                                                    '{{ addslashes($appt->pet_gender  !== '—' ? $appt->pet_gender  : '') }}',
                                                    '{{ addslashes($appt->pet_color   !== '—' ? $appt->pet_color   : '') }}',
                                                    '{{ $appt->pet_weight !== '—' ? $appt->pet_weight : '' }}',
                                                    {{ $appt->veterinarian_id }},
                                                    '{{ \Carbon\Carbon::parse($appt->scheduled_date)->format('Y-m-d') }}',
                                                    '{{ substr($appt->scheduled_time, 0, 5) }}',
                                                    '{{ addslashes($appt->type ?? '') }}',
                                                    '{{ addslashes($appt->reason_for_visit ?? '') }}'
                                                )">Edit</button>
                                    @endif

                                    {{-- Delete (admin only) --}}
                                    <button class="btn-delete" onclick="openDelete({{ $appt->id }})">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row" id="empty-row">
                            <td colspan="9">No appointments found{{ $selectedStatus ? ' with status "' . ($selectedStatus === 'no_show' ? 'No Show' : ($selectedStatus === 'canceled' ? 'Cancelled' : ucfirst($selectedStatus))) . '"' : '' }}{{ $selectedDate ? ' on ' . \Carbon\Carbon::parse($selectedDate)->format('M d, Y') : '' }}.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</main>

{{-- ══════════════════════════════
     VIEW / DETAIL MODAL
══════════════════════════════ --}}
<div class="modal-overlay" id="detailModal">
    <div class="modal-box modal-box-lg">
        <div class="modal-head">
            <h3>Appointment Details</h3>
            <button class="modal-close-btn" onclick="closeModal('detailModal')">&times;</button>
        </div>
        <div id="modal-content" style="overflow-y:auto;flex:1;">
            <div class="modal-loading">Loading…</div>
        </div>
        <div class="modal-foot">
            <button class="btn-close-modal" onclick="closeModal('detailModal')">Close</button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════
     ADD APPOINTMENT MODAL
══════════════════════════════ --}}
<div class="modal-overlay" id="addModal">
    <div class="modal-box modal-box-lg">
        <div class="modal-head">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Appointment
            </h3>
            <button class="modal-close-btn" onclick="closeModal('addModal')">&times;</button>
        </div>

        <form method="POST" action="{{ route('appointments.store') }}" id="addForm">
            @csrf
            <div class="modal-body">

                <div class="form-section-label">Appointment Info</div>

                <div class="form-row">
                    <div>
                        <select name="customer_id" id="add_customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->first_name }} {{ $c->last_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <input type="date" name="scheduled_date" id="add_scheduled_date"
                               class="form-input" value="{{ old('scheduled_date') }}" required>
                        @error('scheduled_date')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <input type="time" name="scheduled_time" id="add_scheduled_time"
                               class="form-input" value="{{ old('scheduled_time', '09:00') }}" required>
                        @error('scheduled_time')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <select name="veterinarian_id" id="add_veterinarian_id" class="form-select" required>
                            <option value="">Assigned Staff</option>
                            @foreach($staffList as $staff)
                                <option value="{{ $staff->id }}" {{ old('veterinarian_id') == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }} ({{ ucfirst(str_replace('_', ' ', $staff->role)) }})
                                </option>
                            @endforeach
                        </select>
                        @error('veterinarian_id')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <select name="type" id="add_type" class="form-select">
                            <option value="">Appointment Type</option>
                            <option value="Checkup"     {{ old('type') === 'Checkup'     ? 'selected' : '' }}>Checkup</option>
                            <option value="Vaccination" {{ old('type') === 'Vaccination' ? 'selected' : '' }}>Vaccination</option>
                            <option value="Surgery"     {{ old('type') === 'Surgery'     ? 'selected' : '' }}>Surgery</option>
                            <option value="Grooming"    {{ old('type') === 'Grooming'    ? 'selected' : '' }}>Grooming</option>
                        </select>
                        @error('type')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <textarea name="reason_for_visit" id="add_notes" class="form-textarea"
                                  placeholder="Notes / Reason for Visit"
                                  style="min-height:42px;">{{ old('reason_for_visit') }}</textarea>
                        @error('reason_for_visit')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-section-label">Pet Info</div>

                <div class="form-row">
                    <div>
                        <input type="text" name="pet_name" id="add_pet_name"
                               class="form-input" placeholder="Pet Name"
                               value="{{ old('pet_name') }}" required>
                        @error('pet_name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <select name="species" id="add_species" class="form-select"
                                onchange="updateBreeds('add')" required>
                            <option value="">Species</option>
                            <option value="Dog"     {{ old('species') === 'Dog'     ? 'selected' : '' }}>Dog</option>
                            <option value="Cat"     {{ old('species') === 'Cat'     ? 'selected' : '' }}>Cat</option>
                            <option value="Bird"    {{ old('species') === 'Bird'    ? 'selected' : '' }}>Bird</option>
                            <option value="Rabbit"  {{ old('species') === 'Rabbit'  ? 'selected' : '' }}>Rabbit</option>
                            <option value="Hamster" {{ old('species') === 'Hamster' ? 'selected' : '' }}>Hamster</option>
                            <option value="Other"   {{ old('species') === 'Other'   ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('species')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <select name="breed" id="add_breed" class="form-select">
                            <option value="">Breed (select species first)</option>
                        </select>
                        @error('breed')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <select name="gender" id="add_gender" class="form-select">
                            <option value="">Gender</option>
                            <option value="Male"   {{ old('gender') === 'Male'   ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('gender')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <input type="text" name="color" id="add_color"
                               class="form-input" placeholder="Color (e.g. Brown, White)"
                               value="{{ old('color') }}">
                        @error('color')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <input type="number" name="weight" id="add_weight"
                               class="form-input" placeholder="Weight (kg)"
                               value="{{ old('weight') }}" min="0" max="999.99" step="0.01">
                        @error('weight')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

            </div>
            <div class="modal-foot">
                <button type="button" class="btn-close-modal" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn-save-form">Save Appointment</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════
     EDIT APPOINTMENT MODAL
══════════════════════════════ --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box modal-box-lg">
        <div class="modal-head">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                             m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Appointment
            </h3>
            <button class="modal-close-btn" onclick="closeModal('editModal')">&times;</button>
        </div>

        <form method="POST" id="editForm" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">

                <div class="form-section-label">Appointment Info</div>

                <div class="form-row">
                    <div>
                        <select name="customer_id" id="edit_customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->first_name }} {{ $c->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="date" name="scheduled_date" id="edit_scheduled_date" class="form-input" required>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <input type="time" name="scheduled_time" id="edit_scheduled_time" class="form-input" required>
                    </div>
                    <div>
                        <select name="veterinarian_id" id="edit_veterinarian_id" class="form-select" required>
                            <option value="">Assigned Staff</option>
                            @foreach($staffList as $staff)
                                <option value="{{ $staff->id }}">
                                    {{ $staff->name }} ({{ ucfirst(str_replace('_', ' ', $staff->role)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <select name="type" id="edit_type" class="form-select">
                            <option value="">Appointment Type</option>
                            <option value="Checkup">Checkup</option>
                            <option value="Vaccination">Vaccination</option>
                            <option value="Surgery">Surgery</option>
                            <option value="Grooming">Grooming</option>
                        </select>
                    </div>
                    <div>
                        <textarea name="reason_for_visit" id="edit_notes" class="form-textarea"
                                  placeholder="Notes / Reason for Visit" style="min-height:42px;"></textarea>
                    </div>
                </div>

                <div class="form-section-label">Pet Info</div>

                <div class="form-row">
                    <div>
                        <input type="text" name="pet_name" id="edit_pet_name"
                               class="form-input" placeholder="Pet Name" required>
                    </div>
                    <div>
                        <select name="species" id="edit_species" class="form-select"
                                onchange="updateBreeds('edit')" required>
                            <option value="">Species</option>
                            <option value="Dog">Dog</option>
                            <option value="Cat">Cat</option>
                            <option value="Bird">Bird</option>
                            <option value="Rabbit">Rabbit</option>
                            <option value="Hamster">Hamster</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <select name="breed" id="edit_breed" class="form-select">
                            <option value="">Breed (select species first)</option>
                        </select>
                    </div>
                    <div>
                        <select name="gender" id="edit_gender" class="form-select">
                            <option value="">Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <input type="text" name="color" id="edit_color"
                               class="form-input" placeholder="Color (e.g. Brown, White)">
                    </div>
                    <div>
                        <input type="number" name="weight" id="edit_weight"
                               class="form-input" placeholder="Weight (kg)"
                               min="0" max="999.99" step="0.01">
                    </div>
                </div>

            </div>
            <div class="modal-foot">
                <button type="button" class="btn-close-modal" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-save-form">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════
     DELETE MODAL
══════════════════════════════ --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box modal-box-sm">
        <div class="modal-head">
            <h3>Confirm Delete</h3>
            <button class="modal-close-btn" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="delete-modal-body">
            Are you sure you want to delete this appointment?<br>This action cannot be undone.
        </div>
        <div class="modal-foot" style="gap:10px;">
            <button class="btn-cancel" onclick="closeModal('deleteModal')">Cancel</button>
            <form id="delete-form" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-delete-confirm">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
/* ════════════════════════════════════════════════════════════
   BREED DATA
════════════════════════════════════════════════════════════ */
const breedOptions = {
    Dog:     ['Aspin (Mixed Breed)','Labrador Retriever','German Shepherd','Golden Retriever','Bulldog','Poodle','Beagle','Rottweiler','Shih Tzu','Dachshund','Siberian Husky','Doberman','Chihuahua','Pomeranian','Chow Chow','Dalmatian','Border Collie','Great Dane','Maltese','Other'],
    Cat:     ['Puspin (Mixed Breed)','Persian','Siamese','Maine Coon','Ragdoll','Bengal','Sphynx','British Shorthair','Abyssinian','Scottish Fold','Russian Blue','Birman','Devon Rex','Oriental Shorthair','Other'],
    Bird:    ['Budgerigar','Cockatiel','African Grey','Lovebird','Canary','Other'],
    Rabbit:  ['Holland Lop','Mini Rex','Netherland Dwarf','Flemish Giant','Other'],
    Hamster: ['Syrian','Dwarf Campbell','Roborovski','Chinese','Other'],
    Other:   ['Other'],
};

function updateBreeds(prefix, selectedBreed = '') {
    const species = document.getElementById(prefix + '_species').value;
    const sel     = document.getElementById(prefix + '_breed');
    sel.innerHTML = '<option value="">Breed (select species first)</option>';
    if (!species || !breedOptions[species]) return;
    breedOptions[species].forEach(b => {
        const o = document.createElement('option');
        o.value = o.textContent = b;
        if (selectedBreed === b) o.selected = true;
        sel.appendChild(o);
    });
}

/* ════════════════════════════════════════════════════════════
   MODAL HELPERS
════════════════════════════════════════════════════════════ */
const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
let activeId = null;

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', function(e) { if (e.target === this) closeModal(this.id); });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(el => closeModal(el.id));
    }
});

/* ════════════════════════════════════════════════════════════
   EDIT MODAL  –  populate fields and open
════════════════════════════════════════════════════════════ */
function openEditModal(id, customerId, petName, species, breed, gender, color, weight,
                       staffId, date, time, type, notes) {
    document.getElementById('editForm').action = `{{ url('appointments') }}/${id}`;
    document.getElementById('edit_customer_id').value    = customerId;
    document.getElementById('edit_scheduled_date').value = date;
    document.getElementById('edit_scheduled_time').value = time;
    document.getElementById('edit_veterinarian_id').value = staffId;
    document.getElementById('edit_type').value           = type;
    document.getElementById('edit_notes').value          = notes;
    document.getElementById('edit_pet_name').value = petName;
    document.getElementById('edit_species').value  = species;
    document.getElementById('edit_gender').value   = gender;
    document.getElementById('edit_color').value    = color;
    document.getElementById('edit_weight').value   = weight;
    updateBreeds('edit', breed);
    openModal('editModal');
}

/* ════════════════════════════════════════════════════════════
   DELETE MODAL
════════════════════════════════════════════════════════════ */
function openDelete(id) {
    document.getElementById('delete-form').action = `{{ url('appointments') }}/${id}`;
    openModal('deleteModal');
}

/* ════════════════════════════════════════════════════════════
   STATUS CONFIG
════════════════════════════════════════════════════════════ */
const statusConfig = {
    scheduled : { label: 'Scheduled', bg: '#dbeeff', color: '#004085', border: '#90c8ff', dot: '#2196f3' },
    confirmed : { label: 'Confirmed', bg: '#ede9fe', color: '#3b0764', border: '#c4b5fd', dot: '#7c3aed' },
    completed : { label: 'Completed', bg: '#e8f5e9', color: '#155724', border: '#81c784', dot: '#27ae60' },
    no_show   : { label: 'No Show',   bg: '#fffbeb', color: '#78350f', border: '#fcd34d', dot: '#f59e0b' },
    canceled  : { label: 'Cancelled', bg: '#fde8e8', color: '#842029', border: '#f5a5a5', dot: '#e53935' },
    cancelled : { label: 'Cancelled', bg: '#fde8e8', color: '#842029', border: '#f5a5a5', dot: '#e53935' },
};

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function normStatus(s) {
    if (!s) return s;
    const lower = s.toLowerCase();
    return lower === 'cancelled' ? 'canceled' : lower;
}

/* ════════════════════════════════════════════════════════════
   TIMEZONE-AWARE TIMESTAMP FORMATTER
   ────────────────────────────────────────────────────────────
   Laravel stores timestamps in UTC (e.g. "2024-01-01 13:37:00").
   Without a timezone indicator, new Date() behaviour is
   browser-inconsistent.  We normalise to ISO-8601 UTC by
   replacing the space separator with 'T' and appending 'Z',
   then let the browser convert to the viewer's local timezone.
════════════════════════════════════════════════════════════ */
function parseUtcTimestamp(str) {
    if (!str) return null;
    // Already has timezone info — parse as-is
    if (/[Z+\-]\d*$/.test(str.trim())) {
        const d = new Date(str);
        return isNaN(d) ? null : d;
    }
    // MySQL/Laravel format: "2024-01-15 13:37:00" → treat as UTC
    const normalised = str.trim().replace(' ', 'T') + 'Z';
    const d = new Date(normalised);
    return isNaN(d) ? null : d;
}

function formatTimestampLocal(str) {
    const d = parseUtcTimestamp(str);
    if (!d) return str || '—';
    const date = d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    const time = d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    return date + ' · ' + time;
}

/* ════════════════════════════════════════════════════════════
   BUILD DETAIL MODAL CONTENT
════════════════════════════════════════════════════════════ */
function buildModalContent(data) {
    const currentNorm = normStatus(data.status);
    const isLocked    = ['completed', 'canceled'].includes(currentNorm);
    const isConfirmed = currentNorm === 'confirmed';
    const isNoShow    = currentNorm === 'no_show';
    const history     = data.status_history || data.status_histories || [];

    const details = [
        ['Appointment ID',   '#' + data.id],
        ['Date & Time',      (data.scheduled_date || '—') + (data.scheduled_time ? '  ' + data.scheduled_time : '')],
        ['Customer',         (data.customer_name || '—') + '  (ID: ' + (data.customer_id || '—') + ')'],
        ['Pet',              (data.pet_name || '—') + (data.pet_id && data.pet_id !== '—' ? '  (ID: ' + data.pet_id + ')' : '')],
        ['Species / Breed',  [data.species, data.breed].filter(v => v && v !== '—').join(' / ') || '—'],
        ['Gender / Color',   [data.gender, data.color].filter(v => v && v !== '—').join(' / ') || '—'],
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

    let lockNoticeHTML = '';
    if (isLocked) {
        lockNoticeHTML = `<div class="lock-notice">🔒 This appointment is <strong>${currentNorm === 'completed' ? 'completed' : 'cancelled'}</strong> and cannot be modified.</div>`;
    } else if (isConfirmed) {
        lockNoticeHTML = `<div class="lock-notice-warn">⚠ This appointment is <strong>confirmed</strong>. It can no longer be cancelled.</div>`;
    } else if (isNoShow) {
        lockNoticeHTML = `<div class="lock-notice-warn">⚠ This appointment is marked <strong>No Show</strong>. It can only be moved to <strong>Cancelled</strong>.</div>`;
    }

    /* ── Timeline — timestamps shown in viewer's local timezone ── */
    let timelineHTML = '';
    if (history.length > 0) {
        timelineHTML = history.map(item => {
            const sc       = statusConfig[normStatus(item.status)] || { label: item.status, dot: '#aaa', bg: '#f5f5f5', color: '#555', border: '#ccc' };
            const lbl      = item.status_label || sc.label;
            // ↓ Use the timezone-aware formatter so times show in local time (e.g. PHT)
            const formatted = formatTimestampLocal(item.changed_at);
            const roleLabel = item.changed_by ?? 'System';
            const tlClass   = normStatus(item.status);
            return `<div class="timeline-item tl-${tlClass}">
                <div class="timeline-dot-wrap">
                    <div class="timeline-dot"><div class="dot-inner"></div></div>
                </div>
                <div class="timeline-content">
                    <div class="timeline-status-label"
                         style="background:${sc.bg};color:${sc.color};border-color:${sc.border};">
                        ${escHtml(lbl)}
                    </div>
                    <div class="timeline-timestamp">
                        <span>${escHtml(formatted)}</span>
                        &nbsp;·&nbsp; by <span class="timeline-role-badge">${escHtml(roleLabel)}</span>
                    </div>
                </div>
            </div>`;
        }).join('');
    } else {
        timelineHTML = `<div class="timeline-empty">No status history recorded.</div>`;
    }

    /* ── Status update dropdown ── */
    const usedStatuses = new Set(history.map(h => normStatus(h.status)));
    const allStatuses = [
        { val: 'scheduled', label: 'Scheduled' },
        { val: 'confirmed', label: 'Confirmed' },
        { val: 'no_show',   label: 'No Show' },
        { val: 'canceled',  label: 'Cancelled' },
        { val: 'completed', label: 'Completed → Reports' },
    ];

    let optionsHTML = `<option value="" disabled selected>— Select new status —</option>`;
    allStatuses.forEach(s => {
        const isUsed             = usedStatuses.has(s.val);
        const isCurrent          = s.val === currentNorm;
        const isBlockedConfirmed = s.val === 'canceled' && isConfirmed;
        const isBlockedNoShow    = isNoShow && s.val !== 'canceled';
        const isBlocked          = isBlockedConfirmed || isBlockedNoShow;
        const disabled           = isUsed || isCurrent || isBlocked ? 'disabled' : '';
        const prefix             = isCurrent ? '● ' : (isUsed || isBlocked ? '✗ ' : '');
        optionsHTML += `<option value="${s.val}" ${disabled}>${prefix}${escHtml(s.label)}</option>`;
    });

    const statusUpdateHTML = isLocked ? '' :
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

    return '<div class="modal-body">' + detailHTML + lockNoticeHTML + '</div>' +
        '<div class="timeline-section"><h4>Status History</h4><div class="timeline">' + timelineHTML + '</div></div>' +
        statusUpdateHTML;
}

function onStatusDropdownChange(sel) {
    const saveBtn = document.getElementById('btn-save-status');
    if (saveBtn) saveBtn.disabled = !sel.value;
}

/* ════════════════════════════════════════════════════════════
   OPEN DETAIL MODAL  (AJAX)
════════════════════════════════════════════════════════════ */
function openDetail(id) {
    activeId = id;
    document.getElementById('modal-content').innerHTML = '<div class="modal-loading">Loading…</div>';
    openModal('detailModal');

    fetch(`/appointments/${id}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            document.getElementById('modal-content').innerHTML = buildModalContent(data);
        })
        .catch(() => {
            document.getElementById('modal-content').innerHTML =
                '<div class="modal-loading" style="color:#e53935;">Could not load appointment details.</div>';
        });
}

/* ════════════════════════════════════════════════════════════
   SAVE STATUS  (AJAX PATCH)
════════════════════════════════════════════════════════════ */
function saveStatus() {
    const sel     = document.getElementById('modal-status-select');
    const saveBtn = document.getElementById('btn-save-status');
    const saving  = document.getElementById('saving-indicator');
    const newStatus = sel?.value;
    if (!newStatus || !saveBtn) return;

    saveBtn.disabled     = true;
    saving.style.display = 'inline';

    fetch(`/appointments/${activeId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type'    : 'application/json',
            'X-CSRF-TOKEN'    : CSRF,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept'          : 'application/json',
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(r => r.json())
    .then(data => {
        saving.style.display = 'none';
        if (data.error) { alert(data.error); saveBtn.disabled = false; return; }

        if (data.success) {
            closeModal('detailModal');
            const row = document.querySelector(`tr[data-appt-id="${activeId}"]`);

            if (data.remove_from_list) {
                if (row) {
                    row.classList.add('row-removing');
                    row.addEventListener('animationend', () => {
                        row.remove();
                        updateRecordCount();
                        showEmptyRowIfNeeded();
                    }, { once: true });
                }
            } else {
                if (row) {
                    const badge  = row.querySelector(`#badge-${activeId}`);
                    const sc     = statusConfig[normStatus(data.status)] || {};
                    const bClass = {
                        scheduled : 'badge-scheduled',
                        confirmed : 'badge-confirmed',
                        no_show   : 'badge-no-show',
                        canceled  : 'badge-cancelled',
                        cancelled : 'badge-cancelled',
                    }[normStatus(data.status)] || 'badge-scheduled';

                    if (badge) {
                        badge.className   = 'badge ' + bClass;
                        badge.textContent = sc.label || data.label;
                    }
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

/* ════════════════════════════════════════════════════════════
   RECORD COUNT HELPERS
════════════════════════════════════════════════════════════ */
function updateRecordCount() {
    const visibleRows = document.querySelectorAll('#appt-tbody tr[data-appt-id]').length;
    const tag = document.getElementById('record-count-tag');
    if (tag) tag.textContent = visibleRows + ' record' + (visibleRows !== 1 ? 's' : '');
}

function showEmptyRowIfNeeded() {
    const visibleRows = document.querySelectorAll('#appt-tbody tr[data-appt-id]').length;
    const existing    = document.getElementById('empty-row');
    if (visibleRows === 0 && !existing) {
        const tbody = document.getElementById('appt-tbody');
        const tr    = document.createElement('tr');
        tr.id        = 'empty-row';
        tr.className = 'empty-row';
        tr.innerHTML = '<td colspan="9">No appointments found.</td>';
        tbody.appendChild(tr);
    }
}

/* ════════════════════════════════════════════════════════════
   RE-OPEN ADD MODAL ON VALIDATION FAILURE
════════════════════════════════════════════════════════════ */
@if($errors->any() && old('_token'))
    document.addEventListener('DOMContentLoaded', () => {
        openModal('addModal');
        const s = '{{ old("species") }}', b = '{{ old("breed") }}';
        if (s) { document.getElementById('add_species').value = s; updateBreeds('add', b); }
    });
@endif
</script>

</body>
</html>