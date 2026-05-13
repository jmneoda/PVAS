{{-- resources/views/receptionist/customers/index.blade.php --}}
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
        }

        .sidebar-logo {
            width: 130px;
            height: 130px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

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

        .header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-header h1 {
            font-size: 18px;
            font-weight: 800;
            color: #111;
            letter-spacing: 0.3px;
        }

        /* Date filter */
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

        /* ── Top Bar (stat + add button) ── */
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

        /* Add Customer Button */
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

        .btn-add .plus-icon {
            font-size: 20px;
            font-weight: 400;
            line-height: 1;
        }

        /* ── Search Bar ── */
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

        .search-wrapper svg {
            flex-shrink: 0;
            color: #666;
        }

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

        /* ── Table Card ── */
        .table-card {
            background: #fff;
            border: 1.5px solid #ccc;
            border-radius: 6px;
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

        .customers-table tbody tr td {
            padding: 10px 14px;
            font-size: 13px;
            color: #333;
            border-bottom: 1px solid #eee;
            white-space: nowrap;
            vertical-align: middle;
        }

        .customers-table tbody tr:last-child td { border-bottom: none; }
        .customers-table tbody tr:hover td { background: #f5f5f5; }

        /* ── Action word buttons ── */
        .action-btns {
            display: flex;
            gap: 6px;
            align-items: center;
        }

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
            text-decoration: none;
            line-height: 1.4;
        }

        .btn-action:active { transform: scale(0.96); }

        .btn-edit {
            background: #1a6bbf;
            color: #ffffff;
        }

        .btn-edit:hover { opacity: 0.85; }

        .btn-delete {
            background: #c0392b;
            color: #ffffff;
        }

        .btn-delete:hover { opacity: 0.85; }

        .empty-row td {
            text-align: center;
            color: #777;
            font-style: italic;
            padding: 40px 0;
            font-size: 14px;
            background: #d8d8d8 !important;
        }

        /* ════════════════════════════
           MODAL OVERLAY
        ════════════════════════════ */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active { display: flex; }

        .modal {
            background: #e8e8e8;
            border: 2px solid #aaa;
            border-radius: 6px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        /* Modal Header */
        .modal-header {
            background: #d8d8d8;
            border-bottom: 1.5px solid #bbb;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h2 {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            font-weight: 800;
            color: #111;
        }

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

        /* Modal Body */
        .modal-body {
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .form-input {
            width: 100%;
            padding: 11px 14px;
            background: #d4d4d4;
            border: 1.5px solid #aaa;
            border-radius: 4px;
            font-size: 14px;
            font-family: 'Figtree', sans-serif;
            font-weight: 600;
            color: #111;
            outline: none;
            transition: border-color 0.15s;
        }

        .form-input::placeholder { color: #666; font-weight: 600; }
        .form-input:focus        { border-color: #666; background: #ccc; }

        /* Validation error */
        .field-error {
            color: #c0392b;
            font-size: 12px;
            font-weight: 600;
            margin-top: -6px;
        }

        /* Modal Footer */
        .modal-footer {
            padding: 12px 16px 16px;
            display: flex;
            justify-content: flex-end;
        }

        .btn-save {
            background: #2196f3;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 10px 28px;
            font-size: 14px;
            font-family: 'Figtree', sans-serif;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-save:hover { background: #1976d2; }

        /* Flash message */
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
        <a href="{{ route('receptionist.dashboard') }}"
           class="{{ request()->routeIs('receptionist.dashboard') ? 'active' : '' }}">
            Dashboard
        </a>

        <a href="{{ route('receptionist.customers.index') }}"
           class="{{ request()->routeIs('receptionist.customers.*') ? 'active' : '' }}">Customer</a>

        <a href="{{ \Route::has('receptionist.appointments.index') ? route('receptionist.appointments.index') : '#' }}"
           class="{{ request()->routeIs('receptionist.appointments.*') ? 'active' : '' }}">Appointment</a>

        <a href="{{ route('receptionist.reports.index') }}"
           class="{{ request()->routeIs('receptionist.reports.*') ? 'active' : '' }}">Reports</a>

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
        <div class="header-left">
            <h1>Customer</h1>
        </div>

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

    {{-- Hidden form for GET filter --}}
    <form id="period-filter-form" method="GET" action="{{ route('receptionist.customers.index') }}">
        <input type="hidden" id="period-hidden" name="period" value="{{ $selectedPeriod ?? 'all' }}">
        @if($search ?? null)
            <input type="hidden" name="search" value="{{ $search }}">
        @endif
    </form>

    <div class="page-body">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="flash-error">
                @foreach($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        {{-- Top bar: stat + add button --}}
        <div class="top-bar">
            <div class="stat-box">
                Total Customer &nbsp;<span class="stat-number">{{ $totalCustomers }}</span>
            </div>

            <button class="btn-add" onclick="openAddModal()">
                <span class="plus-icon">&#43;</span>
                Add Customer
            </button>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('receptionist.customers.index') }}">
            @if($selectedPeriod ?? null)
                <input type="hidden" name="period" value="{{ $selectedPeriod }}">
            @endif
            <div class="search-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2.5">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text"
                       name="search"
                       value="{{ $search ?? '' }}"
                       placeholder="Search Name"
                       autocomplete="off"
                       onchange="this.form.submit()">
            </div>
        </form>

        {{-- Table --}}
        <div class="table-card">
            <table class="customers-table">
                <thead>
                    <tr>
                        <th>Customer_ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact_Number</th>
                        <th>Address</th>
                        <th>Registered</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>{{ $customer->id }}</td>
                            <td>{{ $customer->first_name }} {{ $customer->last_name }}</td>
                            <td>{{ $customer->email ?? '—' }}</td>
                            <td>{{ $customer->contact_number }}</td>
                            <td>{{ $customer->address ?? '—' }}</td>
                            <td>{{ $customer->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="action-btns">
                                    {{-- Edit --}}
                                    <button class="btn-action btn-edit"
                                            title="Edit Customer"
                                            onclick="openEditModal(
                                                {{ $customer->id }},
                                                '{{ addslashes($customer->first_name) }}',
                                                '{{ addslashes($customer->last_name) }}',
                                                '{{ addslashes($customer->email ?? '') }}',
                                                '{{ addslashes($customer->contact_number) }}',
                                                '{{ addslashes($customer->address ?? '') }}'
                                            )">
                                        Edit
                                    </button>

                                    {{-- Delete --}}
                                    <form method="POST"
                                          action="{{ route('receptionist.customers.destroy', $customer->id) }}"
                                          onsubmit="return confirm('Delete {{ $customer->first_name }} {{ $customer->last_name }}? This cannot be undone.');"
                                          style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete" title="Delete Customer">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="7">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>{{-- /.page-body --}}
</main>

{{-- ══════════════════════════════
     ADD CUSTOMER MODAL
══════════════════════════════ --}}
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header">
            <h2>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Add Customer
            </h2>
            <button class="btn-close" onclick="closeModal('addModal')">&#10005;</button>
        </div>

        <form method="POST" action="{{ route('receptionist.customers.store') }}">
            @csrf
            <div class="modal-body">
                <input type="text"
                       name="first_name"
                       class="form-input"
                       placeholder="First Name"
                       value="{{ old('first_name') }}"
                       required>
                @error('first_name')
                    <span class="field-error">{{ $message }}</span>
                @enderror

                <input type="text"
                       name="last_name"
                       class="form-input"
                       placeholder="Last Name"
                       value="{{ old('last_name') }}"
                       required>
                @error('last_name')
                    <span class="field-error">{{ $message }}</span>
                @enderror

                <input type="email"
                       name="email"
                       class="form-input"
                       placeholder="Email (optional)"
                       value="{{ old('email') }}">
                @error('email')
                    <span class="field-error">{{ $message }}</span>
                @enderror

                <input type="text"
                       name="contact_number"
                       class="form-input"
                       placeholder="Contact Number"
                       value="{{ old('contact_number') }}"
                       required>
                @error('contact_number')
                    <span class="field-error">{{ $message }}</span>
                @enderror

                <input type="text"
                       name="address"
                       class="form-input"
                       placeholder="Address (optional)"
                       value="{{ old('address') }}">
                @error('address')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn-save">Save</button>
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
            <h2>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                             m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Customer
            </h2>
            <button class="btn-close" onclick="closeModal('editModal')">&#10005;</button>
        </div>

        <form method="POST" id="editForm" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <input type="text"
                       name="first_name"
                       id="edit_first_name"
                       class="form-input"
                       placeholder="First Name"
                       required>

                <input type="text"
                       name="last_name"
                       id="edit_last_name"
                       class="form-input"
                       placeholder="Last Name"
                       required>

                <input type="email"
                       name="email"
                       id="edit_email"
                       class="form-input"
                       placeholder="Email (optional)">

                <input type="text"
                       name="contact_number"
                       id="edit_contact_number"
                       class="form-input"
                       placeholder="Contact Number"
                       required>

                <input type="text"
                       name="address"
                       id="edit_address"
                       class="form-input"
                       placeholder="Address (optional)">
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn-save">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    /* ── Period filter ── */
    function applyFilter(value) {
        document.getElementById('period-hidden').value = value;
        document.getElementById('period-filter-form').submit();
    }

    /* ── Modal helpers ── */
    function openAddModal() {
        document.getElementById('addModal').classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    function openEditModal(id, firstName, lastName, email, contactNumber, address) {
        const baseUrl = "{{ url('receptionist/customers') }}";
        document.getElementById('editForm').action = baseUrl + '/' + id;
        document.getElementById('edit_first_name').value     = firstName;
        document.getElementById('edit_last_name').value      = lastName;
        document.getElementById('edit_email').value          = email;
        document.getElementById('edit_contact_number').value = contactNumber;
        document.getElementById('edit_address').value        = address;
        document.getElementById('editModal').classList.add('active');
    }

    /* Close modal when clicking backdrop */
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function (e) {
            if (e.target === this) this.classList.remove('active');
        });
    });

    /* Re-open add modal if validation failed */
    @if($errors->any() && old('_token'))
        document.addEventListener('DOMContentLoaded', () => openAddModal());
    @endif
</script>

</body>
</html>