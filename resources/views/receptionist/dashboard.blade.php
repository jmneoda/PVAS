{{-- resources/views/receptionist/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PVAS – Receptionist Dashboard</title>

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
            background: #ffffff; border-right: 2px solid #808080;
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
        .role-badge .role-dot     { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .role-badge .role-name    { font-size: 13px; font-weight: 800; }
        .role-badge .role-divider { width: 1px; height: 14px; background: currentColor; opacity: 0.25; }
        .role-badge .role-label   { font-size: 11px; font-weight: 700; opacity: 0.75; text-transform: uppercase; letter-spacing: 0.05em; }

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
            cursor: pointer; padding: 2px 4px; border-radius: 3px;
            transition: color 0.15s, background 0.15s; white-space: nowrap;
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
        .date-banner svg    { flex-shrink: 0; color: #777; }
        .date-banner strong { color: #222; }

        /* ── Stat Cards ── */
        .stat-cards { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }

        .stat-card {
            background: #ffffff; border: 1.5px solid #ccc; border-radius: 6px;
            padding: 18px 12px 14px; text-align: center;
            position: relative; overflow: hidden;
            text-decoration: none; display: block; cursor: pointer;
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

        /* ── Completed card gets a "reports" destination badge ── */
        .stat-card.completed .reports-badge {
            position: absolute; top: 8px; right: 8px;
            font-size: 9px; font-weight: 800; text-transform: uppercase;
            letter-spacing: 0.06em; color: #155724;
            background: #e8f5e9; border: 1px solid #a5d6a7;
            border-radius: 10px; padding: 2px 7px; line-height: 1.4;
            white-space: nowrap;
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

        /* ── Summary Row ── */
        .summary-row {
            background: #ffffff; border: 1.5px solid #ccc; border-radius: 6px;
            padding: 18px 20px; display: flex; align-items: center; gap: 16px;
        }
        .summary-row .summary-label    { font-size: 13px; font-weight: 800; color: #444; white-space: nowrap; }
        .summary-row .summary-sublabel { font-size: 11px; color: #888; font-weight: 600; }
        .summary-row .summary-value    { font-size: 28px; font-weight: 800; color: #111; line-height: 1; }
        .summary-row .summary-divider  { width: 1px; height: 36px; background: #ddd; }
        .summary-row .summary-spacer   { flex: 1; }
        .summary-row .view-all-link {
            font-size: 12px; font-weight: 700; color: #2196f3; text-decoration: none;
            border: 1.5px solid #90c8ff; background: #dbeeff; border-radius: 4px;
            padding: 6px 14px; transition: background 0.15s; white-space: nowrap;
        }
        .summary-row .view-all-link:hover { background: #bfddff; }

        /* ── Reports shortcut link in summary ── */
        .summary-row .view-reports-link {
            font-size: 12px; font-weight: 700; color: #155724; text-decoration: none;
            border: 1.5px solid #a5d6a7; background: #e8f5e9; border-radius: 4px;
            padding: 6px 14px; transition: background 0.15s; white-space: nowrap;
        }
        .summary-row .view-reports-link:hover { background: #c8e6c9; }

        /* ════════════════════════════
           HISTOGRAM CARD
        ════════════════════════════ */
        .histogram-card {
            background: #ffffff;
            border: 1.5px solid #ccc;
            border-radius: 6px;
            overflow: hidden;
        }
        .histogram-header {
            background: #f2f2f2; padding: 12px 16px;
            display: flex; align-items: center; gap: 8px;
            border-bottom: 1.5px solid #ddd;
        }
        .histogram-header h2 { font-size: 14px; font-weight: 800; color: #222; }
        .histogram-header .date-tag {
            margin-left: auto; font-size: 11px; font-weight: 700; color: #666;
            background: #e4e4e4; border: 1px solid #ccc; border-radius: 10px; padding: 2px 10px;
        }

        /* ── Completed bar gets a "→ Reports" label inside the legend ── */
        .legend-reports-hint {
            font-size: 9px; font-weight: 800; text-transform: uppercase;
            letter-spacing: 0.05em; color: #155724;
            background: #e8f5e9; border: 1px solid #a5d6a7;
            border-radius: 8px; padding: 1px 6px; margin-left: 2px;
        }

        .histogram-body { padding: 28px 32px 20px; display: flex; flex-direction: column; gap: 20px; }

        /* ── Chart area ── */
        .chart-area { display: flex; align-items: flex-end; gap: 0; height: 240px; position: relative; }

        .y-axis {
            display: flex; flex-direction: column; justify-content: space-between;
            align-items: flex-end; padding-bottom: 36px; padding-right: 6px;
            height: 100%; flex-shrink: 0; width: 36px;
        }
        .y-label { font-size: 10px; font-weight: 700; color: #888; line-height: 1; }

        .chart-inner {
            flex: 1; display: flex; flex-direction: column; height: 100%;
            position: relative;
            border-left: 2px solid #555;
            border-bottom: 2px solid #555;
        }

        .grid-lines {
            position: absolute; inset: 0; bottom: 36px;
            display: flex; flex-direction: column; justify-content: space-between;
            pointer-events: none; z-index: 0;
        }
        .grid-line { width: 100%; height: 1px; background: #e0e0e0; }

        .bars-row {
            flex: 1; display: flex; align-items: flex-end;
            gap: 0; padding: 0; position: relative; z-index: 1;
        }

        .bar-group {
            flex: 1; display: flex; flex-direction: column;
            align-items: stretch; height: 100%; justify-content: flex-end; position: relative;
        }

        .bar {
            width: 100%; border-radius: 0;
            border-right: 1px solid rgba(255,255,255,0.35);
            position: relative; cursor: pointer;
            transition: filter 0.15s;
            transform-origin: bottom center;
            transform: scaleY(0);
            animation: barRise 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            display: block;
            text-decoration: none;
        }
        .bar-group:last-child .bar { border-right: none; }
        .bar:hover { filter: brightness(1.1); }

        /* Completed bar gets a subtle diagonal stripe pattern to signal it's different */
        .bar.bar-completed {
            background-image: repeating-linear-gradient(
                135deg,
                transparent,
                transparent 6px,
                rgba(255,255,255,0.12) 6px,
                rgba(255,255,255,0.12) 12px
            ) !important;
        }

        .bar::after {
            content: attr(data-tooltip);
            position: absolute; bottom: calc(100% + 8px); left: 50%;
            transform: translateX(-50%);
            background: #1a1a1a; color: #fff;
            font-size: 11px; font-weight: 700;
            padding: 4px 10px; border-radius: 4px;
            white-space: nowrap; pointer-events: none;
            opacity: 0; transition: opacity 0.15s; z-index: 10;
        }
        .bar:hover::after { opacity: 1; }

        @keyframes barRise {
            from { transform: scaleY(0); }
            to   { transform: scaleY(1); }
        }

        .x-axis-row { height: 36px; display: flex; align-items: center; gap: 0; padding: 0; }
        .x-label {
            flex: 1; text-align: center;
            font-size: 11px; font-weight: 700; color: #555;
            letter-spacing: 0.03em; line-height: 1.2;
        }

        /* ── Legend ── */
        .chart-legend {
            display: flex; align-items: center; justify-content: center;
            gap: 20px; flex-wrap: wrap;
            padding-top: 4px; border-top: 1px solid #f0f0f0;
        }
        .legend-item { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; color: #555; }
        .legend-dot  { width: 10px; height: 10px; border-radius: 2px; flex-shrink: 0; }

        /* ── Empty state ── */
        .chart-empty {
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; height: 240px; color: #bbb; gap: 10px;
        }
        .chart-empty svg { opacity: 0.35; }
        .chart-empty p   { font-size: 13px; font-weight: 700; font-style: italic; }

        /* ── Flash ── */
        .flash-success {
            display: flex; align-items: center; gap: 8px; padding: 8px 14px;
            background: #e8f5e9; border: 1px solid #81c784; border-radius: 4px;
            font-size: 13px; font-weight: 700; color: #155724;
        }

        @media (max-width: 1100px) { .stat-cards { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 700px) {
            .sidebar { width: 130px; min-width: 130px; }
            .stat-cards { grid-template-columns: repeat(2, 1fr); }
            .header-right { gap: 8px; }
            .role-badge .role-name { display: none; }
            .histogram-body { padding: 18px 14px 14px; }
        }
    </style>
</head>
<body>

{{-- ══════════════════════════════
     SIDEBAR
══════════════════════════════ --}}
<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="{{ asset('images/PVAS.png') }}" alt="PVAS Logo">
    </div>
    <nav>
        <a href="{{ route('receptionist.dashboard') }}"
           class="{{ request()->routeIs('receptionist.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('receptionist.customers.index') }}"
           class="{{ request()->routeIs('receptionist.customers.*') ? 'active' : '' }}">Customer</a>
        <a href="{{ route('receptionist.appointments.index') }}"
           class="{{ request()->routeIs('receptionist.appointments.*') ? 'active' : '' }}">Appointment</a>
        <a href="{{ route('receptionist.reports.index') }}"
           class="{{ request()->routeIs('receptionist.reports.*') ? 'active' : '' }}">Reports</a>
    </nav>
    <div class="sidebar-spacer"></div>
    <nav>
        <a href="{{ route('logout') }}" class="logout"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
    </nav>
    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>
</aside>

{{-- ══════════════════════════════
     MAIN CONTENT
══════════════════════════════ --}}
<main class="main">

    {{-- ── Page Header ── --}}
    <div class="page-header">
        <h1>Dashboard</h1>
        <div class="header-right">
            @php
                $authUser  = Auth::user();
                $roleColor = ['bg' => '#e3f2fd', 'border' => '#90caf9', 'text' => '#0d47a1', 'dot' => '#2196f3'];
            @endphp
            <div class="role-badge"
                 style="background-color:{{ $roleColor['bg'] }};border-color:{{ $roleColor['border'] }};color:{{ $roleColor['text'] }};">
                <span class="role-dot" style="background:{{ $roleColor['dot'] }};"></span>
                <span class="role-name">{{ $authUser?->name ?? 'User' }}</span>
                <span class="role-divider"></span>
                <span class="role-label">Receptionist</span>
            </div>

            <form method="GET" action="{{ route('receptionist.dashboard') }}" id="date-form">
                <div class="filter-wrapper">
                    <label for="date-input">Date:</label>
                    <input type="date" id="date-input" name="date" value="{{ $selectedDate ?? '' }}"
                           onchange="document.getElementById('date-form').submit()">
                    @if($selectedDate)
                        <button type="button" class="btn-clear-date"
                                onclick="document.getElementById('date-input').value=''; document.getElementById('date-form').submit();">
                            ✕ Clear
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ── Dashboard Body ── --}}
    <div class="dashboard-body">

        @if(session('success'))
            <div class="flash-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if($selectedDate)
            <div class="date-banner">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8"  y1="2" x2="8"  y2="6"/>
                    <line x1="3"  y1="10" x2="21" y2="10"/>
                </svg>
                Showing data for: <strong>{{ $dateLabel }}</strong>
            </div>
        @endif


        {{-- ── Stat Cards ── --}}
        {{--
            IMPORTANT: 'key' values below match the DB ENUM exactly:
            enum('scheduled','confirmed','completed','no_show','canceled')
            'canceled' = one 'l'  ← must stay this way

            ROUTING LOGIC:
            • All statuses except 'completed' → receptionist.appointments.index
            • 'completed' → receptionist.reports.index
              (completed appointments are locked & live in Reports only)
        --}}
        @php
            $apptBase    = route('receptionist.appointments.index');
            $reportsBase = route('receptionist.reports.index');

            /*
             * Build the Reports URL for the Completed card.
             * If a date filter is active, pass date_filter=custom&custom_date=<date>
             * so the Reports table opens on the same day the user is browsing.
             * Otherwise, default to the Reports "this_week" view.
             */
            $completedReportsHref = $selectedDate
                ? $reportsBase . '?' . http_build_query([
                    'date_filter' => 'custom',
                    'custom_date' => $selectedDate,
                  ])
                : $reportsBase;

            $cards = [
                [
                    'key'   => 'scheduled',
                    'css'   => 'scheduled',
                    'label' => 'Scheduled',
                    'count' => $scheduledCount,
                    'href'  => $apptBase . '?' . http_build_query(array_filter([
                                   'status' => 'scheduled',
                                   'date'   => $selectedDate,
                               ])),
                ],
                [
                    'key'   => 'confirmed',
                    'css'   => 'confirmed',
                    'label' => 'Confirmed',
                    'count' => $confirmedCount,
                    'href'  => $apptBase . '?' . http_build_query(array_filter([
                                   'status' => 'confirmed',
                                   'date'   => $selectedDate,
                               ])),
                ],
                [
                    'key'   => 'completed',
                    'css'   => 'completed',
                    'label' => 'Completed',
                    'count' => $completedCount,
                    // ← Goes to Reports, not Appointments
                    'href'  => $completedReportsHref,
                ],
                [
                    'key'   => 'no_show',
                    'css'   => 'no-show',
                    'label' => 'No Show',
                    'count' => $noShowCount,
                    'href'  => $apptBase . '?' . http_build_query(array_filter([
                                   'status' => 'no_show',
                                   'date'   => $selectedDate,
                               ])),
                ],
                [
                    'key'   => 'canceled',
                    'css'   => 'cancelled',
                    'label' => 'Cancelled',
                    'count' => $canceledCount,
                    //        ^^^^^^^^^ DB enum value (one 'l')
                    'href'  => $apptBase . '?' . http_build_query(array_filter([
                                   'status' => 'canceled',
                                   //          ^^^^^^^ DB enum (one 'l')
                                   'date'   => $selectedDate,
                               ])),
                ],
            ];
        @endphp

        <div class="stat-cards">
            @foreach($cards as $card)
                <a href="{{ $card['href'] }}" class="stat-card {{ $card['css'] }}">

                    {{-- Badge only on the Completed card to signal it routes to Reports --}}
                    @if($card['key'] === 'completed')
                        <span class="reports-badge">→ Reports</span>
                    @endif

                    <div class="stat-label">{{ $card['label'] }}</div>
                    <div class="stat-value">{{ $card['count'] }}</div>

                    <span class="quick-link-hint">
                        {{ $card['key'] === 'completed' ? 'View in Reports →' : 'View all →' }}
                    </span>
                </a>
            @endforeach
        </div>

        {{-- ── Summary Row ── --}}
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
            {{-- Link to the full Reports table for completed records --}}
            <a href="{{ $completedReportsHref }}" class="view-reports-link">View Reports →</a>
            <a href="{{ $apptBase }}" class="view-all-link">View All Appointments →</a>
        </div>

        {{-- ── Appointment Status Histogram ── --}}
        {{--
            ROUTING LOGIC (mirrors the stat cards above):
            • Completed bar → receptionist.reports.index  (with date_filter if applicable)
            • All other bars → receptionist.appointments.index
        --}}
        @php
            $chartData = [
                [
                    'key'   => 'scheduled',
                    'label' => 'Scheduled',
                    'count' => $scheduledCount,
                    'color' => '#2196f3',
                    'bg'    => '#dbeeff',
                    'text'  => '#004085',
                    'href'  => $apptBase . '?' . http_build_query(array_filter(['status' => 'scheduled', 'date' => $selectedDate])),
                ],
                [
                    'key'   => 'confirmed',
                    'label' => 'Confirmed',
                    'count' => $confirmedCount,
                    'color' => '#7c3aed',
                    'bg'    => '#ede9fe',
                    'text'  => '#3b0764',
                    'href'  => $apptBase . '?' . http_build_query(array_filter(['status' => 'confirmed', 'date' => $selectedDate])),
                ],
                [
                    'key'   => 'completed',
                    'label' => 'Completed',
                    'count' => $completedCount,
                    'color' => '#27ae60',
                    'bg'    => '#e8f5e9',
                    'text'  => '#155724',
                    // ← Routes to Reports, not Appointments
                    'href'  => $completedReportsHref,
                ],
                [
                    'key'   => 'no_show',
                    'label' => 'No Show',
                    'count' => $noShowCount,
                    'color' => '#f59e0b',
                    'bg'    => '#fffbeb',
                    'text'  => '#78350f',
                    'href'  => $apptBase . '?' . http_build_query(array_filter(['status' => 'no_show', 'date' => $selectedDate])),
                ],
                [
                    'key'   => 'canceled',
                    'label' => 'Cancelled',
                    'count' => $canceledCount,
                    //        ^^^^^^^^^ DB enum (one 'l')
                    'color' => '#e53935',
                    'bg'    => '#fde8e8',
                    'text'  => '#842029',
                    'href'  => $apptBase . '?' . http_build_query(array_filter(['status' => 'canceled', 'date' => $selectedDate])),
                    //                                                                     ^^^^^^^ DB enum (one 'l')
                ],
            ];
            $maxCount = max(max(array_column($chartData, 'count')), 1);
            $yMax     = (int) ceil($maxCount / 5) * 5;
            if ($yMax < 5) $yMax = 5;
            $yStep    = $yMax / 5;
        @endphp

        <div class="histogram-card">
            <div class="histogram-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <rect x="3" y="12" width="4" height="9"/>
                    <rect x="10" y="7"  width="4" height="14"/>
                    <rect x="17" y="3"  width="4" height="18"/>
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
                                        $delay     = $idx * 80;
                                        $isCompleted = $bar['key'] === 'completed';
                                        $tooltip   = $isCompleted
                                            ? $bar['label'] . ': ' . $bar['count'] . ' appointment' . ($bar['count'] !== 1 ? 's' : '') . ' – click to view in Reports'
                                            : $bar['label'] . ': ' . $bar['count'] . ' appointment' . ($bar['count'] !== 1 ? 's' : '');
                                    @endphp
                                    <div class="bar-group">
                                        <a href="{{ $bar['href'] }}"
                                           class="bar{{ $isCompleted ? ' bar-completed' : '' }}"
                                           style="height:{{ max($heightPct, $bar['count'] > 0 ? 2 : 0) }}%; background:{{ $bar['color'] }}; animation-delay:{{ $delay }}ms;"
                                           data-tooltip="{{ $tooltip }}"
                                           title="{{ $tooltip }}">
                                        </a>
                                    </div>
                                @endforeach
                            </div>

                            <div class="x-axis-row">
                                @foreach($chartData as $bar)
                                    <div class="x-label" style="color:{{ $bar['text'] }};">{{ $bar['label'] }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Legend --}}
                    <div class="chart-legend">
                        @foreach($chartData as $bar)
                            <div class="legend-item">
                                <span class="legend-dot" style="background:{{ $bar['color'] }};"></span>
                                {{ $bar['label'] }}
                                <span style="color:#999;font-weight:600;">({{ $bar['count'] }})</span>
                                {{-- Show a "→ Reports" pill next to Completed in the legend --}}
                                @if($bar['key'] === 'completed')
                                    <span class="legend-reports-hint">→ Reports</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                @else
                    <div class="chart-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <rect x="3" y="12" width="4" height="9"/>
                            <rect x="10" y="7"  width="4" height="14"/>
                            <rect x="17" y="3"  width="4" height="18"/>
                        </svg>
                        <p>No appointments found{{ $selectedDate ? ' for ' . $dateLabel : '' }}.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>{{-- /.dashboard-body --}}
</main>

</body>
</html>