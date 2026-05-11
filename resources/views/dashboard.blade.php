<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PVAS - Dashboard</title>

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
           SIDEBAR
        ════════════════════════════ */
        .sidebar {
            width: 180px; min-width: 180px;
            background: #FFFFFF; border-right: 2px solid #808080;
            display: flex; flex-direction: column; align-items: center;
            padding: 24px 0 0; position: sticky; top: 0; height: 100vh;
        }
        .sidebar-logo { width: 140px; height: 110px; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; }
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

        /* ════════════════════════════
           MAIN
        ════════════════════════════ */
        .main { flex: 1; display: flex; flex-direction: column; background: #c8c8c8; min-height: 100vh; overflow-x: hidden; }

        .page-header {
            background: #c8c8c8; border-bottom: 2px solid #999;
            padding: 10px 20px; display: flex; align-items: center;
            justify-content: space-between; gap: 12px; min-height: 56px;
        }
        .page-header h1 { font-size: 20px; font-weight: 800; color: #111; white-space: nowrap; }
        .header-right { display: flex; align-items: center; gap: 12px; }

        .role-badge {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 6px 14px; border-radius: 20px; border: 1.5px solid;
            font-size: 12px; font-weight: 700; white-space: nowrap;
        }
        .role-badge .role-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .role-badge .role-name { font-size: 13px; font-weight: 800; }
        .role-badge .role-divider { width: 1px; height: 14px; background: currentColor; opacity: 0.25; }
        .role-badge .role-label { font-size: 11px; font-weight: 700; opacity: 0.75; text-transform: uppercase; letter-spacing: 0.05em; }

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
        .btn-clear-date {
            font-size: 11px; font-weight: 700; color: #888; background: none; border: none;
            cursor: pointer; padding: 2px 4px; border-radius: 3px; transition: color 0.15s, background 0.15s; white-space: nowrap;
        }
        .btn-clear-date:hover { color: #e53935; background: #fde8e8; }

        /* ════════════════════════════
           DASHBOARD BODY
        ════════════════════════════ */
        .dashboard-body { padding: 16px 20px; display: flex; flex-direction: column; gap: 14px; }

        .date-banner {
            display: flex; align-items: center; gap: 8px;
            font-size: 12px; font-weight: 700; color: #555;
            background: #d4d4d4; border: 1px solid #bbb; border-radius: 4px; padding: 6px 14px;
        }
        .date-banner svg { flex-shrink: 0; color: #777; }
        .date-banner strong { color: #222; }

        /* ── Stat Cards ── */
        .stat-cards { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }

        .stat-card {
            background: #FFFFFF; border: 1.5px solid #ccc; border-radius: 6px;
            padding: 18px 12px 14px; text-align: center; position: relative;
            overflow: hidden; text-decoration: none; display: block; cursor: pointer;
            transition: transform 0.12s, box-shadow 0.12s, border-color 0.12s;
        }
        .stat-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0;
            height: 4px; background: var(--accent, #aaa); border-radius: 4px 4px 0 0;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.12); border-color: var(--accent, #aaa); }
        .stat-card:hover .quick-link-hint { opacity: 1; }
        .quick-link-hint {
            position: absolute; bottom: 6px; right: 8px;
            font-size: 9px; font-weight: 800; text-transform: uppercase;
            letter-spacing: 0.06em; opacity: 0; transition: opacity 0.15s; color: var(--accent, #aaa);
        }
        .stat-card.scheduled { --accent: #2196f3; }
        .stat-card.confirmed { --accent: #7c3aed; }
        .stat-card.completed { --accent: #27ae60; }
        .stat-card.no-show   { --accent: #f59e0b; }
        .stat-card.cancelled { --accent: #e53935; }
        .stat-card .stat-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 10px; }
        .stat-card.scheduled .stat-label { color: #0d47a1; }
        .stat-card.confirmed .stat-label { color: #3b0764; }
        .stat-card.completed .stat-label { color: #1b5e20; }
        .stat-card.no-show   .stat-label { color: #78350f; }
        .stat-card.cancelled .stat-label { color: #842029; }
        .stat-card .stat-value { font-size: 32px; font-weight: 800; line-height: 1; }
        .stat-card.scheduled .stat-value { color: #2196f3; }
        .stat-card.confirmed .stat-value { color: #7c3aed; }
        .stat-card.completed .stat-value { color: #27ae60; }
        .stat-card.no-show   .stat-value { color: #f59e0b; }
        .stat-card.cancelled .stat-value { color: #e53935; }

        /* ── Summary row ── */
        .summary-row {
            background: #FFFFFF; border: 1.5px solid #ccc; border-radius: 6px;
            padding: 18px 20px; display: flex; align-items: center; gap: 16px;
        }
        .summary-row .summary-label { font-size: 13px; font-weight: 800; color: #444; white-space: nowrap; }
        .summary-row .summary-sublabel { font-size: 11px; color: #888; font-weight: 600; }
        .summary-row .summary-value { font-size: 28px; font-weight: 800; color: #111; line-height: 1; }
        .summary-row .summary-divider { width: 1px; height: 36px; background: #ddd; }
        .summary-row .summary-spacer { flex: 1; }
        .summary-row .view-all-link {
            font-size: 12px; font-weight: 700; color: #2196f3; text-decoration: none;
            border: 1.5px solid #90c8ff; background: #dbeeff; border-radius: 4px;
            padding: 6px 14px; transition: background 0.15s; white-space: nowrap;
        }
        .summary-row .view-all-link:hover { background: #bfddff; }

        /* ════════════════════════════
           HISTOGRAM CARD
        ════════════════════════════ */
        .histogram-card {
            background: #FFFFFF;
            border: 1.5px solid #ccc;
            border-radius: 6px;
            overflow: hidden;
        }

        .histogram-header {
            background: #f2f2f2;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1.5px solid #ddd;
        }
        .histogram-header h2 { font-size: 14px; font-weight: 800; color: #222; }
        .histogram-header .date-tag {
            margin-left: auto;
            font-size: 11px; font-weight: 700; color: #666;
            background: #e4e4e4; border: 1px solid #ccc;
            border-radius: 10px; padding: 2px 10px;
        }

        .histogram-body {
            padding: 28px 32px 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ── Chart area ── */
        .chart-area {
            display: flex;
            align-items: flex-end;
            gap: 0;
            height: 240px;
            position: relative;
        }

        /* Y-axis labels */
        .y-axis {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
            padding-bottom: 36px;
            padding-right: 6px;
            height: 100%;
            flex-shrink: 0;
            width: 36px;
        }
        .y-label {
            font-size: 10px;
            font-weight: 700;
            color: #888;
            line-height: 1;
        }

        /* Grid + bars wrapper */
        .chart-inner {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
            border-left: 2px solid #555;
            border-bottom: 2px solid #555;
        }

        /* Horizontal grid lines */
        .grid-lines {
            position: absolute;
            inset: 0;
            bottom: 36px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            pointer-events: none;
            z-index: 0;
        }
        .grid-line {
            width: 100%;
            height: 1px;
            background: #e0e0e0;
        }

        /* Bars row */
        .bars-row {
            flex: 1;
            display: flex;
            align-items: flex-end;
            gap: 0;
            padding: 0;
            position: relative;
            z-index: 1;
        }

        .bar-group {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            height: 100%;
            justify-content: flex-end;
            position: relative;
        }

        .bar {
            width: 100%;
            border-radius: 0;
            border-right: 1px solid rgba(255,255,255,0.35);
            position: relative;
            cursor: pointer;
            transition: filter 0.15s;
            transform-origin: bottom center;
            transform: scaleY(0);
            animation: barRise 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        .bar-group:last-child .bar { border-right: none; }
        .bar:hover { filter: brightness(1.1); }

        .bar::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            background: #1a1a1a;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 4px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s;
            z-index: 10;
        }
        .bar:hover::after { opacity: 1; }

        @keyframes barRise {
            from { transform: scaleY(0); }
            to   { transform: scaleY(1); }
        }

        /* X-axis labels */
        .x-axis-row {
            height: 36px;
            display: flex;
            align-items: center;
            gap: 0;
            padding: 0;
        }
        .x-label {
            flex: 1;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            color: #555;
            letter-spacing: 0.03em;
            line-height: 1.2;
        }

        /* ── Legend ── */
        .chart-legend {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            padding-top: 4px;
            border-top: 1px solid #f0f0f0;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            color: #555;
            cursor: default;
        }
        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 2px;
            flex-shrink: 0;
        }

        /* ── Empty state ── */
        .chart-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 220px;
            color: #bbb;
            gap: 10px;
        }
        .chart-empty svg { opacity: 0.35; }
        .chart-empty p { font-size: 13px; font-weight: 700; font-style: italic; }

        /* ── Flash ── */
        .flash-success {
            display: flex; align-items: center; gap: 8px; padding: 8px 14px;
            background: #e8f5e9; border: 1px solid #81c784; border-radius: 4px;
            font-size: 13px; font-weight: 700; color: #155724;
        }

        /* ════════════════════════════
           APPOINTMENT DETAIL MODAL
        ════════════════════════════ */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.48); z-index: 999;
            align-items: flex-start; justify-content: center; padding-top: 40px;
            overflow-y: auto;
        }
        .modal-overlay.open { display: flex; }

        .modal-box {
            background: #fff; border-radius: 8px; width: 520px; max-width: 95vw;
            box-shadow: 0 8px 40px rgba(0,0,0,0.28); overflow: hidden;
            margin-bottom: 40px;
        }

        .modal-head {
            background: #f0f0f0; border-bottom: 1.5px solid #ddd;
            padding: 12px 18px; display: flex; align-items: center; justify-content: space-between;
        }
        .modal-head h3 { font-size: 15px; font-weight: 800; color: #222; }
        .modal-close-btn { background: none; border: none; font-size: 22px; cursor: pointer; color: #555; line-height: 1; padding: 0 4px; }
        .modal-close-btn:hover { color: #000; }

        .modal-body { padding: 20px 24px 8px; }
        .detail-row { display: flex; gap: 8px; margin-bottom: 12px; font-size: 13px; line-height: 1.5; }
        .detail-label { font-weight: 800; color: #111; min-width: 140px; flex-shrink: 0; }
        .detail-value { color: #444; }

        .timeline-section { border-top: 1.5px solid #eee; padding: 16px 24px 4px; }
        .timeline-section h4 { font-size: 11px; font-weight: 800; color: #555; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 14px; }
        .timeline { display: flex; flex-direction: column; gap: 0; }
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

        .lock-notice { display: flex; align-items: center; gap: 8px; background: #f8f8f8; border: 1.5px solid #ddd; border-radius: 4px; padding: 10px 14px; margin: 4px 0 8px; font-size: 12px; font-weight: 700; color: #777; }

        .status-update-section { border-top: 1.5px solid #eee; padding: 14px 24px 4px; }
        .status-update-section label { font-size: 12px; font-weight: 800; color: #333; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em; }
        .status-row { display: flex; align-items: center; gap: 8px; }
        .status-select-modal { flex: 1; font-size: 13px; font-weight: 700; font-family: 'Figtree', sans-serif; padding: 7px 10px; border: 1.5px solid #ccc; border-radius: 4px; background: #f8f8f8; color: #222; cursor: pointer; outline: none; transition: border-color 0.15s; }
        .status-select-modal:focus { border-color: #2196f3; }
        .status-select-modal:disabled { background: #eee; color: #aaa; cursor: not-allowed; }
        .btn-save-status { font-size: 12px; font-weight: 800; padding: 7px 18px; border-radius: 4px; border: 1.5px solid #81c784; background: #e8f5e9; color: #155724; cursor: pointer; transition: background 0.15s; white-space: nowrap; font-family: 'Figtree', sans-serif; }
        .btn-save-status:hover { background: #c8e6c9; }
        .btn-save-status:disabled { background: #eee; border-color: #ccc; color: #aaa; cursor: not-allowed; }

        .modal-foot { padding: 12px 24px 16px; display: flex; justify-content: flex-end; }
        .btn-close-modal { font-size: 13px; font-weight: 700; padding: 7px 20px; border-radius: 4px; border: 1.5px solid #bbb; background: #e8e8e8; color: #444; cursor: pointer; transition: background 0.15s; font-family: 'Figtree', sans-serif; }
        .btn-close-modal:hover { background: #d8d8d8; }

        #saving-indicator { font-size: 12px; font-weight: 700; color: #2196f3; display: none; }
        .modal-loading { padding: 40px; text-align: center; color: #aaa; font-size: 13px; font-weight: 700; }

        @media (max-width: 1100px) { .stat-cards { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 700px) {
            .sidebar { width: 130px; min-width: 130px; }
            .stat-cards { grid-template-columns: repeat(2, 1fr); }
            .header-right { gap: 8px; }
            .histogram-body { padding: 18px 14px 14px; }
            .bars-row { gap: 8px; padding: 0 4px; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="{{ asset('images/PVAS.png') }}" alt="PVAS Logo">
    </div>
    <nav>
        <a href="{{ route('dashboard') }}"
           class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>

        <a href="{{ \Route::has('customers.index') ? route('customers.index') : '#' }}"
           class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">Customers</a>

        <a href="{{ \Route::has('appointments.index') ? route('appointments.index') : '#' }}"
           class="{{ request()->routeIs('appointments.*') ? 'active' : '' }}">Appointment</a>

        <a href="{{ \Route::has('staff.index') ? route('staff.index') : '#' }}"
           class="{{ request()->routeIs('staff.*') ? 'active' : '' }}">Staff</a>

        <a href="{{ \Route::has('reports.index') ? route('reports.index') : '#' }}"
           class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">Reports</a>
    </nav>
    <div class="sidebar-spacer"></div>
    <nav>
        <a href="{{ route('logout') }}" class="logout"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
    </nav>
    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>
</aside>

<main class="main">

    <div class="page-header">
        <h1>Dashboard</h1>
        <div class="header-right">
            <div class="role-badge"
                 style="background-color:{{ $roleColor['bg'] }};border-color:{{ $roleColor['border'] }};color:{{ $roleColor['text'] }};">
                <span class="role-dot" style="background:{{ $roleColor['dot'] }};"></span>
                <span class="role-name">{{ $userName }}</span>
                <span class="role-divider"></span>
                <span class="role-label">{{ $roleLabel }}</span>
            </div>
            <form method="GET" action="{{ route('dashboard') }}" id="date-form">
                <div class="filter-wrapper">
                    <label for="date-input">Date:</label>
                    <input type="date" id="date-input" name="date" value="{{ $selectedDate ?? '' }}"
                           onchange="document.getElementById('date-form').submit()">
                    @if($selectedDate)
                        <button type="button" class="btn-clear-date"
                                onclick="document.getElementById('date-input').value=''; document.getElementById('date-form').submit();">✕ Clear</button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="dashboard-body">

        @if(session('success'))
            <div class="flash-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @if($selectedDate)
            <div class="date-banner">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Showing data for: <strong>{{ $dateLabel }}</strong>
            </div>
        @endif

        {{-- Stat Cards --}}
        <div class="stat-cards">
            @php
                $cards = [
                    ['key' => 'scheduled', 'css' => 'scheduled', 'label' => 'Scheduled', 'count' => $scheduledCount],
                    ['key' => 'confirmed', 'css' => 'confirmed', 'label' => 'Confirmed', 'count' => $confirmedCount],
                    ['key' => 'completed', 'css' => 'completed', 'label' => 'Completed', 'count' => $completedCount],
                    ['key' => 'no_show',   'css' => 'no-show',   'label' => 'No Show',   'count' => $noShowCount],
                    // FIX: key must be 'canceled' (one L) to match the DB enum and appointments filter
                    ['key' => 'canceled',  'css' => 'cancelled', 'label' => 'Cancelled', 'count' => $cancelledCount],
                ];
                $apptBase = \Route::has('appointments.index') ? route('appointments.index') : '#';
            @endphp
            @foreach($cards as $card)
                @php
                    $href = $apptBase !== '#'
                        ? $apptBase . '?' . http_build_query(array_filter(['status' => $card['key'], 'date' => $selectedDate]))
                        : '#';
                @endphp
                <a href="{{ $href }}" class="stat-card {{ $card['css'] }}">
                    <div class="stat-label">{{ $card['label'] }}</div>
                    <div class="stat-value">{{ $card['count'] }}</div>
                    <span class="quick-link-hint">View all →</span>
                </a>
            @endforeach
        </div>

        {{-- Summary row --}}
        <div class="summary-row">
            <div>
                <div class="summary-label">Total Appointments</div>
                <div class="summary-sublabel">{{ $selectedDate ? $dateLabel : 'All Dates' }}</div>
            </div>
            <div class="summary-value">{{ $totalAppointments }}</div>
            <div class="summary-divider"></div>
            <div>
                <div class="summary-label">This Month</div>
                <div class="summary-sublabel">{{ now()->format('F Y') }}</div>
            </div>
            <div class="summary-value">{{ $monthlyAppointments }}</div>
            <div class="summary-spacer"></div>
            @if(\Route::has('appointments.index'))
                <a href="{{ route('appointments.index') }}" class="view-all-link">View All Appointments →</a>
            @endif
        </div>

        {{-- ── Appointment Status Histogram ── --}}
        @php
            $chartData = [
                ['key' => 'scheduled', 'label' => 'Scheduled', 'count' => $scheduledCount, 'color' => '#2196f3', 'bg' => '#dbeeff', 'text' => '#004085'],
                ['key' => 'confirmed', 'label' => 'Confirmed', 'count' => $confirmedCount, 'color' => '#7c3aed', 'bg' => '#ede9fe', 'text' => '#3b0764'],
                ['key' => 'completed', 'label' => 'Completed', 'count' => $completedCount, 'color' => '#27ae60', 'bg' => '#e8f5e9', 'text' => '#155724'],
                ['key' => 'no_show',   'label' => 'No Show',   'count' => $noShowCount,    'color' => '#f59e0b', 'bg' => '#fffbeb', 'text' => '#78350f'],
                // FIX: key must be 'canceled' (one L) so the bar link routes correctly
                ['key' => 'canceled',  'label' => 'Cancelled', 'count' => $cancelledCount, 'color' => '#e53935', 'bg' => '#fde8e8', 'text' => '#842029'],
            ];
            $maxCount = max(max(array_column($chartData, 'count')), 1);
            $yMax = (int) ceil($maxCount / 5) * 5;
            if ($yMax < 5) $yMax = 5;
            $yStep = $yMax / 5;
        @endphp

        <div class="histogram-card">
            <div class="histogram-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <rect x="3" y="12" width="4" height="9"/><rect x="10" y="7" width="4" height="14"/><rect x="17" y="3" width="4" height="18"/>
                </svg>
                <h2>Appointments by Status</h2>
                <span class="date-tag">{{ $dateLabel }}</span>
            </div>

            <div class="histogram-body">
                @if($totalAppointments > 0)
                    <div class="chart-area">
                        {{-- Y-axis --}}
                        <div class="y-axis">
                            @for($i = 5; $i >= 0; $i--)
                                <span class="y-label">{{ (int)($yStep * $i) }}</span>
                            @endfor
                        </div>

                        {{-- Inner: grid + bars --}}
                        <div class="chart-inner">
                            <div class="grid-lines">
                                @for($i = 0; $i < 6; $i++)
                                    <div class="grid-line"></div>
                                @endfor
                            </div>

                            <div class="bars-row">
                                @foreach($chartData as $idx => $bar)
                                    @php
                                        $heightPct = $yMax > 0 ? ($bar['count'] / $yMax) * 100 : 0;
                                        $delay = $idx * 80;
                                    @endphp
                                    <div class="bar-group">
                                        <a href="{{ $apptBase !== '#' ? $apptBase . '?' . http_build_query(array_filter(['status' => $bar['key'], 'date' => $selectedDate])) : '#' }}"
                                           class="bar"
                                           style="
                                               height: {{ max($heightPct, $bar['count'] > 0 ? 2 : 0) }}%;
                                               background: {{ $bar['color'] }};
                                               animation-delay: {{ $delay }}ms;
                                           "
                                           data-tooltip="{{ $bar['label'] }}: {{ $bar['count'] }} appointment{{ $bar['count'] !== 1 ? 's' : '' }}"
                                           title="{{ $bar['label'] }}: {{ $bar['count'] }}">
                                        </a>
                                    </div>
                                @endforeach
                            </div>

                            <div class="x-axis-row">
                                @foreach($chartData as $bar)
                                    <div class="x-label" style="color: {{ $bar['text'] }};">{{ $bar['label'] }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="chart-legend">
                        @foreach($chartData as $bar)
                            <div class="legend-item">
                                <span class="legend-dot" style="background: {{ $bar['color'] }};"></span>
                                {{ $bar['label'] }} <span style="color:#999; font-weight:600;">({{ $bar['count'] }})</span>
                            </div>
                        @endforeach
                    </div>

                @else
                    <div class="chart-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="12" width="4" height="9"/><rect x="10" y="7" width="4" height="14"/><rect x="17" y="3" width="4" height="18"/>
                        </svg>
                        <p>No appointments found{{ $selectedDate ? ' for ' . $dateLabel : '' }}.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</main>

{{-- ══════════════════════════════════════════════
     APPOINTMENT DETAIL MODAL (with status history)
══════════════════════════════════════════════ --}}
<div class="modal-overlay" id="detailModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3>Appointment Details</h3>
            <button class="modal-close-btn" onclick="closeModal('detailModal')">&times;</button>
        </div>

        <div id="modal-content">
            <div class="modal-loading">Loading…</div>
        </div>

        <div class="modal-foot">
            <button class="btn-close-modal" onclick="closeModal('detailModal')">Close</button>
        </div>
    </div>
</div>

<script>
    const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
    let activeId = null;

    function openModal(id)  { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }

    document.querySelectorAll('.modal-overlay').forEach(el => {
        el.addEventListener('click', function(e) { if (e.target === this) closeModal(this.id); });
    });

    const statusConfig = {
        scheduled : { label: 'Scheduled', bg: '#dbeeff', color: '#004085', border: '#90c8ff', dot: '#2196f3' },
        confirmed : { label: 'Confirmed', bg: '#ede9fe', color: '#3b0764', border: '#c4b5fd', dot: '#7c3aed' },
        completed : { label: 'Completed', bg: '#e8f5e9', color: '#155724', border: '#81c784', dot: '#27ae60' },
        no_show   : { label: 'No Show',   bg: '#fffbeb', color: '#78350f', border: '#fcd34d', dot: '#f59e0b' },
        // FIX: both spellings map to the same config so timeline renders correctly
        canceled  : { label: 'Cancelled', bg: '#fde8e8', color: '#842029', border: '#f5a5a5', dot: '#e53935' },
        cancelled : { label: 'Cancelled', bg: '#fde8e8', color: '#842029', border: '#f5a5a5', dot: '#e53935' },
    };

    function normStatus(s) {
        if (!s) return s;
        const lower = s.toLowerCase();
        return lower === 'cancelled' ? 'canceled' : lower;
    }

    function formatDateTime(dateStr, timeStr) {
        if (!dateStr) return '—';
        try {
            const d = new Date(dateStr + (timeStr ? 'T' + timeStr : ''));
            if (isNaN(d)) return dateStr + (timeStr ? ' ' + timeStr : '');
            const date = d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
            const time = timeStr ? d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : '';
            return date + (time ? '  ' + time : '');
        } catch(e) { return dateStr; }
    }

    function buildModalContent(data) {
        // FIX: lock on both completed and canceled
        const isLocked = ['completed', 'canceled'].includes(normStatus(data.status));
        const history  = data.status_history || [];

        const details = [
            ['Appointment ID',   '#' + data.id],
            ['Date & Time',      formatDateTime(data.scheduled_date, data.scheduled_time)],
            ['Customer',         (data.customer_name || '—') + '  (ID: ' + data.customer_id + ')'],
            ['Pet',              data.pet_name     || '—'],
            ['Veterinarian',     data.vet_name     || '—'],
            ['Address',          data.address      || '—'],
            ['Contact Number',   data.contact_number || '—'],
            ['Reason for Visit', data.reason_for_visit || '—'],
        ];

        let detailHTML = details.map(([label, value]) =>
            `<div class="detail-row">
                <span class="detail-label">${label}</span>
                <span class="detail-value">${escHtml(value)}</span>
            </div>`
        ).join('');

        let timelineHTML = '';
        if (history.length > 0) {
            timelineHTML = history.map(item => {
                const sc = statusConfig[normStatus(item.status)] || { label: item.status, dot: '#aaa', bg: '#f5f5f5', color: '#555', border: '#ccc' };
                let dateDisplay = '—', timeDisplay = '';
                if (item.changed_at) {
                    try {
                        const dt = new Date(item.changed_at);
                        dateDisplay = dt.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                        timeDisplay = dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                    } catch(e) { dateDisplay = item.changed_at; }
                }
                const tlClass = normStatus(item.status);
                return `<div class="timeline-item tl-${tlClass}">
                    <div class="timeline-dot-wrap"><div class="timeline-dot"><div class="dot-inner"></div></div></div>
                    <div class="timeline-content">
                        <div class="timeline-status-label" style="background:${sc.bg};color:${sc.color};border-color:${sc.border};">${escHtml(sc.label)}</div>
                        <div class="timeline-timestamp">
                            <span>${escHtml(dateDisplay)}</span>
                            ${timeDisplay ? ' &nbsp;·&nbsp; <span>' + escHtml(timeDisplay) + '</span>' : ''}
                            ${item.changed_by ? ' &nbsp;·&nbsp; by <span>' + escHtml(item.changed_by) + '</span>' : ''}
                        </div>
                    </div>
                </div>`;
            }).join('');
        } else {
            timelineHTML = `<div class="timeline-empty">No status history recorded.</div>`;
        }

        const lockedStatus = normStatus(data.status);
        const lockNoticeHTML = isLocked
            ? `<div class="lock-notice">🔒 This appointment is <strong>${lockedStatus === 'completed' ? 'completed' : 'cancelled'}</strong> and cannot be modified.</div>`
            : '';

        const statusOptions = Object.entries(statusConfig)
            // dedupe: skip 'cancelled' key since 'canceled' covers it
            .filter(([val]) => val !== 'cancelled')
            .map(([val, sc]) => `<option value="${val}" ${normStatus(data.status) === val ? 'selected' : ''}>${sc.label}</option>`)
            .join('');

        const statusUpdateHTML = isLocked ? '' : `
            <div class="status-update-section">
                <label>Update Status</label>
                <div class="status-row">
                    <select class="status-select-modal" id="modal-status-select">${statusOptions}</select>
                    <button class="btn-save-status" id="btn-save-status" onclick="saveStatus()">Save</button>
                    <span id="saving-indicator">Saving…</span>
                </div>
            </div>`;

        return `
            <div class="modal-body">${detailHTML}${lockNoticeHTML}</div>
            <div class="timeline-section">
                <h4>Status History</h4>
                <div class="timeline">${timelineHTML}</div>
            </div>
            ${statusUpdateHTML}`;
    }

    function escHtml(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function openDetail(id) {
        activeId = id;
        document.getElementById('modal-content').innerHTML = '<div class="modal-loading">Loading…</div>';
        openModal('detailModal');

        fetch(`/appointments/${id}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => { document.getElementById('modal-content').innerHTML = buildModalContent(data); })
            .catch(() => {
                document.getElementById('modal-content').innerHTML =
                    '<div class="modal-loading" style="color:#e53935;">Could not load appointment details.</div>';
            });
    }

    function saveStatus() {
        const newStatus = document.getElementById('modal-status-select')?.value;
        const saveBtn   = document.getElementById('btn-save-status');
        const saving    = document.getElementById('saving-indicator');
        if (!newStatus || !saveBtn) return;

        saveBtn.disabled     = true;
        saving.style.display = 'inline';

        fetch(`/appointments/${activeId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type'     : 'application/json',
                'X-CSRF-TOKEN'     : CSRF,
                'X-Requested-With' : 'XMLHttpRequest',
                'Accept'           : 'application/json',
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(r => r.json())
        .then(data => {
            saving.style.display = 'none';
            if (data.error) { alert(data.error); saveBtn.disabled = false; return; }
            if (data.success) { openDetail(activeId); }
        })
        .catch(() => {
            saving.style.display = 'none';
            if (saveBtn) saveBtn.disabled = false;
            alert('Failed to update status. Please try again.');
        });
    }
</script>

</body>
</html>