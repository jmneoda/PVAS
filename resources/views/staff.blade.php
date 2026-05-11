<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PVAS - Staff</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ── Reset & Base ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Figtree', sans-serif;
            background: #d0d0d0;
            min-height: 100vh;
            display: flex;
        }

        /* ════════════════════════════════════
           SIDEBAR
        ════════════════════════════════════ */
        .sidebar {
            width: 180px;
            min-width: 180px;
            background: #FFFFFF;
            border-right: 2px solid #808080;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 0 16px;
        }

        .sidebar-logo {
            width: 200px;
            height: 100px;
            margin-bottom: 28px;
        }

        .sidebar-logo img {
            width: 200%;
            height: 100%;
            object-fit: contain;
        }

        .sidebar nav { width: 100%; display: flex; flex-direction: column; }

        .sidebar nav a {
            display: block;
            width: 100%;
            padding: 14px 0;
            text-align: center;
            font-size: 15px;
            font-weight: 600;
            color: #111;
            text-decoration: none;
            border-top: 1px solid #aaa;
            transition: background 0.15s;
        }

        .sidebar nav a:last-child  { border-bottom: 1px solid #aaa; }
        .sidebar nav a:hover       { background: #b0b0b0; }
        .sidebar nav a.active      { background: #b8b8b8; }
        .sidebar nav a.logout      { color: #cc0000; font-weight: 700; }

        .sidebar-spacer { flex: 1; }

        /* ════════════════════════════════════
           MAIN
        ════════════════════════════════════ */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #c8c8c8;
            min-height: 100vh;
        }

        /* ── Page Header ── */
        .page-header {
            background: #c8c8c8;
            border-bottom: 2px solid #999;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .page-header h1 {
            font-size: 20px;
            font-weight: 700;
            color: #111;
        }

        .date-picker-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .date-picker-wrapper label {
            font-size: 14px;
            font-weight: 600;
            color: #222;
        }

        .date-picker-wrapper input[type="month"] {
            border: 1px solid #888;
            background: #e0e0e0;
            padding: 4px 8px;
            font-size: 13px;
            border-radius: 3px;
            cursor: pointer;
        }

        /* ── Staff Body ── */
        .staff-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        /* ── Add Staff button row ── */
        .action-row {
            display: flex;
            justify-content: flex-end;
        }

        .btn-add-staff {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #FFFFFF;
            border: 1.5px solid #aaa;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 15px;
            font-weight: 700;
            color: #111;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            transition: background 0.15s;
        }

        .btn-add-staff:hover { background: #f0f0f0; }

        .btn-add-staff .plus-icon {
            font-size: 20px;
            line-height: 1;
            font-weight: 400;
        }

        /* ── Staff Table Card ── */
        .staff-card {
            background: #FFFFFF;
            border: 1px solid #aaa;
            border-radius: 4px;
            overflow: hidden;
        }

        /* Staff Role filter header */
        .staff-role-header {
            display: flex;
            align-items: center;
            gap: 0;
            background: #e0e0e0;
            border-bottom: 1px solid #bbb;
            padding: 0;
        }

        .role-filter-label {
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 700;
            color: #222;
            border-right: 1px solid #bbb;
            white-space: nowrap;
        }

        .role-filter-tabs {
            display: flex;
            align-items: center;
            overflow-x: auto;
            flex: 1;
        }

        .role-tab {
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 600;
            color: #444;
            text-decoration: none;
            border-right: 1px solid #ccc;
            white-space: nowrap;
            transition: background 0.12s;
        }

        .role-tab:hover  { background: #d4d4d4; }
        .role-tab.active { background: #c0c0c0; color: #111; }

        .role-tab .count {
            display: inline-block;
            margin-left: 5px;
            background: #aaa;
            color: #fff;
            border-radius: 8px;
            padding: 0 6px;
            font-size: 11px;
            font-weight: 700;
        }

        .role-tab.active .count { background: #666; }

        /* Staff Table */
        .staff-table {
            width: 100%;
            border-collapse: collapse;
        }

        .staff-table thead tr th {
            padding: 11px 16px;
            text-align: left;
            font-size: 14px;
            font-weight: 700;
            color: #222;
            border-bottom: 1px solid #ccc;
            background: #fafafa;
        }

        .staff-table tbody tr td {
            padding: 11px 16px;
            font-size: 13px;
            color: #333;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
        }

        .staff-table tbody tr:last-child td { border-bottom: none; }
        .staff-table tbody tr:hover { background: rgba(0,0,0,0.03); }

        .empty-row td {
            text-align: center;
            color: #888;
            font-style: italic;
            padding: 30px 0;
        }

        /* Role badge */
        .role-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
        }

        .role-badge.veterinarian  { background: #cce5ff; color: #004085; }
        .role-badge.receptionist  { background: #fff3cd; color: #856404; }
        .role-badge.vet_nurse     { background: #d4edda; color: #155724; }
        .role-badge.vet_assistant { background: #d1ecf1; color: #0c5460; }
        .role-badge.groomer       { background: #f8d7da; color: #721c24; }

        /* ── Action buttons — matches reports View / Delete style ── */
        .action-group { display: inline-flex; align-items: center; gap: 5px; }

        .tbl-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 800;
            border-radius: 4px;
            border: 1.5px solid;
            cursor: pointer;
            font-family: 'Figtree', sans-serif;
            transition: background 0.15s, transform 0.1s;
            white-space: nowrap;
            text-decoration: none;
        }

        .tbl-btn:active { transform: scale(0.96); }

        /* Edit — same blue as View */
        .tbl-btn.edit {
            background: #e3f2fd;
            color: #1565c0;
            border-color: #90caf9;
        }

        .tbl-btn.edit:hover { background: #bbdefb; color: #0d47a1; }

        /* Remove — same red as Delete */
        .tbl-btn.remove {
            background: #ffeaea;
            color: #c62828;
            border-color: #ef9a9a;
        }

        .tbl-btn.remove:hover { background: #ffcdd2; color: #b71c1c; }

        /* ── Flash messages ── */
        .flash {
            padding: 10px 16px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .flash.success { background: #d4edda; color: #155724; border-color: #c3e6cb; }
        .flash.error   { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }

        /* ════════════════════════════════════
           MODAL OVERLAY
        ════════════════════════════════════ */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.35);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.open {
            display: flex;
        }

        .modal {
            background: #FFFFFF;
            border-radius: 4px;
            width: 420px;
            max-width: 95vw;
            box-shadow: 0 4px 24px rgba(0,0,0,0.22);
            overflow: hidden;
        }

        /* Modal Header */
        .modal-header {
            background: #e0e0e0;
            border-bottom: 1px solid #bbb;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header .modal-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            font-weight: 700;
            color: #111;
        }

        .modal-header .modal-title svg {
            width: 18px;
            height: 18px;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            line-height: 1;
            color: #555;
            cursor: pointer;
            padding: 0 4px;
            transition: color 0.15s;
        }

        .modal-close:hover { color: #cc0000; }

        /* Modal Body */
        .modal-body {
            padding: 20px 24px;
        }

        .form-row {
            margin-bottom: 14px;
        }

        .form-row label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #222;
            margin-bottom: 4px;
        }

        .form-row .required-star { color: #cc0000; }

        .form-row input[type="text"],
        .form-row input[type="email"],
        .form-row input[type="password"],
        .form-row select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #aaa;
            border-radius: 3px;
            font-size: 13px;
            background: #f7f7f7;
            color: #111;
            outline: none;
            transition: border-color 0.15s;
        }

        .form-row input:focus,
        .form-row select:focus {
            border-color: #0055cc;
            background: #fff;
        }

        .form-row input[readonly] {
            background: #ebebeb;
            color: #666;
            cursor: default;
        }

        .form-row .field-error {
            color: #cc0000;
            font-size: 11px;
            margin-top: 3px;
        }

        .assign-role-select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23555' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 30px !important;
        }

        /* Modal Footer */
        .modal-footer {
            padding: 12px 24px 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-cancel {
            padding: 8px 18px;
            background: #e0e0e0;
            border: 1px solid #aaa;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-cancel:hover { background: #d0d0d0; }

        .btn-save {
            padding: 8px 24px;
            background: #1a73e8;
            border: none;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-save:hover { background: #1558b0; }

        /* Remove confirm modal */
        .confirm-modal {
            background: #FFFFFF;
            border-radius: 4px;
            width: 340px;
            max-width: 95vw;
            box-shadow: 0 4px 24px rgba(0,0,0,0.22);
            padding: 28px 24px 20px;
            text-align: center;
        }

        .confirm-modal p {
            font-size: 14px;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .confirm-modal strong { color: #111; }

        .confirm-btns {
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        .btn-danger {
            padding: 8px 22px;
            background: #cc0000;
            border: none;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
        }

        .btn-danger:hover { background: #a30000; }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .staff-table thead th:nth-child(3),
            .staff-table tbody td:nth-child(3) { display: none; }
        }

        @media (max-width: 640px) {
            .sidebar { width: 130px; min-width: 130px; }
        }
    </style>
</head>
<body>

{{-- ═══════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════ --}}
<aside class="sidebar">

    <div class="sidebar-logo">
        <img src="{{ asset('images/PVAS.png') }}" alt="PVAS Logo">
    </div>

    <nav>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ \Route::has('customers.index') ? route('customers.index') : '#' }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">Customers</a>
        <a href="{{ \Route::has('appointments.index') ? route('appointments.index') : '#' }}" class="{{ request()->routeIs('appointments.*') ? 'active' : '' }}">Appointment</a>
        <a href="{{ route('staff.index') }}" class="{{ request()->routeIs('staff.*') ? 'active' : '' }}">Staff</a>
        <a href="{{ \Route::has('reports.index') ? route('reports.index') : '#' }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">Reports</a>
    </nav>

    <div class="sidebar-spacer"></div>

    <nav>
        <a href="{{ route('logout') }}"
           class="logout"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            Logout
        </a>
    </nav>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">
        @csrf
    </form>

</aside>

{{-- ═══════════════════════════════════════
     MAIN CONTENT
═══════════════════════════════════════ --}}
<main class="main">

    {{-- Page Header --}}
    <div class="page-header">
        <h1>Staff</h1>
        <div class="date-picker-wrapper">
            <label for="month-filter">Date:</label>
            <input
                type="month"
                id="month-filter"
                name="month"
                value="{{ $selectedMonth ?? now()->format('Y-m') }}"
                onchange="this.form.submit()"
                form="month-filter-form"
            >
        </div>
    </div>

    <form id="month-filter-form" method="GET" action="{{ route('staff.index') }}">
        @if(($roleFilter ?? 'all') !== 'all')
            <input type="hidden" name="role_filter" value="{{ $roleFilter }}">
        @endif
    </form>

    <div class="staff-body">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="flash success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash error">{{ session('error') }}</div>
        @endif

        {{-- ── Add Staff Button ── --}}
        <div class="action-row">
            <button class="btn-add-staff" onclick="openAddModal()">
                <span class="plus-icon">&#43;</span>
                Add Staff
            </button>
        </div>

        {{-- ── Staff Table Card ── --}}
        <div class="staff-card">

            {{-- Staff Role filter tabs --}}
            <div class="staff-role-header">
                <span class="role-filter-label">Staff Role</span>
                <div class="role-filter-tabs">
                    <a href="{{ route('staff.index', array_merge(request()->only('month'), ['role_filter' => 'all'])) }}"
                       class="role-tab {{ ($roleFilter ?? 'all') === 'all' ? 'active' : '' }}">
                        All
                        <span class="count">{{ array_sum($roleCounts ?? []) }}</span>
                    </a>
                    @php
                        $tabs = [
                            'veterinarian'  => 'Veterinarian',
                            'receptionist'  => 'Receptionist',
                            'vet_nurse'     => 'Vet Nurse',
                            'vet_assistant' => 'Vet Assistant',
                            'groomer'       => 'Groomer',
                        ];
                    @endphp
                    @foreach($tabs as $roleKey => $roleLabel)
                        <a href="{{ route('staff.index', array_merge(request()->only('month'), ['role_filter' => $roleKey])) }}"
                           class="role-tab {{ ($roleFilter ?? 'all') === $roleKey ? 'active' : '' }}">
                            {{ $roleLabel }}
                            <span class="count">{{ $roleCounts[$roleKey] ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Table --}}
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Staff ID</th>
                        <th>Staff Name</th>
                        <th>Email</th>
                        <th>Contact Number</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffList ?? [] as $staff)
                        <tr>
                            <td>{{ $staff->id }}</td>
                            <td>{{ $staff->name }}</td>
                            <td>{{ $staff->email }}</td>
                            <td>{{ $staff->phone_number ?? '—' }}</td>
                            <td>
                                @php
                                    $roleKey   = $staff->role;
                                    $roleLabel = $tabs[$roleKey] ?? ucfirst($roleKey);
                                @endphp
                                <span class="role-badge {{ $roleKey }}">{{ $roleLabel }}</span>
                            </td>
                            <td>
                                <div class="action-group">
                                    {{-- Edit button --}}
                                    <button
                                        class="tbl-btn edit"
                                        onclick="openEditModal(
                                            {{ $staff->id }},
                                            '{{ addslashes($staff->name) }}',
                                            '{{ addslashes($staff->email) }}',
                                            '{{ addslashes($staff->phone_number ?? '') }}',
                                            '{{ $staff->role }}'
                                        )">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                        Edit
                                    </button>

                                    {{-- Remove button --}}
                                    <button
                                        class="tbl-btn remove"
                                        onclick="openRemoveConfirm({{ $staff->id }}, '{{ addslashes($staff->name) }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19 6l-1 14H6L5 6M10 11v6M14 11v6"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 6V4h6v2"/>
                                        </svg>
                                        Remove
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="6">No staff members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>{{-- /.staff-card --}}

    </div>{{-- /.staff-body --}}
</main>

{{-- ═══════════════════════════════════════
     ADD / EDIT STAFF MODAL
═══════════════════════════════════════ --}}
<div class="modal-overlay" id="staffModal">
    <div class="modal">

        <div class="modal-header">
            <div class="modal-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span id="modalTitle">Staff Details</span>
            </div>
            <button class="modal-close" onclick="closeModal()" title="Close">&times;</button>
        </div>

        <form id="staffForm" method="POST" action="{{ route('staff.store') }}">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="staff_id"  id="formStaffId" value="">

            <div class="modal-body">

                <div class="form-row">
                    <label>Staff ID:</label>
                    <input type="text" id="displayStaffId" readonly placeholder="Auto-generated">
                </div>

                <div class="form-row">
                    <label for="staffName">Name: <span class="required-star">*</span></label>
                    <input type="text" id="staffName" name="name" placeholder="Full name" required>
                    @error('name') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-row">
                    <label for="staffEmail">Email <span class="required-star">*</span></label>
                    <input type="email" id="staffEmail" name="email" placeholder="email@example.com" required>
                    @error('email') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-row">
                    <label for="staffPhone">Contact Number</label>
                    <input type="text" id="staffPhone" name="phone_number" placeholder="e.g. 09XXXXXXXXX">
                </div>

                <div class="form-row">
                    <label for="staffRole">Assign Role <span class="required-star">*</span></label>
                    <select id="staffRole" name="role" class="assign-role-select" required>
                        <option value="" disabled selected>Assign Role</option>
                        <option value="veterinarian">Veterinarian</option>
                        <option value="receptionist">Receptionist</option>
                        <option value="vet_nurse">Vet Nurse</option>
                        <option value="vet_assistant">Vet Assistant</option>
                        <option value="groomer">Groomer</option>
                    </select>
                    @error('role') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div id="passwordSection">
                    <div class="form-row">
                        <label for="staffPassword">Password <span class="required-star">*</span></label>
                        <input type="password" id="staffPassword" name="password" placeholder="Min. 8 characters">
                        @error('password') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-row">
                        <label for="staffPasswordConfirm">Confirm Password <span class="required-star">*</span></label>
                        <input type="password" id="staffPasswordConfirm" name="password_confirmation" placeholder="Re-enter password">
                    </div>
                </div>

            </div>{{-- /.modal-body --}}

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-save">Save</button>
            </div>
        </form>

    </div>
</div>

{{-- ═══════════════════════════════════════
     REMOVE CONFIRM MODAL
═══════════════════════════════════════ --}}
<div class="modal-overlay" id="removeModal">
    <div class="confirm-modal">
        <p>Remove <strong id="removeStaffName"></strong> from the staff list?<br>This action cannot be undone.</p>
        <div class="confirm-btns">
            <button class="btn-cancel" onclick="closeRemoveModal()">Cancel</button>
            <form id="removeForm" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">Remove</button>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════ --}}
<script>
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add Staff';
        document.getElementById('staffForm').action = "{{ route('staff.store') }}";
        document.getElementById('formMethod').value  = 'POST';
        document.getElementById('formStaffId').value = '';
        document.getElementById('displayStaffId').value = 'Auto-generated';
        document.getElementById('staffName').value    = '';
        document.getElementById('staffEmail').value   = '';
        document.getElementById('staffPhone').value   = '';
        document.getElementById('staffRole').value    = '';
        document.getElementById('staffPassword').value          = '';
        document.getElementById('staffPasswordConfirm').value   = '';
        document.getElementById('passwordSection').style.display = 'block';
        document.getElementById('staffPassword').required          = true;
        document.getElementById('staffPasswordConfirm').required   = true;
        document.getElementById('staffModal').classList.add('open');
    }

    function openEditModal(id, name, email, phone, role) {
        document.getElementById('modalTitle').textContent = 'Edit Staff';
        const updateUrl = "{{ url('staff') }}/" + id;
        document.getElementById('staffForm').action = updateUrl;
        document.getElementById('formMethod').value  = 'PUT';
        document.getElementById('formStaffId').value = id;
        document.getElementById('displayStaffId').value = '#' + id;
        document.getElementById('staffName').value      = name;
        document.getElementById('staffEmail').value     = email;
        document.getElementById('staffPhone').value     = phone;
        document.getElementById('staffRole').value      = role;
        document.getElementById('passwordSection').style.display = 'none';
        document.getElementById('staffPassword').required          = false;
        document.getElementById('staffPasswordConfirm').required   = false;
        document.getElementById('staffModal').classList.add('open');
    }

    function closeModal() {
        document.getElementById('staffModal').classList.remove('open');
    }

    function openRemoveConfirm(id, name) {
        document.getElementById('removeStaffName').textContent = name;
        const removeUrl = "{{ url('staff') }}/" + id;
        document.getElementById('removeForm').action = removeUrl;
        document.getElementById('removeModal').classList.add('open');
    }

    function closeRemoveModal() {
        document.getElementById('removeModal').classList.remove('open');
    }

    document.getElementById('staffModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    document.getElementById('removeModal').addEventListener('click', function(e) {
        if (e.target === this) closeRemoveModal();
    });

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            openAddModal();
        });
    @endif

    setTimeout(function () {
        const flash = document.querySelector('.flash');
        if (flash) flash.style.display = 'none';
    }, 4000);
</script>

</body>
</html>