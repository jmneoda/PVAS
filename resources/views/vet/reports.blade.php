{{-- resources/views/vet/reports.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PVAS – Vet Reports</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Figtree', sans-serif;
            background: #d0d0d0;
            min-height: 100vh;
            display: flex;
        }

        /* ════════════════════════════
           SIDEBAR  (matches vet/dashboard.blade.php)
        ════════════════════════════ */
        .sidebar {
            width: 180px; min-width: 180px;
            background: #ffffff; border-right: 2px solid #808080;
            display: flex; flex-direction: column; align-items: center;
            padding: 24px 0 0; position: sticky; top: 0; height: 100vh;
        }
        .sidebar-logo {
            width: 140px; height: 110px; margin-bottom: 20px;
            display: flex; align-items: center; justify-content: center;
        }
        .sidebar-logo img { width: 100%; height: 100%; object-fit: contain; }

        .sidebar-nav { width: 100%; display: flex; flex-direction: column; flex: 1; }
        .sidebar-nav a {
            display: block; width: 100%; padding: 15px 0; text-align: center;
            font-size: 15px; font-weight: 700; color: #111; text-decoration: none;
            border-top: 1.5px solid #bbb; transition: background 0.15s;
        }
        .sidebar-nav a:hover  { background: #e0e0e0; }
        .sidebar-nav a.active { background: #d0d0d0; }
        .sidebar-nav a.logout {
            color: #cc0000;
            border-top: 1.5px solid #bbb;
            border-bottom: 1.5px solid #bbb;
            margin-top: auto;
        }
        .sidebar-spacer { flex: 1; }

        /* ════════════════════════════
           MAIN
        ════════════════════════════ */
        .main { flex: 1; display: flex; flex-direction: column; background: #c8c8c8; min-height: 100vh; }

        /* ── Page Header ── */
        .page-header {
            background: #c8c8c8; border-bottom: 2px solid #999;
            padding: 10px 20px; display: flex; align-items: center;
            justify-content: space-between; gap: 12px; min-height: 56px;
            flex-wrap: wrap;
        }
        .page-header h1 { font-size: 20px; font-weight: 800; color: #111; }

        /* ── Segmented Date Toggle ── */
        .date-toggle {
            display: flex; align-items: stretch;
            background: #d8d8d8; border: 1.5px solid #999;
            border-radius: 6px; overflow: visible;
        }
        .date-toggle > button {
            padding: 0 16px; border: none; background: transparent;
            font-size: 13px; font-weight: 700; color: #444;
            cursor: pointer; font-family: 'Figtree', sans-serif;
            transition: background 0.15s, color 0.15s;
            border-right: 1px solid #bbb; white-space: nowrap;
            height: 34px; display: inline-flex; align-items: center;
        }
        .date-toggle > button:first-child { border-radius: 4px 0 0 4px; }
        .date-toggle > button:hover:not(.active) { background: #ccc; color: #111; }
        .date-toggle > button.active { background: #27ae60; color: #fff; }

        .custom-wrap { position: relative; display: flex; align-items: stretch; }
        .custom-face {
            padding: 0 14px; border: none; background: transparent;
            font-size: 13px; font-weight: 700; color: #444;
            font-family: 'Figtree', sans-serif; white-space: nowrap;
            height: 34px; display: inline-flex; align-items: center; gap: 6px;
            border-radius: 0 4px 4px 0; pointer-events: none;
            transition: background 0.15s, color 0.15s;
        }
        .custom-face.active { background: #27ae60; color: #fff; }
        .custom-date-input {
            position: absolute; inset: 0; width: 100%; height: 100%;
            opacity: 0; cursor: pointer; z-index: 5;
            border: none; background: transparent; color: transparent; font-size: 0;
        }
        .custom-date-input::-webkit-calendar-picker-indicator {
            position: absolute; inset: 0; width: 100%; height: 100%;
            opacity: 0; cursor: pointer; margin: 0; padding: 0;
        }

        /* ════════════════════════════
           BODY
        ════════════════════════════ */
        .reports-body { padding: 16px 20px; display: flex; flex-direction: column; gap: 14px; }

        .date-banner {
            display: flex; align-items: center; gap: 8px;
            font-size: 12px; font-weight: 700; color: #555;
            background: #d4d4d4; border: 1px solid #bbb;
            border-radius: 4px; padding: 6px 14px;
        }
        .date-banner strong { color: #222; }

        .flash-success, .flash-error {
            display: flex; align-items: center; gap: 8px; padding: 8px 14px;
            border-radius: 4px; font-size: 13px; font-weight: 700;
        }
        .flash-success { background: #e8f5e9; border: 1px solid #81c784; color: #155724; }
        .flash-error   { background: #fde8e8; border: 1px solid #f5a5a5; color: #842029; }

        /* ── Summary strip ── */
        .summary-strip {
            background: #fff; border: 1.5px solid #ccc; border-radius: 6px;
            padding: 14px 20px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap;
        }
        .summary-strip .s-label { font-size: 12px; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: 0.05em; }
        .summary-strip .s-value { font-size: 26px; font-weight: 800; color: #27ae60; line-height: 1; }
        .summary-strip .s-divider { width: 1px; height: 32px; background: #ddd; }
        .summary-strip .s-spacer  { flex: 1; min-width: 20px; }

        .export-group { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .btn-export {
            display: inline-flex; align-items: center; gap: 7px;
            font-size: 13px; font-weight: 800; padding: 8px 16px;
            border: none; border-radius: 4px; cursor: pointer;
            text-decoration: none; transition: background 0.15s;
            font-family: 'Figtree', sans-serif; white-space: nowrap;
        }
        .btn-export-pdf { background: #e53935; color: #fff; }
        .btn-export-pdf:hover { background: #c62828; }
        .btn-export-csv { background: #1a7fe8; color: #fff; }
        .btn-export-csv:hover { background: #1465c0; }

        /* ── Table card ── */
        .table-card { background: #fff; border: 1.5px solid #ccc; border-radius: 6px; overflow: hidden; }
        .table-header {
            background: #f2f2f2; padding: 12px 16px;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1.5px solid #ddd;
        }
        .table-header h2 { font-size: 14px; font-weight: 800; color: #222; }
        .count-tag {
            font-size: 11px; font-weight: 700; color: #666;
            background: #e4e4e4; border: 1px solid #ccc;
            border-radius: 10px; padding: 2px 10px;
        }

        .table-wrap { overflow-x: auto; }

        table.report-table { width: 100%; border-collapse: collapse; }
        table.report-table thead th {
            padding: 10px 14px; text-align: left;
            font-size: 11px; font-weight: 800; color: #333;
            background: #f8f8f8; border-bottom: 1.5px solid #ddd;
            text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;
        }
        table.report-table thead th:last-child { text-align: center; }
        table.report-table tbody td {
            padding: 10px 14px; font-size: 13px; color: #333;
            border-bottom: 1px solid #eee; white-space: nowrap; vertical-align: middle;
        }
        table.report-table tbody tr:last-child td { border-bottom: none; }
        table.report-table tbody tr:hover td { background: #f5f5f5; }
        .empty-row td {
            text-align: center; color: #888; font-style: italic;
            padding: 36px 0; font-size: 13px;
        }

        .badge-completed {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 800;
            background: #e8f5e9; color: #155724; border: 1px solid #81c784;
        }
        .badge-completed::before {
            content: ''; width: 6px; height: 6px; border-radius: 50%;
            background: #27ae60; flex-shrink: 0;
        }

        /* ── Action buttons ── */
        .action-cell { display: flex; align-items: center; justify-content: center; gap: 6px; }

        .btn-view, .btn-delete {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 12px; font-weight: 700; padding: 5px 12px;
            border-radius: 4px; cursor: pointer; border: 1.5px solid;
            font-family: 'Figtree', sans-serif; white-space: nowrap;
            transition: background 0.15s, border-color 0.15s; text-decoration: none;
        }
        .btn-view {
            background: #dbeeff; border-color: #90c8ff; color: #0d47a1;
        }
        .btn-view:hover { background: #bfddff; border-color: #42a5f5; }

        .btn-delete {
            background: #fde8e8; border-color: #f5a5a5; color: #842029;
        }
        .btn-delete:hover { background: #fcc; border-color: #e53935; }

        /* ── Pagination ── */
        .pagination-bar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 16px; border-top: 1.5px solid #eee;
            background: #fafafa; font-size: 12px; color: #666; font-weight: 600;
        }
        .pagination-controls { display: flex; align-items: center; gap: 4px; }
        .page-btn {
            width: 28px; height: 28px; border-radius: 4px; border: 1.5px solid #ddd;
            background: #fff; color: #555; font-size: 12px; font-weight: 700;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all 0.15s; font-family: 'Figtree', sans-serif;
        }
        .page-btn:hover:not(:disabled) { background: #e8f5e9; border-color: #81c784; color: #155724; }
        .page-btn.active { background: #27ae60; border-color: #27ae60; color: #fff; }
        .page-btn:disabled { opacity: 0.4; cursor: not-allowed; }

        /* ════════════════════════════
           MODALS
        ════════════════════════════ */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.45); z-index: 1000;
            align-items: center; justify-content: center;
        }
        .modal-overlay.open { display: flex; }

        .modal-box {
            background: #fff; border-radius: 8px;
            width: 100%; box-shadow: 0 8px 32px rgba(0,0,0,0.22);
            overflow: hidden; animation: modalIn 0.18s ease;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(-14px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .modal-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px; border-bottom: 1.5px solid #e5e5e5; background: #f8f8f8;
        }
        .modal-header h3 { font-size: 15px; font-weight: 800; color: #111; }
        .modal-close {
            background: none; border: none; font-size: 20px; line-height: 1;
            color: #777; cursor: pointer; padding: 2px 6px; border-radius: 4px;
            transition: color 0.15s, background 0.15s;
        }
        .modal-close:hover { color: #e53935; background: #fde8e8; }
        .modal-footer {
            display: flex; justify-content: flex-end; gap: 8px;
            padding: 12px 20px; border-top: 1.5px solid #eee; background: #fafafa;
        }
        .btn-close-footer {
            padding: 7px 20px; font-size: 13px; font-weight: 800;
            font-family: 'Figtree', sans-serif;
            border: 1.5px solid #ccc; background: #f0f0f0; color: #555;
            border-radius: 6px; cursor: pointer; transition: background 0.15s;
        }
        .btn-close-footer:hover { background: #e0e0e0; }

        /* ── View Modal ── */
        #view-modal .modal-box { max-width: 620px; }

        .modal-body {
            padding: 20px; display: flex; flex-direction: column; gap: 0;
            max-height: calc(100vh - 180px); overflow-y: auto;
        }

        .modal-section-title {
            font-size: 10px; font-weight: 800; text-transform: uppercase;
            letter-spacing: 0.08em; color: #aaa; margin: 14px 0 6px;
        }
        .modal-section-title:first-child { margin-top: 0; }

        .detail-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 0;
            border: 1px solid #eee; border-radius: 4px; overflow: hidden;
        }
        .detail-row { display: contents; }
        .detail-label {
            padding: 8px 12px; font-size: 12px; font-weight: 800; color: #555;
            background: #fafafa; border-bottom: 1px solid #eee; border-right: 1px solid #eee;
        }
        .detail-value {
            padding: 8px 12px; font-size: 13px; color: #222;
            background: #fff; border-bottom: 1px solid #eee;
        }
        .detail-row:last-child .detail-label,
        .detail-row:last-child .detail-value { border-bottom: none; }

        .detail-row-full { display: flex; border-bottom: 1px solid #eee; }
        .detail-row-full:last-child { border-bottom: none; }
        .detail-row-full .detail-label { width: 160px; min-width: 160px; border-bottom: none; }
        .detail-row-full .detail-value { flex: 1; border-bottom: none; border-right: none; }

        .spinner {
            display: inline-block; width: 13px; height: 13px;
            border: 2px solid #ccc; border-top-color: #2196f3;
            border-radius: 50%; animation: spin 0.6s linear infinite; vertical-align: middle;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Status History Timeline ── */
        .timeline-wrap {
            border: 1px solid #eee; border-radius: 4px; overflow: hidden;
            background: #fafafa;
        }
        .timeline-empty {
            padding: 18px 16px; font-size: 12px; color: #aaa;
            font-style: italic; text-align: center;
        }
        .timeline-list {
            list-style: none; padding: 12px 16px; display: flex;
            flex-direction: column; gap: 0;
        }
        .timeline-item {
            display: flex; align-items: flex-start; gap: 12px;
            position: relative; padding-bottom: 14px;
        }
        .timeline-item:last-child { padding-bottom: 0; }
        .timeline-item:not(:last-child)::after {
            content: ''; position: absolute;
            left: 9px; top: 20px;
            width: 2px; bottom: 0;
            background: #e0e0e0;
        }

        .tl-dot {
            width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid; position: relative; z-index: 1; margin-top: 1px;
        }
        .tl-dot svg { width: 9px; height: 9px; }

        .tl-dot.scheduled  { background: #e3f2fd; border-color: #90caf9; color: #1565c0; }
        .tl-dot.confirmed  { background: #ede7f6; border-color: #b39ddb; color: #4527a0; }
        .tl-dot.completed  { background: #e8f5e9; border-color: #81c784; color: #1b5e20; }
        .tl-dot.no_show    { background: #fff3e0; border-color: #ffb74d; color: #e65100; }
        .tl-dot.canceled,
        .tl-dot.cancelled  { background: #fde8e8; border-color: #ef9a9a; color: #b71c1c; }

        .tl-body { flex: 1; min-width: 0; }
        .tl-status { font-size: 12px; font-weight: 800; color: #222; line-height: 1.2; }
        .tl-meta   { display: flex; align-items: center; gap: 6px; margin-top: 3px; flex-wrap: wrap; }
        .tl-date   { font-size: 11px; color: #666; font-weight: 600; }
        .tl-sep    { font-size: 10px; color: #ccc; }
        .tl-role   {
            font-size: 10px; font-weight: 800; padding: 1px 7px;
            border-radius: 8px; letter-spacing: 0.04em;
        }
        .tl-role.role-admin        { background: #e3f2fd; color: #1565c0; border: 1px solid #90caf9; }
        .tl-role.role-receptionist { background: #ede7f6; color: #4527a0; border: 1px solid #b39ddb; }
        .tl-role.role-veterinarian { background: #e8f5e9; color: #1b5e20; border: 1px solid #81c784; }
        .tl-role.role-staff        { background: #fff3e0; color: #e65100; border: 1px solid #ffb74d; }
        .tl-role.role-system       { background: #f5f5f5; color: #757575; border: 1px solid #e0e0e0; }
        .tl-role.role-default      { background: #f5f5f5; color: #555;    border: 1px solid #ddd;    }

        /* ── Delete Modal ── */
        #delete-modal .modal-box { max-width: 420px; }

        .delete-body {
            padding: 24px 20px; display: flex; flex-direction: column;
            align-items: center; gap: 12px; text-align: center;
        }
        .delete-icon {
            width: 52px; height: 52px; border-radius: 50%;
            background: #fde8e8; border: 2px solid #f5a5a5;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .delete-icon svg { color: #e53935; }
        .delete-title { font-size: 16px; font-weight: 800; color: #111; }
        .delete-sub   { font-size: 13px; color: #666; line-height: 1.5; max-width: 300px; }
        .delete-appt-id {
            font-size: 13px; font-weight: 800; color: #e53935;
            background: #fde8e8; border: 1px solid #f5a5a5;
            border-radius: 4px; padding: 4px 14px;
        }
        .btn-confirm-delete {
            padding: 8px 24px; font-size: 13px; font-weight: 800;
            font-family: 'Figtree', sans-serif;
            border: 1.5px solid #e53935; background: #e53935; color: #fff;
            border-radius: 6px; cursor: pointer; transition: background 0.15s;
        }
        .btn-confirm-delete:hover { background: #c62828; border-color: #c62828; }

        @media (max-width: 1100px) {
            .date-toggle > button { padding: 0 12px; }
        }
        @media (max-width: 700px) {
            .sidebar { width: 130px; min-width: 130px; }
            .date-toggle > button { padding: 0 10px; font-size: 12px; }
            .reports-body { padding: 12px; }
            #view-modal .modal-box   { max-width: 96vw; margin: 0 8px; }
            #delete-modal .modal-box { max-width: 96vw; margin: 0 8px; }
            .detail-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

{{-- ══════════════════════════════
     SIDEBAR  (vet)
══════════════════════════════ --}}
<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="{{ asset('images/PVAS.png') }}" alt="PVAS Logo">
    </div>
    <div class="sidebar-nav">
        <a href="{{ route('vet.dashboard') }}"
           class="{{ request()->routeIs('vet.dashboard') ? 'active' : '' }}">Dashboard</a>

        <a href="{{ route('vet.appointments.index') }}"
           class="{{ request()->routeIs('vet.appointments.*') ? 'active' : '' }}">Appointments</a>

        <a href="{{ route('vet.reports.index') }}"
           class="{{ request()->routeIs('vet.reports.*') ? 'active' : '' }}">Reports</a>

        <div class="sidebar-spacer"></div>

        <a href="{{ route('logout') }}" class="logout"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
    </div>
    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>
</aside>

{{-- ══════════════════════════════
     MAIN
══════════════════════════════ --}}
<main class="main">

    {{-- Hidden filter form — uses VET route --}}
    <form method="GET" action="{{ route('vet.reports.index') }}" id="filter-form" style="display:none;">
        <input type="hidden" name="date_filter" id="hid-filter" value="{{ $dateFilter }}">
        <input type="hidden" name="custom_date" id="hid-custom"  value="{{ $customDate ?? '' }}">
    </form>

    {{-- ── Page Header ── --}}
    <div class="page-header">
        <h1>Reports</h1>

        <div class="date-toggle">
            <button type="button" class="{{ $dateFilter === 'this_week'  ? 'active' : '' }}"
                    onclick="applyFilter('this_week')">This Week</button>
            <button type="button" class="{{ $dateFilter === 'this_month' ? 'active' : '' }}"
                    onclick="applyFilter('this_month')">This Month</button>
            <button type="button" class="{{ $dateFilter === 'this_year'  ? 'active' : '' }}"
                    onclick="applyFilter('this_year')">This Year</button>

            <div class="custom-wrap">
                <span class="custom-face {{ $dateFilter === 'custom' ? 'active' : '' }}" id="custom-face">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8"  y1="2" x2="8"  y2="6"/>
                        <line x1="3"  y1="10" x2="21" y2="10"/>
                    </svg>
                    <span id="custom-label">
                        @if($dateFilter === 'custom' && !empty($customDate))
                            {{ \Carbon\Carbon::parse($customDate)->format('M d, Y') }}
                        @else
                            Custom
                        @endif
                    </span>
                </span>
                <input class="custom-date-input" type="date" id="custom-date-input"
                       value="{{ $customDate ?? now()->toDateString() }}"
                       max="{{ now()->toDateString() }}">
            </div>
        </div>
    </div>

    <div class="reports-body">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="flash-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="flash-error">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Date context banner --}}
        <div class="date-banner">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8"  y1="2" x2="8"  y2="6"/>
                <line x1="3"  y1="10" x2="21" y2="10"/>
            </svg>
            Showing completed appointments for: <strong>{{ $dateLabel }}</strong>
        </div>

        {{-- Summary strip --}}
        <div class="summary-strip">
            <div>
                <div class="s-label">Completed Records</div>
                <div class="s-value">{{ count($records) }}</div>
            </div>
            <div class="s-divider"></div>
            <div>
                <div class="s-label">Period</div>
                <div style="font-size:13px; font-weight:700; color:#444; margin-top:2px;">{{ $dateLabel }}</div>
            </div>
            <div class="s-spacer"></div>

            <div class="export-group">
                <a href="{{ route('vet.reports.pdf', array_filter(['date_filter' => $dateFilter, 'custom_date' => $customDate ?? ''])) }}"
                   class="btn-export btn-export-pdf">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 16l-5-5 1.41-1.41L11 13.17V4h2v9.17l2.59-2.58L17 11l-5 5z"/>
                        <line x1="5" y1="19" x2="19" y2="19" stroke-width="2.5"/>
                    </svg>
                    Export PDF
                </a>
                <a href="{{ route('vet.reports.csv', array_filter(['date_filter' => $dateFilter, 'custom_date' => $customDate ?? ''])) }}"
                   class="btn-export btn-export-csv">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 17H7A5 5 0 017 7h2M15 7h2a5 5 0 010 10h-2M8 12h8"/>
                    </svg>
                    Export CSV
                </a>
            </div>
        </div>

        {{-- ── Table ── --}}
        <div class="table-card">
            <div class="table-header">
                <div style="display:flex; align-items:center; gap:8px;">
                    <h2>Completed Appointment Records</h2>
                    <span class="count-tag">
                        {{ count($records) }} record{{ count($records) !== 1 ? 's' : '' }}
                    </span>
                </div>
            </div>

            <div class="table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Appt. ID</th>
                            <th>Date &amp; Time</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Pet</th>
                            <th>Species / Breed</th>
                            <th>Veterinarian</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="report-tbody">
                        @php
                            use App\Models\Appointment;
                            $completedRecords = $records->filter(
                                fn($r) => $r->status === Appointment::STATUS_COMPLETED
                            );
                        @endphp

                        @forelse($completedRecords as $record)
                            <tr class="report-row" data-id="{{ $record->appointment_id }}">
                                <td style="font-weight:700; color:#888;">{{ $loop->iteration }}</td>
                                <td style="font-weight:700; color:#555;">#{{ $record->appointment_id }}</td>
                                <td>
                                    <div style="font-weight:700;">
                                        {{ \Carbon\Carbon::parse($record->appointment_date)->format('M d, Y') }}
                                    </div>
                                    <div style="font-size:11px; color:#888; margin-top:1px;">
                                        {{ \Carbon\Carbon::parse($record->appointment_time)->format('h:i A') }}
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight:700;">{{ $record->customer_name }}</div>
                                    <div style="font-size:11px; color:#888;">ID: {{ $record->customer_id }}</div>
                                </td>
                                <td>{{ $record->contact_number ?? '—' }}</td>
                                <td style="font-weight:700;">{{ $record->pet_name }}</td>
                                <td>
                                    <div>{{ $record->pet_species }}</div>
                                    <div style="font-size:11px; color:#888;">{{ $record->pet_breed }}</div>
                                </td>
                                <td>{{ $record->vet_name }}</td>
                                <td style="max-width:160px; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $record->reason_for_visit ?? '—' }}
                                </td>
                                <td><span class="badge-completed">Completed</span></td>
                                <td>
                                    <div class="action-cell">
                                        <button type="button"
                                                class="btn-view"
                                                onclick="openViewModal({{ $record->appointment_id }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            View
                                        </button>

                                        <button type="button"
                                                class="btn-delete"
                                                onclick="openDeleteModal({{ $record->appointment_id }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                                <path d="M10 11v6M14 11v6"/>
                                                <path d="M9 6V4h6v2"/>
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="11">No completed appointments found for <strong>{{ $dateLabel }}</strong>.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-bar" id="pagination-bar">
                <span id="pagination-info"></span>
                <div class="pagination-controls" id="pagination-controls"></div>
            </div>
        </div>

    </div>
</main>

{{-- ══════════════════════════════
     VIEW DETAIL MODAL
══════════════════════════════ --}}
<div class="modal-overlay" id="view-modal" role="dialog" aria-modal="true" aria-labelledby="view-modal-title">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="view-modal-title">Appointment Details</h3>
            <button class="modal-close" onclick="closeViewModal()" title="Close">&times;</button>
        </div>

        <div class="modal-body">
            <div id="view-loading" style="text-align:center; padding:32px 0; color:#888; font-size:13px; font-weight:700;">
                <span class="spinner"></span>&nbsp; Loading…
            </div>

            <div id="view-content" style="display:none;">

                {{-- Appointment --}}
                <div class="modal-section-title">Appointment</div>
                <div class="detail-grid">
                    <div class="detail-row">
                        <span class="detail-label">Appointment ID</span>
                        <span class="detail-value" id="v-id"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date</span>
                        <span class="detail-value" id="v-date"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Time</span>
                        <span class="detail-value" id="v-time"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Veterinarian</span>
                        <span class="detail-value" id="v-vet"></span>
                    </div>
                </div>

                <div style="margin-top:6px; border:1px solid #eee; border-radius:4px; overflow:hidden;">
                    <div class="detail-row-full">
                        <span class="detail-label">Reason for Visit</span>
                        <span class="detail-value" id="v-reason"></span>
                    </div>
                </div>

                {{-- Customer --}}
                <div class="modal-section-title">Customer</div>
                <div class="detail-grid">
                    <div class="detail-row">
                        <span class="detail-label">Name</span>
                        <span class="detail-value" id="v-customer"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Customer ID</span>
                        <span class="detail-value" id="v-customer-id"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Contact</span>
                        <span class="detail-value" id="v-contact"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address</span>
                        <span class="detail-value" id="v-address"></span>
                    </div>
                </div>

                {{-- Pet --}}
                <div class="modal-section-title">Pet</div>
                <div class="detail-grid">
                    <div class="detail-row">
                        <span class="detail-label">Name</span>
                        <span class="detail-value" id="v-pet"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Species</span>
                        <span class="detail-value" id="v-species"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Breed</span>
                        <span class="detail-value" id="v-breed"></span>
                    </div>
                </div>

                {{-- Status History Timeline --}}
                <div class="modal-section-title">Status History</div>
                <div class="timeline-wrap">
                    <div id="v-history-empty" class="timeline-empty" style="display:none;">
                        No status history recorded for this appointment.
                    </div>
                    <ul class="timeline-list" id="v-history-list"></ul>
                </div>

            </div>{{-- /view-content --}}
        </div>

        <div class="modal-footer">
            <button class="btn-close-footer" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════
     DELETE CONFIRM MODAL
══════════════════════════════ --}}
<div class="modal-overlay" id="delete-modal" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="delete-modal-title">Delete Record</h3>
            <button class="modal-close" onclick="closeDeleteModal()" title="Close">&times;</button>
        </div>

        <div class="delete-body">
            <div class="delete-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6"/>
                    <path d="M9 6V4h6v2"/>
                </svg>
            </div>
            <div class="delete-title">Permanently Delete Record?</div>
            <div class="delete-sub">
                This action cannot be undone. The completed appointment record will be removed permanently.
            </div>
            <div class="delete-appt-id" id="delete-appt-label">#—</div>
        </div>

        {{-- DELETE form — points to vet.reports.destroy --}}
        <form id="delete-form" method="POST" style="display:none;">
            @csrf
            @method('DELETE')
            <input type="hidden" name="date_filter" value="{{ $dateFilter }}">
            <input type="hidden" name="custom_date" value="{{ $customDate ?? '' }}">
        </form>

        <div class="modal-footer">
            <button class="btn-close-footer" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn-confirm-delete" id="confirm-delete-btn" onclick="submitDelete()">
                Yes, Delete
            </button>
        </div>
    </div>
</div>

<script>
    /* ══════════════════════════
       FILTER
    ══════════════════════════ */
    function applyFilter(filter) {
        document.getElementById('hid-filter').value = filter;
        document.getElementById('hid-custom').value  = '';
        document.getElementById('filter-form').submit();
    }

    document.getElementById('custom-date-input').addEventListener('change', function () {
        var picked = this.value;
        if (!picked) return;
        document.getElementById('hid-filter').value = 'custom';
        document.getElementById('hid-custom').value  = picked;
        document.getElementById('filter-form').submit();
    });

    /* ══════════════════════════
       STATUS CONFIG
    ══════════════════════════ */
    var STATUS_LABELS = {
        scheduled: 'Scheduled',
        confirmed: 'Confirmed',
        completed: 'Completed',
        no_show:   'No Show',
        canceled:  'Cancelled',
        cancelled: 'Cancelled'
    };

    var STATUS_ICONS = {
        scheduled: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
        confirmed: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>',
        completed: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
        no_show:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        canceled:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        cancelled: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>'
    };

    var ROLE_CLASS = {
        'Admin':         'admin',
        'Receptionist':  'receptionist',
        'Veterinarian':  'veterinarian',
        'Vet Nurse':     'veterinarian',
        'Vet Assistant': 'veterinarian',
        'Groomer':       'staff',
        'Staff':         'staff',
        'System':        'system'
    };

    /* ══════════════════════════
       VIEW MODAL
    ══════════════════════════ */
    function openViewModal(id) {
        document.getElementById('view-loading').style.display = 'block';
        document.getElementById('view-content').style.display = 'none';
        document.getElementById('view-modal').classList.add('open');
        document.body.style.overflow = 'hidden';

        fetch('/vet/reports/' + id, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) {
            if (!res.ok) throw new Error('Server error ' + res.status);
            return res.json();
        })
        .then(function (d) {
            document.getElementById('v-id').textContent          = '#' + d.appointment_id;
            document.getElementById('v-date').textContent        = formatDate(d.appointment_date);
            document.getElementById('v-time').textContent        = formatTime(d.appointment_time);
            document.getElementById('v-vet').textContent         = d.vet_name         || '—';
            document.getElementById('v-reason').textContent      = d.reason_for_visit  || '—';
            document.getElementById('v-customer').textContent    = d.customer_name    || '—';
            document.getElementById('v-customer-id').textContent = d.customer_id ? 'ID: ' + d.customer_id : '—';
            document.getElementById('v-contact').textContent     = d.contact_number   || '—';
            document.getElementById('v-address').textContent     = d.address          || '—';
            document.getElementById('v-pet').textContent         = d.pet_name         || '—';
            document.getElementById('v-species').textContent     = d.pet_species      || '—';
            document.getElementById('v-breed').textContent       = d.pet_breed        || '—';

            renderTimeline(d.status_history || []);

            document.getElementById('view-loading').style.display = 'none';
            document.getElementById('view-content').style.display = 'block';
        })
        .catch(function (err) {
            document.getElementById('view-loading').innerHTML =
                '<span style="color:#e53935;">Failed to load record. Please try again.</span>';
            console.error(err);
        });
    }

    function renderTimeline(history) {
        var list  = document.getElementById('v-history-list');
        var empty = document.getElementById('v-history-empty');
        list.innerHTML = '';

        if (!history || history.length === 0) {
            empty.style.display = 'block';
            list.style.display  = 'none';
            return;
        }

        empty.style.display = 'none';
        list.style.display  = '';

        history.forEach(function (item) {
            var status   = (item.status || '').toLowerCase();
            var label    = STATUS_LABELS[status] || capitalise(status);
            var icon     = STATUS_ICONS[status]  || STATUS_ICONS['scheduled'];
            var roleText = item.role || 'System';
            var roleCls  = ROLE_CLASS[roleText]  || 'default';

            var li = document.createElement('li');
            li.className = 'timeline-item';
            li.innerHTML =
                '<div class="tl-dot ' + status + '">' + icon + '</div>' +
                '<div class="tl-body">' +
                    '<div class="tl-status">' + label + '</div>' +
                    '<div class="tl-meta">' +
                        '<span class="tl-date">'  + formatDateTime(item.changed_at) + '</span>' +
                        '<span class="tl-sep">·</span>' +
                        '<span class="tl-role role-' + roleCls + '">' + escHtml(roleText) + '</span>' +
                    '</div>' +
                '</div>';
            list.appendChild(li);
        });
    }

    function closeViewModal() {
        document.getElementById('view-modal').classList.remove('open');
        document.body.style.overflow = '';
    }

    /* ══════════════════════════
       DELETE MODAL
    ══════════════════════════ */
    var pendingDeleteId = null;

    function openDeleteModal(id) {
        pendingDeleteId = id;
        document.getElementById('delete-appt-label').textContent = 'Appointment #' + id;
        document.getElementById('delete-modal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        document.getElementById('delete-modal').classList.remove('open');
        document.body.style.overflow = '';
        pendingDeleteId = null;
    }

    function submitDelete() {
        if (!pendingDeleteId) return;
        var form = document.getElementById('delete-form');
        form.action = '/vet/reports/' + pendingDeleteId;
        form.submit();
    }

    /* ── Backdrop / Escape ── */
    document.getElementById('view-modal').addEventListener('click', function (e) {
        if (e.target === this) closeViewModal();
    });
    document.getElementById('delete-modal').addEventListener('click', function (e) {
        if (e.target === this) closeDeleteModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeViewModal(); closeDeleteModal(); }
    });

    /* ══════════════════════════
       HELPERS
    ══════════════════════════ */
    function formatDate(str) {
        if (!str) return '—';
        var d = new Date(str + 'T00:00:00');
        return d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    }
    function formatTime(str) {
        if (!str) return '—';
        var t = new Date('1970-01-01T' + str);
        if (isNaN(t)) return str;
        return t.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }
    function formatDateTime(str) {
        if (!str) return '—';
        var dt = new Date(str);
        if (isNaN(dt)) return str;
        var date = dt.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
        var time = dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        return date + ' at ' + time;
    }
    function capitalise(s) {
        return s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
    }
    function escHtml(s) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(s));
        return d.innerHTML;
    }

    /* ══════════════════════════
       PAGINATION
    ══════════════════════════ */
    var PER_PAGE    = 10;
    var currentPage = 1;
    var rows        = Array.from(document.querySelectorAll('#report-tbody .report-row'));
    var totalRows   = rows.length;
    var totalPages  = Math.ceil(totalRows / PER_PAGE);

    function renderPage(page) {
        currentPage = page;
        rows.forEach(function (r, i) {
            r.style.display = (i >= (page - 1) * PER_PAGE && i < page * PER_PAGE) ? '' : 'none';
        });
        renderControls();
    }

    function renderControls() {
        var info = document.getElementById('pagination-info');
        var ctrl = document.getElementById('pagination-controls');
        if (!info || !ctrl) return;
        if (totalRows === 0) { info.textContent = 'No records'; ctrl.innerHTML = ''; return; }

        var start = (currentPage - 1) * PER_PAGE + 1;
        var end   = Math.min(currentPage * PER_PAGE, totalRows);
        info.textContent = 'Showing ' + start + '–' + end + ' of ' + totalRows +
                           ' record' + (totalRows !== 1 ? 's' : '');

        ctrl.innerHTML = '';

        var prev = document.createElement('button');
        prev.className = 'page-btn'; prev.textContent = '‹';
        prev.disabled  = (currentPage === 1);
        prev.onclick   = function () { renderPage(currentPage - 1); };
        ctrl.appendChild(prev);

        buildPageRange(currentPage, totalPages).forEach(function (p) {
            if (p === '…') {
                var span = document.createElement('span');
                span.textContent = '…';
                span.style.cssText = 'padding:0 4px; color:#aaa; font-size:12px;';
                ctrl.appendChild(span);
            } else {
                var btn = document.createElement('button');
                btn.className   = 'page-btn' + (p === currentPage ? ' active' : '');
                btn.textContent = p;
                btn.onclick     = (function (pg) { return function () { renderPage(pg); }; })(p);
                ctrl.appendChild(btn);
            }
        });

        var next = document.createElement('button');
        next.className = 'page-btn'; next.textContent = '›';
        next.disabled  = (currentPage === totalPages || totalPages === 0);
        next.onclick   = function () { renderPage(currentPage + 1); };
        ctrl.appendChild(next);
    }

    function buildPageRange(current, total) {
        if (total <= 7) {
            var arr = [];
            for (var i = 1; i <= total; i++) arr.push(i);
            return arr;
        }
        var pages = [1];
        if (current > 3) pages.push('…');
        for (var p = Math.max(2, current - 1); p <= Math.min(total - 1, current + 1); p++) pages.push(p);
        if (current < total - 2) pages.push('…');
        pages.push(total);
        return pages;
    }

    if (totalRows > 0) {
        renderPage(1);
    } else {
        var bar = document.getElementById('pagination-bar');
        if (bar) bar.style.display = 'none';
    }
</script>

</body>
</html>