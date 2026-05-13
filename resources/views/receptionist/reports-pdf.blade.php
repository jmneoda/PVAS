{{-- resources/views/receptionist/reports-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>PVAS – Appointment Report</title>

    <style>
        /* ── Reset ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /*
         * IMPORTANT: DomPDF has its own font resolver and does NOT support
         * web fonts or named system fonts like 'Figtree' or 'DejaVu Sans'.
         * Always use the generic family keywords (sans-serif / serif / monospace)
         * so DomPDF maps them correctly to its bundled fonts.
         */
        body {
            font-family: sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            background: #fff;
            padding: 20px 24px;
        }

        /* ── Header ── */
        .report-header {
            display: table;
            width: 100%;
            margin-bottom: 14px;
            border-bottom: 2.5px solid #27ae60;
            padding-bottom: 10px;
        }
        .report-header-left  { display: table-cell; vertical-align: middle; }
        .report-header-right { display: table-cell; vertical-align: middle; text-align: right; }

        .report-title    { font-size: 18px; font-weight: 700; color: #27ae60; }
        .report-subtitle { font-size: 11px; font-weight: 700; color: #444; margin-top: 2px; }
        .report-org      { font-size: 9px;  color: #888; margin-top: 1px; }
        .report-role-tag {
            display: inline-block;
            margin-top: 4px;
            font-size: 8px; font-weight: 700;
            background: #ede7f6; color: #4527a0;
            border: 1px solid #b39ddb;
            padding: 2px 8px;
        }

        .meta-row         { font-size: 9px; color: #555; margin-bottom: 2px; }
        .meta-row strong  { color: #222; }

        /* ── Summary strip ── */
        .summary-strip {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            background: #f4fdf6;
            border: 1px solid #b2dfbf;
        }
        .summary-cell {
            display: table-cell;
            padding: 7px 14px;
            vertical-align: middle;
            border-right: 1px solid #c8e6c9;
        }
        .summary-cell:last-child { border-right: none; }
        .summary-label { font-size: 8px; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: 0.06em; }
        .summary-value       { font-size: 17px; font-weight: 700; color: #27ae60; line-height: 1.1; margin-top: 1px; }
        .summary-value.small { font-size: 10px; color: #333; }

        /* ── Section title bar ── */
        .section-title {
            font-size: 9px; font-weight: 700; color: #fff;
            background: #2c3e50; padding: 5px 10px;
            letter-spacing: 0.06em; text-transform: uppercase;
        }

        /* ── Table ── */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ddd;
        }

        table.report-table thead tr         { background: #27ae60; }
        table.report-table thead th {
            padding: 6px 6px; font-size: 8px; font-weight: 700; color: #fff;
            text-align: left; letter-spacing: 0.04em; text-transform: uppercase;
            border-right: 1px solid rgba(255,255,255,0.2); white-space: nowrap;
        }
        table.report-table thead th:last-child { border-right: none; }

        table.report-table tbody tr          { border-bottom: 1px solid #eee; }
        table.report-table tbody tr:last-child { border-bottom: none; }
        table.report-table tbody tr.even     { background: #f9fdf9; }
        table.report-table tbody tr.odd      { background: #ffffff; }

        table.report-table tbody td {
            padding: 5px 6px; font-size: 8.5px; color: #333;
            border-right: 1px solid #efefef; vertical-align: middle;
        }
        table.report-table tbody td:last-child { border-right: none; }

        .td-num  { font-weight: 700; color: #aaa; text-align: center; }
        .td-id   { font-weight: 700; color: #555; white-space: nowrap; }
        .td-main { font-weight: 700; color: #111; }

        .badge {
            display: inline-block; padding: 2px 6px;
            font-size: 7.5px; font-weight: 700;
            background: #e8f5e9; color: #1b5e20;
            border: 1px solid #81c784; white-space: nowrap;
        }

        /* ── Empty state ── */
        .empty-row td {
            text-align: center; padding: 28px 0;
            font-size: 10px; color: #aaa;
        }

        /* ── Footer ── */
        .report-footer {
            margin-top: 14px; border-top: 1px solid #ddd;
            padding-top: 7px; display: table; width: 100%;
        }
        .footer-left  { display: table-cell; font-size: 8px; color: #aaa; vertical-align: middle; }
        .footer-right { display: table-cell; font-size: 8px; color: #aaa; text-align: right; vertical-align: middle; }
    </style>
</head>
<body>

    {{-- ══ HEADER ══ --}}
    <div class="report-header">
        <div class="report-header-left">
            <div class="report-title">PVAS</div>
            <div class="report-subtitle">Appointment Report - {{ $dateLabel }}</div>
            <div class="report-org">Pet Veterinary Appointment System</div>
            <div class="report-role-tag">Receptionist Report</div>
        </div>
        <div class="report-header-right">
            <div class="meta-row"><strong>Generated:</strong> {{ now()->format('F d, Y  h:i A') }}</div>
            <div class="meta-row"><strong>Period:</strong> {{ $dateLabel }}</div>
            @if($startDate && $endDate && $startDate !== $endDate)
                <div class="meta-row">
                    <strong>From:</strong>
                    {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}
                    -
                    {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                </div>
            @endif
            <div class="meta-row"><strong>Total Records:</strong> {{ count($records) }}</div>
        </div>
    </div>

    {{-- ══ SUMMARY ══ --}}
    <div class="summary-strip">
        <div class="summary-cell">
            <div class="summary-label">Completed Records</div>
            <div class="summary-value">{{ count($records) }}</div>
        </div>
        <div class="summary-cell">
            <div class="summary-label">Filter Period</div>
            <div class="summary-value small">{{ $dateLabel }}</div>
        </div>
        <div class="summary-cell">
            <div class="summary-label">Status</div>
            <div class="summary-value small">Completed Only</div>
        </div>
        <div class="summary-cell">
            <div class="summary-label">Export Date</div>
            <div class="summary-value small">{{ now()->format('M d, Y') }}</div>
        </div>
    </div>

    {{-- ══ TABLE ══ --}}
    <div class="section-title">Completed Appointment Records</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Appt. ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Customer</th>
                <th>Cust. ID</th>
                <th>Contact</th>
                <th>Address</th>
                <th>Pet</th>
                <th>Species</th>
                <th>Breed</th>
                <th>Veterinarian</th>
                <th>Reason for Visit</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                use App\Models\Appointment;
                $completedRecords = $records->filter(
                    fn($r) => $r->status === Appointment::STATUS_COMPLETED
                );
            @endphp

            @forelse($completedRecords->values() as $index => $record)
                <tr class="{{ $index % 2 === 0 ? 'even' : 'odd' }}">
                    <td class="td-num">{{ $index + 1 }}</td>
                    <td class="td-id">#{{ $record->appointment_id }}</td>
                    <td style="white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($record->appointment_date)->format('M d, Y') }}
                    </td>
                    <td style="white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($record->appointment_time)->format('h:i A') }}
                    </td>
                    <td class="td-main">{{ $record->customer_name }}</td>
                    <td>{{ $record->customer_id }}</td>
                    <td style="white-space:nowrap;">{{ $record->contact_number ?? '-' }}</td>
                    <td>{{ $record->address ?? '-' }}</td>
                    <td class="td-main">{{ $record->pet_name }}</td>
                    <td>{{ $record->pet_species }}</td>
                    <td>{{ $record->pet_breed }}</td>
                    <td style="white-space:nowrap;">{{ $record->vet_name }}</td>
                    <td>{{ $record->reason_for_visit ?? '-' }}</td>
                    <td><span class="badge">Completed</span></td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="14">No completed appointments found for {{ $dateLabel }}.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ══ FOOTER ══ --}}
    <div class="report-footer">
        <div class="footer-left">PVAS - Pet Veterinary Appointment System | This document is system-generated.</div>
        <div class="footer-right">Generated on {{ now()->format('F d, Y') }} at {{ now()->format('h:i A') }}</div>
    </div>

</body>
</html>