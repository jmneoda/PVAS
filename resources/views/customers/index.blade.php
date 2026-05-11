{{-- resources/views/customers/index.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PVAS – Customers</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Figtree', sans-serif;
            background: #a0a0a0;
            min-height: 100vh;
            display: flex;
        }

        /* ════════════════════════════
           SIDEBAR
        ════════════════════════════ */
        .sidebar {
            width: 160px;
            min-width: 160px;
            background: #ffffff;
            border-right: 2px solid #888;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0 0;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .sidebar-logo {
            width: 130px;
            height: 130px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-logo img { width: 100%; height: 100%; object-fit: contain; }

        .sidebar-nav {
            width: 100%;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .sidebar-nav a {
            display: block;
            width: 100%;
            padding: 15px 0;
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            color: #111;
            text-decoration: none;
            border-top: 1.5px solid #bbb;
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
           MAIN CONTENT
        ════════════════════════════ */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #a8a8a8;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── Page Header ── */
        .page-header {
            background: #b8b8b8;
            border-bottom: 2px solid #888;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 52px;
        }

        .page-header h1 {
            font-size: 18px;
            font-weight: 800;
            color: #111;
            letter-spacing: 0.3px;
        }

        /* Date filter — matches receptionist exactly */
        .filter-wrapper {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #d0d0d0;
            border: 1px solid #999;
            border-radius: 3px;
            padding: 3px 8px;
        }

        .filter-wrapper label {
            font-size: 13px;
            font-weight: 700;
            color: #222;
        }

        .filter-wrapper select {
            border: none;
            background: transparent;
            padding: 2px 4px;
            font-size: 13px;
            font-family: 'Figtree', sans-serif;
            font-weight: 600;
            cursor: pointer;
            color: #111;
            outline: none;
        }

        /* ════════════════════════════
           PAGE BODY
        ════════════════════════════ */
        .page-body {
            flex: 1;
            padding: 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        /* ── Flash ── */
        .flash-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 600;
        }

        .flash-error {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
            border-radius: 4px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 600;
        }

        /* ── Top Bar ── */
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-box {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #d4d4d4;
            border: 1.5px solid #999;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 15px;
            font-weight: 800;
            color: #111;
        }

        .stat-box .stat-number {
            font-size: 22px;
            font-weight: 800;
            color: #111;
        }

        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #d4d4d4;
            border: 1.5px solid #888;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 15px;
            font-weight: 800;
            color: #111;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s;
        }

        .btn-add:hover { background: #c0c0c0; }
        .btn-add .plus-icon { font-size: 20px; font-weight: 400; line-height: 1; }

        /* ── Search Row ── */
        .search-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #ffffff;
            border: 1.5px solid #999;
            border-radius: 4px;
            padding: 8px 14px;
            width: 100%;
            max-width: 460px;
        }

        .search-wrapper svg { flex-shrink: 0; color: #666; }

        .search-wrapper input {
            border: none;
            outline: none;
            background: transparent;
            font-size: 15px;
            font-family: 'Figtree', sans-serif;
            font-weight: 700;
            color: #111;
            width: 100%;
        }

        .search-wrapper input::placeholder { color: #777; font-weight: 700; }

        .btn-clear {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #d4d4d4;
            border: 1.5px solid #999;
            border-radius: 4px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            color: #555;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s;
        }

        .btn-clear:hover { background: #c4c4c4; }

        /* ── Table Card ── */
        .table-card {
            background: #d0d0d0;
            border: 1.5px solid #999;
            border-radius: 4px;
            overflow: hidden;
        }

        .customers-table {
            width: 100%;
            border-collapse: collapse;
        }

        .customers-table thead tr th {
            padding: 11px 16px;
            text-align: left;
            font-size: 13px;
            font-weight: 800;
            color: #222;
            background: #c8c8c8;
            border-bottom: 2px solid #aaa;
            white-space: nowrap;
        }

        .customers-table thead th a {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: #222;
            text-decoration: none;
            font-weight: 800;
        }

        .customers-table thead th a:hover { color: #1a6bbf; }

        .sort-icon { font-size: 10px; opacity: 0.5; }
        .sort-icon.active { opacity: 1; color: #1a6bbf; }

        .customers-table tbody tr td {
            padding: 10px 16px;
            font-size: 13px;
            color: #222;
            border-bottom: 1px solid #bbb;
            background: #d8d8d8;
            white-space: nowrap;
        }

        .customers-table tbody tr:last-child td { border-bottom: none; }
        .customers-table tbody tr:hover td     { background: #cccccc; }

        .name-cell { font-weight: 700; color: #111; }

        .id-chip {
            display: inline-block;
            background: #c8c8c8;
            border: 1px solid #aaa;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 800;
            color: #555;
            padding: 1px 6px;
        }

        /* ── Action word buttons ── */
        .action-btns { display: flex; gap: 6px; align-items: center; }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            padding: 5px 14px;
            border-radius: 3px;
            font-size: 12px;
            font-family: 'Figtree', sans-serif;
            font-weight: 700;
            letter-spacing: 0.3px;
            transition: opacity 0.15s, transform 0.1s;
            line-height: 1.4;
        }

        .btn-action:active { transform: scale(0.96); }
        .btn-edit   { background: #1a6bbf; color: #ffffff; }
        .btn-edit:hover   { opacity: 0.85; }
        .btn-delete { background: #c0392b; color: #ffffff; }
        .btn-delete:hover { opacity: 0.85; }

        /* ── Empty state ── */
        .empty-row td {
            text-align: center;
            color: #777;
            font-style: italic;
            padding: 50px 0;
            font-size: 14px;
            background: #d8d8d8 !important;
        }

        /* ── Pagination ── */
        .pagination-wrap {
            padding: 12px 16px;
            background: #cccccc;
            border-top: 1.5px solid #aaa;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .pagination-info {
            font-size: 12px;
            font-weight: 700;
            color: #555;
        }

        .pagination-links { display: flex; gap: 4px; }

        .pagination-links a,
        .pagination-links span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            border: 1.5px solid #aaa;
            color: #444;
            background: #d4d4d4;
            transition: background 0.12s;
        }

        .pagination-links a:hover       { background: #c4c4c4; }
        .pagination-links span.current  { background: #1a6bbf; border-color: #145299; color: #fff; }
        .pagination-links span.disabled { opacity: 0.4; pointer-events: none; }

        /* ════════════════════════════
           MODAL OVERLAY
        ════════════════════════════ */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 1000;
            align-items: flex-start;
            justify-content: center;
            padding-top: 60px;
            overflow-y: auto;
        }

        .modal-overlay.active { display: flex; }

        .modal {
            background: #e8e8e8;
            border: 2px solid #aaa;
            border-radius: 6px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            overflow: hidden;
            margin-bottom: 40px;
        }

        .modal-header {
            background: #d8d8d8;
            border-bottom: 1.5px solid #bbb;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h2 { font-size: 15px; font-weight: 800; color: #111; }

        .btn-close {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color: #555;
            line-height: 1;
            padding: 0 4px;
            border-radius: 3px;
            transition: background 0.15s;
        }

        .btn-close:hover { background: rgba(0,0,0,0.1); color: #111; }

        .modal-body {
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .form-group { display: flex; flex-direction: column; gap: 5px; }

        .form-group label {
            font-size: 12px;
            font-weight: 800;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .required-star { color: #c0392b; }

        .form-input {
            width: 100%;
            padding: 9px 12px;
            background: #d4d4d4;
            border: 1.5px solid #aaa;
            border-radius: 4px;
            font-size: 13px;
            font-family: 'Figtree', sans-serif;
            font-weight: 600;
            color: #111;
            outline: none;
            transition: border-color 0.15s;
        }

        .form-input::placeholder { color: #777; font-weight: 600; }
        .form-input:focus        { border-color: #666; background: #ccc; }

        .field-error { color: #c0392b; font-size: 11px; font-weight: 700; }

        .delete-warning {
            background: #f8d7da;
            border: 1.5px solid #f5c2c7;
            border-radius: 4px;
            padding: 14px;
            font-size: 13px;
            font-weight: 600;
            color: #842029;
            line-height: 1.6;
        }

        .modal-footer {
            padding: 12px 16px 16px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            border-top: 1.5px solid #ccc;
        }

        .btn-cancel {
            background: #d4d4d4;
            border: 1.5px solid #aaa;
            border-radius: 4px;
            padding: 8px 20px;
            font-size: 13px;
            font-family: 'Figtree', sans-serif;
            font-weight: 700;
            color: #444;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-cancel:hover { background: #c4c4c4; }

        .btn-save {
            background: #2196f3;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 8px 24px;
            font-size: 13px;
            font-family: 'Figtree', sans-serif;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-save:hover { background: #1976d2; }

        .btn-confirm-delete {
            background: #c0392b;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 8px 24px;
            font-size: 13px;
            font-family: 'Figtree', sans-serif;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-confirm-delete:hover { background: #a93226; }

        @media (max-width: 860px) {
            .form-row { grid-template-columns: 1fr; }
            .table-card { overflow-x: auto; }
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

    <div class="sidebar-nav">
        <a href="{{ route('dashboard') }}"
           class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>

        <a href="{{ \Route::has('customers.index') ? route('customers.index') : '#' }}"
           class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">Customers</a>

        <a href="{{ \Route::has('appointments.index') ? route('appointments.index') : '#' }}"
           class="{{ request()->routeIs('appointments.*') ? 'active' : '' }}">Appointment</a>

        <a href="{{ \Route::has('staff.index') ? route('staff.index') : '#' }}"
           class="{{ request()->routeIs('staff.*') ? 'active' : '' }}">Staff</a>

        <a href="{{ route('reports.index') }}"
           class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">Reports</a>

        <div class="sidebar-spacer"></div>

        <a href="{{ route('logout') }}"
           class="logout"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            Logout
        </a>
    </div>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>
</aside>

{{-- ══════════════════════════════
     MAIN CONTENT
══════════════════════════════ --}}
<main class="main">

    {{-- Page Header --}}
    <div class="page-header">
        <h1>Customers</h1>

        {{-- Date period filter — same pattern as receptionist --}}
        <div class="filter-wrapper">
            <label for="period-filter">Date:</label>
            <select id="period-filter" name="period" onchange="applyFilter(this.value)">
                <option value="all"        {{ ($selectedPeriod ?? 'all') === 'all'        ? 'selected' : '' }}>All</option>
                <option value="today"      {{ ($selectedPeriod ?? '') === 'today'      ? 'selected' : '' }}>Today</option>
                <option value="yesterday"  {{ ($selectedPeriod ?? '') === 'yesterday'  ? 'selected' : '' }}>Yesterday</option>
                <option value="this_week"  {{ ($selectedPeriod ?? '') === 'this_week'  ? 'selected' : '' }}>This Week</option>
                <option value="this_month" {{ ($selectedPeriod ?? '') === 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="this_year"  {{ ($selectedPeriod ?? '') === 'this_year'  ? 'selected' : '' }}>This Year</option>
            </select>
        </div>
    </div>

    {{-- Hidden form for period GET filter — same pattern as receptionist --}}
    <form id="period-filter-form" method="GET" action="{{ route('customers.index') }}">
        <input type="hidden" id="period-hidden" name="period" value="{{ $selectedPeriod ?? 'all' }}">
        <input type="hidden" name="sort"   value="{{ $sort }}">
        <input type="hidden" name="dir"    value="{{ $dir }}">
        @if($search ?? null)
            <input type="hidden" name="search" value="{{ $search }}">
        @endif
    </form>

    <div class="page-body">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif

        @if($errors->any() && !session('modal'))
            <div class="flash-error">Please fix the errors below.</div>
        @endif

        {{-- Top bar: stat + add button --}}
        <div class="top-bar">
            <div class="stat-box">
                Total Customers &nbsp;<span class="stat-number">{{ $totalCustomers }}</span>
            </div>

            <button class="btn-add" onclick="openModal('addModal')">
                <span class="plus-icon">&#43;</span>
                Add Customer
            </button>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('customers.index') }}" id="search-form">
            <input type="hidden" name="sort"   value="{{ $sort }}">
            <input type="hidden" name="dir"    value="{{ $dir }}">
            <input type="hidden" name="period" value="{{ $selectedPeriod ?? 'all' }}">
            <div class="search-row">
                <div class="search-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text"
                           name="search"
                           value="{{ $search ?? '' }}"
                           placeholder="Search by name, email, contact…"
                           autocomplete="off"
                           onchange="this.form.submit()">
                </div>
                @if($search)
                    <a href="{{ route('customers.index', ['sort' => $sort, 'dir' => $dir, 'period' => $selectedPeriod ?? 'all']) }}"
                       class="btn-clear">&#10005; Clear</a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="table-card">
            @if($customers->count())
                <table class="customers-table">
                    <thead>
                        <tr>
                            @php
                                $sortUrl = fn($col) => route('customers.index', array_merge(
                                    request()->only('search', 'period'),
                                    ['sort' => $col, 'dir' => ($sort === $col && $dir === 'asc') ? 'desc' : 'asc']
                                ));
                                $sortIcon = fn($col) =>
                                    '<span class="sort-icon' . ($sort === $col ? ' active' : '') . '">'
                                    . ($sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '⇅')
                                    . '</span>';
                            @endphp
                            <th>ID</th>
                            <th><a href="{{ $sortUrl('first_name') }}">Name {!! $sortIcon('first_name') !!}</a></th>
                            <th><a href="{{ $sortUrl('email') }}">Email {!! $sortIcon('email') !!}</a></th>
                            <th><a href="{{ $sortUrl('contact_number') }}">Contact {!! $sortIcon('contact_number') !!}</a></th>
                            <th>Address</th>
                            <th><a href="{{ $sortUrl('created_at') }}">Registered {!! $sortIcon('created_at') !!}</a></th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td><span class="id-chip">#{{ $customer->id }}</span></td>
                                <td class="name-cell">{{ $customer->full_name }}</td>
                                <td>{{ $customer->email ?: '—' }}</td>
                                <td>{{ $customer->contact_number ?: '—' }}</td>
                                <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $customer->address ?: '—' }}
                                </td>
                                <td>{{ $customer->created_at?->format('M d, Y') ?? '—' }}</td>
                                <td>
                                    <div class="action-btns">
                                        <button class="btn-action btn-edit"
                                                onclick="openEditModal({{ $customer->id }}, {{ json_encode($customer) }})">
                                            Edit
                                        </button>
                                        <button class="btn-action btn-delete"
                                                onclick="openDeleteModal({{ $customer->id }}, '{{ addslashes($customer->full_name) }}')">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Pagination --}}
                @if($customers->hasPages())
                    <div class="pagination-wrap">
                        <span class="pagination-info">
                            Showing {{ $customers->firstItem() }}–{{ $customers->lastItem() }} of {{ $customers->total() }}
                        </span>
                        <div class="pagination-links">
                            {{-- Previous --}}
                            @if($customers->onFirstPage())
                                <span class="disabled">‹</span>
                            @else
                                <a href="{{ $customers->previousPageUrl() }}">‹</a>
                            @endif

                            {{-- Page numbers (window of ±2) --}}
                            @foreach($customers->getUrlRange(
                                max(1, $customers->currentPage() - 2),
                                min($customers->lastPage(), $customers->currentPage() + 2)
                            ) as $page => $url)
                                @if($page == $customers->currentPage())
                                    <span class="current">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}">{{ $page }}</a>
                                @endif
                            @endforeach

                            {{-- Next --}}
                            @if($customers->hasMorePages())
                                <a href="{{ $customers->nextPageUrl() }}">›</a>
                            @else
                                <span class="disabled">›</span>
                            @endif
                        </div>
                    </div>
                @endif

            @else
                {{-- Empty state --}}
                <table class="customers-table">
                    <thead>
                        <tr>
                            <th>ID</th><th>Name</th><th>Email</th><th>Contact</th>
                            <th>Address</th><th>Registered</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="empty-row">
                            <td colspan="7">
                                {{ $search
                                    ? 'No customers found matching "' . $search . '".'
                                    : 'No customers registered yet.' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>

    </div>{{-- /.page-body --}}
</main>

{{-- ══════════════════════════════
     ADD CUSTOMER MODAL
══════════════════════════════ --}}
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Add New Customer</h2>
            <button class="btn-close" onclick="closeModal('addModal')">&#10005;</button>
        </div>

        <form method="POST" action="{{ route('customers.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name <span class="required-star">*</span></label>
                        <input type="text"
                               name="first_name"
                               class="form-input"
                               placeholder="Juan"
                               value="{{ old('first_name') }}"
                               required>
                        @error('first_name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Last Name <span class="required-star">*</span></label>
                        <input type="text"
                               name="last_name"
                               class="form-input"
                               placeholder="Dela Cruz"
                               value="{{ old('last_name') }}"
                               required>
                        @error('last_name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email"
                           name="email"
                           class="form-input"
                           placeholder="juan@email.com"
                           value="{{ old('email') }}">
                    @error('email')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text"
                           name="contact_number"
                           class="form-input"
                           placeholder="09XX-XXX-XXXX"
                           value="{{ old('contact_number') }}">
                    @error('contact_number')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address"
                              class="form-input"
                              rows="2"
                              placeholder="House No., Street, Barangay, City…">{{ old('address') }}</textarea>
                    @error('address')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn-save">Save Customer</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════
     EDIT CUSTOMER MODAL
══════════════════════════════ --}}
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit Customer</h2>
            <button class="btn-close" onclick="closeModal('editModal')">&#10005;</button>
        </div>

        <form method="POST" id="edit-form" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name <span class="required-star">*</span></label>
                        <input type="text"
                               name="first_name"
                               id="edit-first-name"
                               class="form-input"
                               required>
                    </div>
                    <div class="form-group">
                        <label>Last Name <span class="required-star">*</span></label>
                        <input type="text"
                               name="last_name"
                               id="edit-last-name"
                               class="form-input"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email"
                           name="email"
                           id="edit-email"
                           class="form-input">
                </div>

                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text"
                           name="contact_number"
                           id="edit-contact-number"
                           class="form-input">
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address"
                              id="edit-address"
                              class="form-input"
                              rows="2"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-save">Update Customer</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════
     DELETE CONFIRM MODAL
══════════════════════════════ --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal" style="max-width:400px;">
        <div class="modal-header">
            <h2>Confirm Delete</h2>
            <button class="btn-close" onclick="closeModal('deleteModal')">&#10005;</button>
        </div>

        <form method="POST" id="delete-form" action="">
            @csrf
            @method('DELETE')
            <div class="modal-body">
                <div class="delete-warning" id="delete-warning-text"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('deleteModal')">Cancel</button>
                <button type="submit" class="btn-confirm-delete">Yes, Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
    /* ── Period filter — same pattern as receptionist ── */
    function applyFilter(value) {
        document.getElementById('period-hidden').value = value;
        document.getElementById('period-filter-form').submit();
    }

    /* ── Modal helpers ── */
    function openModal(id)  { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function (e) {
            if (e.target === this) closeModal(this.id);
        });
    });

    /* ── Edit modal ── */
    function openEditModal(id, data) {
        document.getElementById('edit-form').action          = `/customers/${id}`;
        document.getElementById('edit-first-name').value     = data.first_name     ?? '';
        document.getElementById('edit-last-name').value      = data.last_name      ?? '';
        document.getElementById('edit-email').value          = data.email          ?? '';
        document.getElementById('edit-contact-number').value = data.contact_number ?? '';
        document.getElementById('edit-address').value        = data.address        ?? '';
        openModal('editModal');
    }

    /* ── Delete modal ── */
    function openDeleteModal(id, name) {
        document.getElementById('delete-form').action = `/customers/${id}`;
        document.getElementById('delete-warning-text').innerHTML =
            `You are about to permanently delete the customer record for <strong>${name}</strong>.<br><br>
             This will also remove linked pets and appointment references. This action <strong>cannot be undone</strong>.`;
        openModal('deleteModal');
    }

    /* ── Re-open add modal on validation error ── */
    @if($errors->any())
        openModal('addModal');
    @endif
</script>

</body>
</html>