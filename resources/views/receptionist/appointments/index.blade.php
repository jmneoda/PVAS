{{-- resources/views/receptionist/appointments/index.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PVAS – Appointments</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Figtree',sans-serif;background:#a0a0a0;min-height:100vh;display:flex}

        /* ── Sidebar ── */
        .sidebar{width:160px;min-width:160px;background:#fff;border-right:2px solid #888;display:flex;flex-direction:column;align-items:center;padding:20px 0 0;position:sticky;top:0;height:100vh}
        .sidebar-logo{width:130px;height:130px;margin-bottom:20px;display:flex;align-items:center;justify-content:center}
        .sidebar-logo img{width:100%;height:100%;object-fit:contain}
        .sidebar-nav{width:100%;display:flex;flex-direction:column;flex:1}
        .sidebar-nav a{display:block;width:100%;padding:15px 0;text-align:center;font-size:15px;font-weight:700;color:#111;text-decoration:none;border-top:1.5px solid #bbb}
        .sidebar-nav a:hover{background:#e0e0e0}
        .sidebar-nav a.active{background:#d0d0d0}
        .sidebar-nav a.logout{color:#cc0000;border-top:1.5px solid #bbb;border-bottom:1.5px solid #bbb;margin-top:auto}
        .sidebar-spacer{flex:1}

        /* ── Main ── */
        .main{flex:1;display:flex;flex-direction:column;background:#a8a8a8;min-height:100vh;overflow-x:hidden}

        /* ── Page Header ── */
        .page-header{background:#b8b8b8;border-bottom:2px solid #888;padding:10px 20px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;min-height:58px}
        .page-header h1{font-size:20px;font-weight:800;color:#111;white-space:nowrap;margin-right:auto}

        /* ── Status Filter Pills ── */
        .status-filters{display:flex;align-items:center;gap:5px;flex-wrap:wrap}
        .filter-pill{display:inline-flex;align-items:center;padding:3px 11px;border-radius:999px;font-size:12px;font-weight:700;border:1.5px solid #bbb;background:#e4e4e4;color:#444;cursor:pointer;text-decoration:none;transition:background .15s,color .15s;white-space:nowrap;line-height:1.6}
        .filter-pill:hover{background:#d0d0d0}
        .filter-pill.active-all      {background:#111;color:#fff;border-color:#111}
        .filter-pill.active-scheduled{background:#dbeafe;color:#1d4ed8;border-color:#93c5fd}
        .filter-pill.active-confirmed{background:#ede9fe;color:#6d28d9;border-color:#c4b5fd}
        .filter-pill.active-no_show  {background:#fef9c3;color:#854d0e;border-color:#fde047}
        .filter-pill.active-canceled {background:#fee2e2;color:#991b1b;border-color:#fca5a5}

        /* ── Date picker ── */
        .date-filter-wrapper{display:flex;align-items:center;gap:6px;background:#fff;border:1.5px solid #aaa;border-radius:6px;padding:5px 12px;white-space:nowrap}
        .date-filter-wrapper label{font-size:13px;font-weight:700;color:#444}
        .date-filter-wrapper input[type="date"]{border:none;background:transparent;font-size:13px;font-family:'Figtree',sans-serif;font-weight:600;color:#111;outline:none;cursor:pointer}
        .date-clear-btn{background:none;border:none;cursor:pointer;font-size:14px;color:#888;padding:0 2px;line-height:1;transition:color .15s}
        .date-clear-btn:hover{color:#c0392b}

        /* ── Page Body ── */
        .page-body{flex:1;padding:16px 20px;display:flex;flex-direction:column;gap:12px}
        .top-action-bar{display:flex;align-items:center;justify-content:space-between;gap:10px}
        .list-header{display:flex;align-items:center;gap:10px}
        .list-title{font-size:16px;font-weight:800;color:#111}
        .record-badge{display:inline-flex;align-items:center;background:#e8e8e8;border:1.5px solid #bbb;border-radius:999px;padding:2px 12px;font-size:12px;font-weight:700;color:#444}
        .btn-add{display:inline-flex;align-items:center;gap:6px;background:#d4d4d4;border:1.5px solid #888;border-radius:6px;padding:9px 18px;font-size:14px;font-weight:800;color:#111;cursor:pointer;transition:background .15s}
        .btn-add:hover{background:#c0c0c0}
        .btn-add .plus-icon{font-size:18px;font-weight:400;line-height:1}

        /* ── Flash ── */
        .flash{display:flex;align-items:center;gap:8px;padding:8px 14px;border-radius:4px;font-size:13px;font-weight:700}
        .flash-success{background:#e8f5e9;border:1px solid #81c784;color:#155724}
        .flash-error  {background:#fde8e8;border:1px solid #f5a5a5;color:#842029}
        .flash-info   {background:#e3f2fd;border:1px solid #90caf9;color:#0d47a1}

        /* ── Filter Banner ── */
        .filter-banner{display:flex;align-items:center;gap:8px;background:#d4d4d4;border:1px solid #bbb;border-radius:4px;padding:6px 14px;font-size:12px;font-weight:700;color:#555}
        .filter-banner strong{color:#222}

        /* ── Table Card ── */
        .table-card{background:#fff;border:1.5px solid #ccc;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)}
        .table-scroll{overflow-x:auto}
        .appointments-table{width:100%;border-collapse:collapse;min-width:860px}
        .appointments-table thead tr th{padding:11px 16px;text-align:left;font-size:11px;font-weight:800;color:#555;background:#f7f7f7;border-bottom:1.5px solid #e0e0e0;white-space:nowrap;letter-spacing:.04em;text-transform:uppercase}
        .appointments-table tbody tr td{padding:13px 16px;font-size:13px;color:#222;border-bottom:1px solid #efefef;background:#fff;white-space:nowrap;vertical-align:middle}
        .appointments-table tbody tr:last-child td{border-bottom:none}
        .appointments-table tbody tr:hover td{background:#f9f9f9}

        .appt-id{font-weight:800;color:#333;font-size:14px}
        .dt-date{font-weight:700;color:#111;font-size:13px}
        .dt-time{font-weight:500;color:#777;font-size:12px;margin-top:2px}
        .cust-name{font-weight:700;color:#111;font-size:13px}
        .cust-id{font-weight:500;color:#999;font-size:11px;margin-top:1px}

        /* ── Status badge ── */
        .badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:800;border:1px solid transparent}
        .badge::before{content:'';width:6px;height:6px;border-radius:50%;flex-shrink:0}
        .badge-scheduled{background:#dbeeff;color:#004085;border-color:#90c8ff}.badge-scheduled::before{background:#2196f3}
        .badge-confirmed{background:#ede9fe;color:#3b0764;border-color:#c4b5fd}.badge-confirmed::before{background:#7c3aed}
        .badge-no-show  {background:#fffbeb;color:#78350f;border-color:#fcd34d}.badge-no-show::before  {background:#f59e0b}
        .badge-cancelled{background:#fde8e8;color:#842029;border-color:#f5a5a5}.badge-cancelled::before{background:#e53935}

        /* ── Type pill ── */
        .type-pill{display:inline-block;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700}
        .type-Checkup    {background:#d1ecf1;color:#0c5460}
        .type-Vaccination{background:#d4edda;color:#155724}
        .type-Surgery    {background:#f8d7da;color:#842029}
        .type-Grooming   {background:#fde8d8;color:#7b3a0e}

        /* ── Action buttons ── */
        .action-btns{display:flex;gap:6px;align-items:center}
        .btn-view-action  {background:#fff;border:1.5px solid #bbb;border-radius:6px;padding:5px 14px;font-size:12px;font-weight:700;color:#333;cursor:pointer;font-family:'Figtree',sans-serif;transition:background .15s}
        .btn-view-action:hover{background:#f0f0f0;border-color:#888}
        .btn-edit-action  {background:#dbeafe;border:1.5px solid #93c5fd;border-radius:6px;padding:5px 14px;font-size:12px;font-weight:700;color:#1d4ed8;cursor:pointer;font-family:'Figtree',sans-serif;transition:background .15s}
        .btn-edit-action:hover{background:#bfdbfe}
        .btn-delete-action{background:#fee2e2;border:1.5px solid #fca5a5;border-radius:6px;padding:5px 14px;font-size:12px;font-weight:700;color:#991b1b;cursor:pointer;font-family:'Figtree',sans-serif;transition:background .15s}
        .btn-delete-action:hover{background:#fecaca;border-color:#f87171}
        .empty-row td{text-align:center;color:#aaa;font-style:italic;padding:50px 0;font-size:14px;background:#fff !important}

        /* ── Row removal animation ── */
        @keyframes rowFadeOut{from{opacity:1;transform:translateX(0)}to{opacity:0;transform:translateX(40px)}}
        .row-removing{animation:rowFadeOut .35s ease forwards;pointer-events:none}

        /* ════════════════════════════
           MODALS
        ════════════════════════════ */
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.48);z-index:1000;align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto}
        .modal-overlay.active{display:flex}
        .modal{background:#e8e8e8;border:2px solid #aaa;border-radius:8px;width:100%;max-width:700px;box-shadow:0 8px 32px rgba(0,0,0,.3);overflow:hidden;margin-bottom:40px;display:flex;flex-direction:column}
        .modal-sm{max-width:560px}
        .modal-header{background:#d8d8d8;border-bottom:1.5px solid #bbb;padding:13px 18px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
        .modal-header h2{display:flex;align-items:center;gap:8px;font-size:15px;font-weight:800;color:#111}
        .btn-close{background:none;border:none;cursor:pointer;font-size:20px;color:#555;line-height:1;padding:0 4px;border-radius:3px;transition:background .15s}
        .btn-close:hover{background:rgba(0,0,0,.1);color:#111}
        .modal-body{padding:16px 18px;display:flex;flex-direction:column;gap:10px;overflow-y:auto}
        .modal-footer{padding:10px 18px 14px;display:flex;justify-content:flex-end;gap:8px;flex-shrink:0;border-top:1.5px solid #ccc;background:#e0e0e0}

        /* ── Form elements ── */
        .form-section-label{font-size:11px;font-weight:800;color:#555;text-transform:uppercase;letter-spacing:.06em;border-bottom:1.5px solid #bbb;padding-bottom:4px;margin-bottom:-2px}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .form-input,.form-select,.form-textarea{width:100%;padding:9px 12px;background:#d4d4d4;border:1.5px solid #aaa;border-radius:4px;font-size:13px;font-family:'Figtree',sans-serif;font-weight:600;color:#111;outline:none;transition:border-color .15s}
        .form-input:focus,.form-select:focus,.form-textarea:focus{border-color:#666;background:#ccc}
        .form-input::placeholder,.form-textarea::placeholder{color:#666;font-weight:600}
        .form-select{cursor:pointer;appearance:auto}
        .form-textarea{resize:vertical;min-height:72px}
        .field-error{color:#c0392b;font-size:11px;font-weight:600;margin-top:2px;display:block}
        .btn-save{background:#2196f3;color:#fff;border:none;border-radius:5px;padding:9px 32px;font-size:14px;font-family:'Figtree',sans-serif;font-weight:700;cursor:pointer;transition:background .15s}
        .btn-save:hover{background:#1976d2}
        .btn-cancel-form{background:#888;color:#fff;border:none;border-radius:4px;padding:9px 24px;font-size:13px;font-family:'Figtree',sans-serif;font-weight:700;cursor:pointer;transition:background .15s}
        .btn-cancel-form:hover{background:#666}

        /* ── View modal — detail rows ── */
        .detail-row{display:flex;gap:8px;margin-bottom:12px;font-size:13px;line-height:1.5}
        .detail-label{font-weight:800;color:#111;min-width:150px;flex-shrink:0}
        .detail-value{color:#444}

        /* ── Lock notices ── */
        .lock-notice{display:flex;align-items:center;gap:8px;background:#f9f9f9;border:1.5px solid #ddd;border-radius:4px;padding:10px 14px;font-size:12px;font-weight:700;color:#777}
        .lock-notice-warn{display:flex;align-items:center;gap:8px;background:#fffbeb;border:1.5px solid #fcd34d;border-radius:4px;padding:10px 14px;font-size:12px;font-weight:700;color:#78350f}

        /* ── Timeline ── */
        .timeline-section{border-top:1.5px solid #eee;padding:16px 18px 4px}
        .timeline-section h4{font-size:11px;font-weight:800;color:#555;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px}
        .timeline{display:flex;flex-direction:column}
        .timeline-item{display:flex;align-items:flex-start;gap:12px;position:relative;padding-bottom:14px}
        .timeline-item:last-child{padding-bottom:0}
        .timeline-item:not(:last-child)::before{content:'';position:absolute;left:11px;top:22px;bottom:0;width:2px;background:#e0e0e0;z-index:0}
        .timeline-dot-wrap{position:relative;z-index:1;flex-shrink:0;padding-top:2px}
        .timeline-dot{width:22px;height:22px;border-radius:50%;border:2.5px solid var(--dot-color,#aaa);background:var(--dot-bg,#f5f5f5);display:flex;align-items:center;justify-content:center}
        .timeline-dot .dot-inner{width:8px;height:8px;border-radius:50%;background:var(--dot-color,#aaa)}
        .timeline-content{flex:1}
        .timeline-status-label{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:800;padding:2px 10px;border-radius:10px;border:1px solid transparent;margin-bottom:3px}
        .timeline-timestamp{font-size:11px;color:#888;font-weight:600}
        .timeline-timestamp span{color:#555;font-weight:700}
        .timeline-role-badge{display:inline-block;font-size:10px;font-weight:800;padding:1px 7px;border-radius:8px;background:#e8e8e8;border:1px solid #ccc;color:#555;vertical-align:middle;margin-left:2px}

        .tl-scheduled .timeline-dot{--dot-color:#2196f3;--dot-bg:#dbeeff}
        .tl-scheduled .timeline-status-label{background:#dbeeff;color:#004085;border-color:#90c8ff}
        .tl-confirmed .timeline-dot{--dot-color:#7c3aed;--dot-bg:#ede9fe}
        .tl-confirmed .timeline-status-label{background:#ede9fe;color:#3b0764;border-color:#c4b5fd}
        .tl-completed .timeline-dot{--dot-color:#27ae60;--dot-bg:#e8f5e9}
        .tl-completed .timeline-status-label{background:#e8f5e9;color:#155724;border-color:#81c784}
        .tl-no_show   .timeline-dot{--dot-color:#f59e0b;--dot-bg:#fffbeb}
        .tl-no_show   .timeline-status-label{background:#fffbeb;color:#78350f;border-color:#fcd34d}
        .tl-canceled  .timeline-dot{--dot-color:#e53935;--dot-bg:#fde8e8}
        .tl-canceled  .timeline-status-label{background:#fde8e8;color:#842029;border-color:#f5a5a5}

        .timeline-empty{font-size:12px;color:#aaa;font-style:italic}
        .modal-loading{padding:40px;text-align:center;color:#aaa;font-size:13px;font-weight:700}

        /* ── Status update dropdown ── */
        .status-update-section{border-top:1.5px solid #eee;padding:14px 18px 16px}
        .status-update-section>label{font-size:12px;font-weight:800;color:#333;display:block;margin-bottom:8px;text-transform:uppercase;letter-spacing:.04em}
        .status-row{display:flex;align-items:center;gap:8px}
        .status-select-modal{flex:1;font-size:13px;font-weight:700;font-family:'Figtree',sans-serif;padding:8px 12px;border:1.5px solid #ccc;border-radius:6px;background:#f8f8f8;color:#222;cursor:pointer;outline:none;transition:border-color .15s,background .15s;appearance:auto}
        .status-select-modal:focus{border-color:#2196f3;background:#fff}
        .status-select-modal:disabled{background:#eee;color:#aaa;cursor:not-allowed}
        .btn-save-status{font-size:12px;font-weight:800;padding:8px 20px;border-radius:6px;border:1.5px solid #81c784;background:#e8f5e9;color:#155724;cursor:pointer;transition:background .15s;white-space:nowrap;font-family:'Figtree',sans-serif}
        .btn-save-status:hover:not(:disabled){background:#c8e6c9}
        .btn-save-status:disabled{background:#eee;border-color:#ccc;color:#aaa;cursor:not-allowed}
        #saving-indicator{font-size:12px;font-weight:700;color:#2196f3;display:none}

        /* ── View modal footer btn ── */
        .btn-close-modal{background:#e0e0e0;color:#333;border:1.5px solid #bbb;border-radius:5px;padding:9px 24px;font-size:13px;font-family:'Figtree',sans-serif;font-weight:700;cursor:pointer;transition:background .15s}
        .btn-close-modal:hover{background:#ccc}

        /* ── Delete modal ── */
        .delete-modal-body{text-align:center;font-size:14px;color:#444;padding:28px 26px 10px}
        .btn-delete-confirm{background:#e53935;color:#fff;border:none;border-radius:4px;padding:8px 20px;font-size:13px;font-weight:700;cursor:pointer;font-family:'Figtree',sans-serif}
        .btn-delete-confirm:hover{background:#c62828}
    </style>
</head>
<body>

{{-- ══════ SIDEBAR ══════ --}}
<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="{{ asset('images/PVAS.png') }}" alt="PVAS Logo">
    </div>
    <div class="sidebar-nav">
        <a href="{{ route('receptionist.dashboard') }}"
           class="{{ request()->routeIs('receptionist.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('receptionist.customers.index') }}"
           class="{{ request()->routeIs('receptionist.customers.*') ? 'active' : '' }}">Customer</a>
        <a href="{{ route('receptionist.appointments.index') }}"
           class="{{ request()->routeIs('receptionist.appointments.*') ? 'active' : '' }}">Appointment</a>
        <a href="{{ route('receptionist.reports.index') }}"
           class="{{ request()->routeIs('receptionist.reports.*') ? 'active' : '' }}">Reports</a>
        <div class="sidebar-spacer"></div>
        <a href="{{ route('logout') }}" class="logout"
           onclick="event.preventDefault();document.getElementById('logout-form').submit();">Logout</a>
    </div>
    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none">@csrf</form>
</aside>

{{-- ══════ MAIN ══════ --}}
<main class="main">

    {{-- ── Page Header ── --}}
    <div class="page-header">
        <h1>Appointments</h1>

        @php
            $statusMap = [
                ''          => 'All',
                'scheduled' => 'Scheduled',
                'confirmed' => 'Confirmed',
                'no_show'   => 'No Show',
                'canceled'  => 'Cancelled',
            ];
            $currentStatus = $selectedStatus ?? '';
            $currentDate   = $selectedDate   ?? '';
        @endphp

        {{-- Status filter pills --}}
        <div class="status-filters">
            @foreach($statusMap as $key => $label)
                @php
                    $isActive = $currentStatus === $key;
                    $pillKey  = $key === '' ? 'all' : $key;
                    $href     = route('receptionist.appointments.index',
                                    array_filter(['status' => $key ?: null, 'date' => $currentDate ?: null]));
                @endphp
                <a href="{{ $href }}"
                   class="filter-pill {{ $isActive ? 'active-'.$pillKey : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Date picker --}}
        <form method="GET" action="{{ route('receptionist.appointments.index') }}" id="date-filter-form">
            @if($currentStatus)
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif
            <div class="date-filter-wrapper">
                <label for="date-picker">Date:</label>
                <input type="date" id="date-picker" name="date"
                       value="{{ $currentDate }}"
                       onchange="this.form.submit()">
                @if($currentDate)
                    <button type="button" class="date-clear-btn"
                            onclick="document.getElementById('date-picker').value='';document.getElementById('date-filter-form').submit();"
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

        {{-- Filter banner --}}
        @if($currentStatus || $currentDate)
            <div class="filter-banner">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filtered by:
                @if($currentStatus)
                    <strong>{{ $currentStatus === 'no_show' ? 'No Show' : ($currentStatus === 'canceled' ? 'Cancelled' : ucfirst($currentStatus)) }}</strong>
                @endif
                @if($currentDate)
                    @if($currentStatus) &nbsp;·&nbsp; @endif
                    <strong>{{ \Carbon\Carbon::parse($currentDate)->format('F d, Y') }}</strong>
                @endif
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
            <button class="btn-add" onclick="openModal('addModal')">
                <span class="plus-icon">&#43;</span> Add Appointment
            </button>
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
                                $badgeClass = match(strtolower($appt->status)) {
                                    'scheduled' => 'badge-scheduled',
                                    'confirmed' => 'badge-confirmed',
                                    'no_show'   => 'badge-no-show',
                                    'canceled','cancelled' => 'badge-cancelled',
                                    default     => 'badge-scheduled',
                                };
                                $statusLabel = match(strtolower($appt->status)) {
                                    'no_show'             => 'No Show',
                                    'canceled','cancelled' => 'Cancelled',
                                    default               => ucfirst($appt->status),
                                };
                            @endphp
                            <tr data-appt-id="{{ $appt->id }}">
                                <td><span class="appt-id">#{{ $appt->id }}</span></td>

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
                                    @if($appt->type)
                                        <span class="type-pill type-{{ $appt->type }}">{{ $appt->type }}</span>
                                    @else
                                        <span style="color:#ccc">—</span>
                                    @endif
                                </td>

                                <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis">
                                    {{ $appt->reason_for_visit ?? '—' }}
                                </td>

                                <td>
                                    <span class="badge {{ $badgeClass }}" id="badge-{{ $appt->id }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td>
                                    <div class="action-btns">
                                        <button class="btn-view-action"
                                                onclick="openDetail({{ $appt->id }})">View</button>

                                        <button class="btn-edit-action"
                                                onclick="openEditModal(
                                                    {{ $appt->id }},
                                                    {{ $appt->customer_id }},
                                                    '{{ addslashes($appt->pet?->pet_name ?? '') }}',
                                                    '{{ addslashes($appt->pet?->species ?? '') }}',
                                                    '{{ addslashes($appt->pet?->breed ?? '') }}',
                                                    '{{ addslashes($appt->pet?->gender ?? '') }}',
                                                    '{{ addslashes($appt->pet?->color ?? '') }}',
                                                    '{{ $appt->pet?->weight ?? '' }}',
                                                    {{ $appt->veterinarian_id }},
                                                    '{{ $appt->scheduled_date->format('Y-m-d') }}',
                                                    '{{ substr($appt->scheduled_time, 0, 5) }}',
                                                    '{{ addslashes($appt->type ?? '') }}',
                                                    '{{ addslashes($appt->reason_for_visit ?? '') }}'
                                                )">Edit</button>

                                        <button class="btn-delete-action"
                                                onclick="openDeleteModal({{ $appt->id }})">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-row" id="empty-row">
                                <td colspan="9">No appointments found{{ $currentStatus ? ' with status "' . ($currentStatus === 'no_show' ? 'No Show' : ($currentStatus === 'canceled' ? 'Cancelled' : ucfirst($currentStatus))) . '"' : '' }}{{ $currentDate ? ' on ' . \Carbon\Carbon::parse($currentDate)->format('M d, Y') : '' }}.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- /.page-body --}}
</main>

{{-- ══════════════════════════════
     VIEW / DETAILS MODAL
══════════════════════════════ --}}
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
        <div id="modal-content" style="overflow-y:auto;flex:1;">
            <div class="modal-loading">Loading…</div>
        </div>
        <div class="modal-footer">
            <button class="btn-close-modal" onclick="closeModal('detailModal')">Close</button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════
     ADD APPOINTMENT MODAL
══════════════════════════════ --}}
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header">
            <h2>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Appointment
            </h2>
            <button class="btn-close" onclick="closeModal('addModal')">&#10005;</button>
        </div>

        <form method="POST" action="{{ route('receptionist.appointments.store') }}" id="addForm">
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
                                  style="min-height:42px">{{ old('reason_for_visit') }}</textarea>
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

            </div>{{-- /.modal-body --}}
            <div class="modal-footer">
                <button type="button" class="btn-cancel-form" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn-save">Save Appointment</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════
     EDIT APPOINTMENT MODAL
══════════════════════════════ --}}
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h2>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                             m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Appointment
            </h2>
            <button class="btn-close" onclick="closeModal('editModal')">&#10005;</button>
        </div>

        <form method="POST" id="editForm" action="">
            @csrf @method('PUT')
            <div class="modal-body">

                <div class="form-section-label">Appointment Info</div>

                <div class="form-row">
                    <select name="customer_id" id="edit_customer_id" class="form-select" required>
                        <option value="">Select Customer</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->first_name }} {{ $c->last_name }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="scheduled_date" id="edit_scheduled_date" class="form-input" required>
                </div>

                <div class="form-row">
                    <input type="time" name="scheduled_time" id="edit_scheduled_time" class="form-input" required>
                    <select name="veterinarian_id" id="edit_veterinarian_id" class="form-select" required>
                        <option value="">Assigned Staff</option>
                        @foreach($staffList as $staff)
                            <option value="{{ $staff->id }}">
                                {{ $staff->name }} ({{ ucfirst(str_replace('_', ' ', $staff->role)) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <select name="type" id="edit_type" class="form-select">
                        <option value="">Appointment Type</option>
                        <option value="Checkup">Checkup</option>
                        <option value="Vaccination">Vaccination</option>
                        <option value="Surgery">Surgery</option>
                        <option value="Grooming">Grooming</option>
                    </select>
                    <textarea name="reason_for_visit" id="edit_notes" class="form-textarea"
                              placeholder="Notes / Reason for Visit" style="min-height:42px"></textarea>
                </div>

                <div class="form-section-label">Pet Info</div>

                <div class="form-row">
                    <input type="text" name="pet_name" id="edit_pet_name" class="form-input"
                           placeholder="Pet Name" required>
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

                <div class="form-row">
                    <select name="breed" id="edit_breed" class="form-select">
                        <option value="">Breed (select species first)</option>
                    </select>
                    <select name="gender" id="edit_gender" class="form-select">
                        <option value="">Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="form-row">
                    <input type="text"   name="color"  id="edit_color"  class="form-input" placeholder="Color">
                    <input type="number" name="weight" id="edit_weight" class="form-input"
                           placeholder="Weight (kg)" min="0" max="999.99" step="0.01">
                </div>

            </div>{{-- /.modal-body --}}
            <div class="modal-footer">
                <button type="button" class="btn-cancel-form" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════
     DELETE MODAL
══════════════════════════════ --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal modal-sm" style="max-width:400px">
        <div class="modal-header">
            <h2>Confirm Delete</h2>
            <button class="btn-close" onclick="closeModal('deleteModal')">&#10005;</button>
        </div>
        <div class="delete-modal-body">
            Are you sure you want to delete this appointment?<br>This action cannot be undone.
        </div>
        <div class="modal-footer" style="gap:10px">
            <button class="btn-cancel-form" onclick="closeModal('deleteModal')">Cancel</button>
            <form id="delete-form" method="POST" style="display:inline">
                @csrf @method('DELETE')
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
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) overlay.classList.remove('active');
    });
});

/* ════════════════════════════════════════════════════════════
   EDIT MODAL
════════════════════════════════════════════════════════════ */
function openEditModal(id, customerId, petName, species, breed, gender, color, weight,
                       staffId, date, time, type, notes) {
    document.getElementById('editForm').action            = `{{ url('receptionist/appointments') }}/${id}`;
    document.getElementById('edit_customer_id').value     = customerId;
    document.getElementById('edit_scheduled_date').value  = date;
    document.getElementById('edit_scheduled_time').value  = time;
    document.getElementById('edit_veterinarian_id').value = staffId;
    document.getElementById('edit_type').value            = type;
    document.getElementById('edit_notes').value           = notes;
    document.getElementById('edit_pet_name').value        = petName;
    document.getElementById('edit_species').value         = species;
    document.getElementById('edit_gender').value          = gender;
    document.getElementById('edit_color').value           = color;
    document.getElementById('edit_weight').value          = weight;
    updateBreeds('edit', breed);
    openModal('editModal');
}

/* ════════════════════════════════════════════════════════════
   DELETE MODAL
════════════════════════════════════════════════════════════ */
function openDeleteModal(id) {
    document.getElementById('delete-form').action =
        `{{ url('receptionist/appointments') }}/${id}`;
    openModal('deleteModal');
}

/* ════════════════════════════════════════════════════════════
   STATUS CONFIG  (mirrors admin view exactly)
════════════════════════════════════════════════════════════ */
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

/* Normalise "cancelled" → "canceled" so comparisons are consistent */
function normStatus(s) {
    if (!s) return s;
    const l = s.toLowerCase();
    return l === 'cancelled' ? 'canceled' : l;
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ════════════════════════════════════════════════════════════
   BUILD MODAL CONTENT  (same logic as admin buildModalContent)
════════════════════════════════════════════════════════════ */
function buildModalContent(data) {
    const currentNorm = normStatus(data.status);
    const isLocked    = currentNorm === 'completed' || currentNorm === 'canceled';
    const isConfirmed = currentNorm === 'confirmed';
    const isNoShow    = currentNorm === 'no_show';
    const history     = data.status_history || data.status_histories || [];

    /* ── Detail rows ── */
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

    /* ── Lock / warning notices ── */
    let lockNoticeHTML = '';
    if (isLocked) {
        lockNoticeHTML = `<div class="lock-notice">🔒 This appointment is <strong>${currentNorm === 'completed' ? 'completed' : 'cancelled'}</strong> and cannot be modified.</div>`;
    } else if (isConfirmed) {
        lockNoticeHTML = `<div class="lock-notice-warn">⚠ This appointment is <strong>confirmed</strong>. It can no longer be cancelled.</div>`;
    } else if (isNoShow) {
        lockNoticeHTML = `<div class="lock-notice-warn">⚠ This appointment is marked <strong>No Show</strong>. It can only be moved to <strong>Cancelled</strong>.</div>`;
    }

    /* ── Timeline ── */
    let timelineHTML = '';
    if (history.length > 0) {
        timelineHTML = history.map(item => {
            const norm = normStatus(item.status);
            const sc   = statusConfig[norm] || { label: item.status, dot: '#aaa', bg: '#f5f5f5', color: '#555', border: '#ccc' };
            const lbl  = item.status_label || sc.label;

            let dateDisplay = '—', timeDisplay = '';
            if (item.changed_at) {
                try {
                    const dt = new Date(item.changed_at);
                    if (!isNaN(dt)) {
                        dateDisplay = dt.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                        timeDisplay = dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                    } else {
                        dateDisplay = item.changed_at;
                    }
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

    /* ── Status dropdown ──
       Rules:
       - Used statuses disabled with ✗ prefix.
       - Current status disabled with ● prefix.
       - Cancelled disabled when appointment is confirmed.
       - Everything except Cancelled disabled when appointment is no_show.
       - Hidden entirely when appointment is locked.
    ── */
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
        const isUsed      = usedStatuses.has(s.val);
        const isCurrent   = s.val === currentNorm;
        // confirmed → canceled blocked
        const isBlocked   = (s.val === 'canceled' && isConfirmed)
                         // no_show → only canceled allowed; everything else blocked
                         || (isNoShow && s.val !== 'canceled');
        const disabled    = (isUsed || isCurrent || isBlocked) ? 'disabled' : '';
        const prefix      = isCurrent ? '● ' : (isUsed || isBlocked ? '✗ ' : '');
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

    return `<div class="modal-body">${detailHTML}${lockNoticeHTML}</div>
            <div class="timeline-section">
                <h4>Status History</h4>
                <div class="timeline">${timelineHTML}</div>
            </div>
            ${statusUpdateHTML}`;
}

/* Enable Save button only when a real option is chosen */
function onStatusDropdownChange(sel) {
    const btn = document.getElementById('btn-save-status');
    if (btn) btn.disabled = !sel.value;
}

/* ════════════════════════════════════════════════════════════
   OPEN DETAIL MODAL  (AJAX)
════════════════════════════════════════════════════════════ */
let activeId = null;

function openDetail(id) {
    activeId = id;
    document.getElementById('modal-content').innerHTML = '<div class="modal-loading">Loading…</div>';
    openModal('detailModal');

    fetch(`/receptionist/appointments/${id}`, {
        headers: {
            'Accept'          : 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN'    : document.querySelector('meta[name="csrf-token"]').content,
        },
    })
    .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
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
    const sel       = document.getElementById('modal-status-select');
    const saveBtn   = document.getElementById('btn-save-status');
    const saving    = document.getElementById('saving-indicator');
    const newStatus = sel?.value;
    if (!newStatus || !saveBtn) return;

    saveBtn.disabled     = true;
    saving.style.display = 'inline';

    fetch(`/receptionist/appointments/${activeId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type' : 'application/json',
            'Accept'       : 'application/json',
            'X-CSRF-TOKEN' : document.querySelector('meta[name="csrf-token"]').content,
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
                // Completed → animate row out, update count
                if (row) {
                    row.classList.add('row-removing');
                    row.addEventListener('animationend', () => {
                        row.remove();
                        updateRowCountBadge();
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
   RECORD COUNT
════════════════════════════════════════════════════════════ */
function updateRowCountBadge() {
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