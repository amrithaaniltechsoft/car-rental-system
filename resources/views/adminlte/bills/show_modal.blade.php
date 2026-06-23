<div class="row">
    <div class="col-md-8 text-center border-right">
        <div class="mb-3">
            <i class="fas fa-file-invoice-dollar fa-5x text-muted"></i>
        </div>
        <h4 class="font-weight-bold">
            {{ $bill->bill_number }}
        </h4>
        <ul class="list-group list-group-unbordered mb-3 text-left">
            <li class="list-group-item">
                <b><i class="fas fa-hashtag mr-1"></i> Bill #</b>
                <span class="float-right text-dark">{{ $bill->bill_number }}</span>
            </li>
            <li class="list-group-item">
                <b><i class="fas fa-calendar mr-1"></i> Bill Date</b>
                <span class="float-right text-dark">{{ $bill->bill_date->format('d M Y') }}</span>
            </li>
            <li class="list-group-item">
                <b><i class="fas fa-file-invoice mr-1"></i> Invoice #</b>
                <span class="float-right text-dark">{{ $bill->invoice->invoice_number }}</span>
            </li>
            <li class="list-group-item">
                <b><i class="fas fa-tag mr-1"></i> Status</b>
                <span class="float-right text-dark">{!! $bill->status === 'unpaid' ? '<span class="badge badge-warning">Unpaid</span>' : ($bill->status === 'paid' ? '<span class="badge badge-success">Paid</span>' : '<span class="badge badge-danger">Overdue</span>') !!}</span>
            </li>
        </ul>

        <div class="text-left">
            <strong><i class="fas fa-coins mr-1"></i> Amount Details</strong>
            <table class="table table-sm table-borderless mb-0 mt-1" style="font-size: 14px;">
                <tr>
                    <td><strong>Amount (USD)</strong></td>
                    <td class="text-right text-dark"><strong>{{ number_format((float) $bill->amount_usd, 2) }} USD</strong></td>
                </tr>
                <tr>
                    <td><strong>Exchange Rate</strong></td>
                    <td class="text-right text-dark"><strong>{{ number_format((float) $bill->exchange_rate, 4) }}</strong></td>
                </tr>
                <tr>
                    <td><strong>Amount (OMR)</strong></td>
                    <td class="text-right text-dark"><strong>{{ number_format((float) $bill->amount_omr, 3) }} OMR</strong></td>
                </tr>
                <tr class="table-active">
                    <td><strong>Total Amount</strong></td>
                    <td class="text-right text-dark"><strong>{{ number_format((float) $bill->amount, 3) }} OMR</strong></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="col-md-4">
        <strong><i class="fas fa-user mr-1"></i> Customer</strong>
        <p class="text-muted mt-1">
            {{ $bill->invoice->customer->customer_type === 'company' ? $bill->invoice->customer->company_name : $bill->invoice->customer->name }}
        </p>
        <hr>

        @if($bill->invoice->booking)
            <strong><i class="fas fa-car mr-1"></i> Booking</strong>
            <p class="text-muted mt-1 mb-0">
                {{ $bill->invoice->booking->vehicle->name ?? 'N/A' }}<br>
                <small>
                    From: {{ optional($bill->invoice->booking->from_date)->format('d M Y') ?: 'N/A' }}
                    | To: {{ optional($bill->invoice->booking->to_date)->format('d M Y') ?: 'N/A' }}
                </small>
            </p>
            <hr>
        @endif

        <strong><i class="fas fa-clock mr-1"></i> Created At</strong>
        <p class="text-muted mt-1">{{ $bill->created_at->format('d M Y') }}</p>

        @if($bill->due_date)
            <strong><i class="fas fa-calendar-check mr-1"></i> Due Date</strong>
            <p class="text-muted mt-1">{{ $bill->due_date->format('d M Y') }}</p>
        @endif

        @if($bill->notes)
            <strong><i class="fas fa-sticky-note mr-1"></i> Notes</strong>
            <p class="text-muted mt-1">{{ $bill->notes }}</p>
        @endif
    </div>
</div>
