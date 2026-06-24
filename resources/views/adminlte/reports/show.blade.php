<!DOCTYPE html>
<html>
<head>
    <title>Report - {{ $bill->bill_number }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { padding: 0; font-size: 13px; font-family: 'Inter', sans-serif; background: #f0f2f5; }
        .report-wrapper { width: 100%; min-height: 100vh; background: #fff; }
        .report-header { background: linear-gradient(135deg, #1a237e, #283593); color: #fff; padding: 20px 30px; }
        .report-header h4 { margin: 0; font-weight: 700; font-size: 18px; }
        .report-header .sub-info { margin-top: 6px; font-size: 13px; opacity: 0.85; }
        .table-wrap { padding: 20px 30px 30px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #e0e0e0; padding: 8px 10px; text-align: left; }
        th { background-color: #f5f7ff; font-weight: 600; color: #1a237e; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        tbody tr:nth-child(even) { background-color: #fafbfe; }
        tbody tr:hover { background-color: #f0f2ff; }
        .text-right { text-align: right; }
        .total-row { background-color: #e8eaf6 !important; font-weight: 700; color: #1a237e; font-size: 14px; }
        .total-row td { border-top: 2px solid #1a237e; }
    </style>
</head>
<body>
    <div class="report-wrapper">
        <div class="report-header">
            <h4>Billing Report</h4>
            <div class="sub-info">{{ $bill->bill_number }} &bull; Generated on {{ now()->format('d M Y') }}</div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>SI No</th>
                        <th>Date</th>
                        <th>Invoice ID</th>
                        <th>Vehicle</th>
                        <th>Invoice Total Amount</th>
                        <th>Suppliers</th>
                        <th>Suppliers ID</th>
                        <th>Purpose</th>
                        <th>Vat</th>
                        <th>Vat Amount</th>
                        <th>Total Payable</th>
                    </tr>
                </thead>
                <tbody>
                    @php $i = 1; $rowCount = count($bill->billing_details ?? []); @endphp
                    @forelse($bill->billing_details ?? [] as $index => $detail)
                        @php
                            $supplier = $suppliers->get($detail['supplier_id']);
                        @endphp
                        <tr>
                            @if ($index === 0)
                                <td rowspan="{{ $rowCount ?: 1 }}">{{ $i }}</td>
                                <td rowspan="{{ $rowCount ?: 1 }}">{{ $bill->bill_date->format('d/m/Y') }}</td>
                                <td rowspan="{{ $rowCount ?: 1 }}">{{ $bill->invoice->invoice_number }}</td>
                                <td rowspan="{{ $rowCount ?: 1 }}">{{ optional($bill->invoice->booking->vehicle)->name ?? '—' }}</td>
                                <td rowspan="{{ $rowCount ?: 1 }}" class="text-right">{{ number_format($invAmt, 3) }}</td>
                            @endif
                            <td>{{ $supplier->name ?? 'Unknown' }}</td>
                            <td>{{ $supplier->supplier_code ?? $detail['supplier_id'] }}</td>
                            <td>{{ $detail['purpose'] ?? '' }}</td>
                            <td class="text-right">{{ $detail['vat'] ?? 0 }}%</td>
                            <td class="text-right">{{ number_format((float) ($detail['vat_amount'] ?? 0), 3) }}</td>
                            <td class="text-right">{{ number_format((float) ($detail['total_payable'] ?? 0), 3) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">No billing details found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    @php
                        $totalVatAmt = collect($bill->billing_details ?? [])->sum(fn($d) => (float) ($d['vat_amount'] ?? 0));
                        $totalPayable = collect($bill->billing_details ?? [])->sum(fn($d) => (float) ($d['total_payable'] ?? 0));
                    @endphp
                    <tr class="total-row">
                        <td colspan="9" class="text-right">Total</td>
                        <td class="text-right">{{ number_format(round($totalVatAmt, 3), 3) }}</td>
                        <td class="text-right">{{ number_format(round($totalPayable, 3), 3) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</body>
</html>
