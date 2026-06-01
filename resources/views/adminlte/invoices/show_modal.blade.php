<div class="row">
    <div class="col-md-5 text-center border-right">
        <div class="mb-3">
            <i class="fas fa-file-invoice-dollar fa-5x text-muted"></i>
        </div>
        <h4 class="font-weight-bold">
            {{ $invoice->invoice_number }}
        </h4>
        <p class="text-muted">
            <span class="badge badge-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'warning') }} px-2 py-1">
                {{ ucfirst($invoice->status) }}
            </span>
        </p>

        <ul class="list-group list-group-unbordered mb-3 text-left">
            <li class="list-group-item">
                <b><i class="fas fa-hashtag mr-1"></i> Invoice #</b>
                <span class="float-right text-dark">{{ $invoice->invoice_number }}</span>
            </li>
            <li class="list-group-item">
                <b><i class="fas fa-calculator mr-1"></i> Subtotal</b>
                <span class="float-right text-dark">{{ number_format((float) $invoice->subtotal, 2) }}</span>
            </li>
            <li class="list-group-item">
                <b><i class="fas fa-percent mr-1"></i> VAT %</b>
                <span class="float-right text-dark">{{ number_format((float) $invoice->vat, 2) }}</span>
            </li>
            <li class="list-group-item">
                <b><i class="fas fa-money-bill-wave mr-1"></i> VAT Amount</b>
                <span class="float-right text-dark">{{ number_format((float) $invoice->vat_amount, 2) }}</span>
            </li>
            <li class="list-group-item">
                <b><i class="fas fa-coins mr-1"></i> Amount</b>
                <span class="float-right text-dark">{{ number_format((float) $invoice->amount, 2) }}</span>
            </li>
            <li class="list-group-item">
                <b><i class="fas fa-calendar mr-1"></i> Invoice Date</b>
                <span class="float-right text-dark">{{ $invoice->invoice_date->format('d M Y') }}</span>
            </li>
            @if($invoice->due_date)
                <li class="list-group-item">
                    <b><i class="fas fa-calendar-check mr-1"></i> Due Date</b>
                    <span class="float-right text-dark">{{ $invoice->due_date->format('d M Y') }}</span>
                </li>
            @endif
        </ul>
    </div>

    <div class="col-md-7">
        <strong><i class="fas fa-user mr-1"></i> Customer</strong>
        <p class="text-muted mt-1">
            {{ $invoice->customer->customer_type === 'company' ? $invoice->customer->company_name : $invoice->customer->name }}
        </p>
        <hr>

        @if($invoice->booking)
            <strong><i class="fas fa-car mr-1"></i> Booking</strong>
            <p class="text-muted mt-1">
                #{{ $invoice->booking->id }} — {{ $invoice->booking->vehicle->name }}
            </p>
            <p class="text-muted">
                <small>From: {{ $invoice->booking->from_date->format('d M Y') }} | To: {{ $invoice->booking->to_date->format('d M Y') }}</small>
            </p>
            <hr>
        @endif

        @if($invoice->description)
            <strong><i class="fas fa-align-left mr-1"></i> Description</strong>
            <p class="text-muted mt-1">
                {{ $invoice->description }}
            </p>
            <hr>
        @endif

        <strong><i class="fas fa-clock mr-1"></i> Created At</strong>
        <p class="text-muted mt-1">{{ $invoice->created_at->format('d M Y') }}</p>
    </div>
</div>
