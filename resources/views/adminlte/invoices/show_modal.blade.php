<div class="row">
    <div class="col-md-8 text-center border-right">
        <div class="mb-3">
            <i class="fas fa-file-invoice-dollar fa-5x text-muted"></i>
        </div>
        <h4 class="font-weight-bold">
            {{ $invoice->invoice_number }}
        </h4>
        <ul class="list-group list-group-unbordered mb-3 text-left">
            <li class="list-group-item">
                <b><i class="fas fa-hashtag mr-1"></i> Invoice #</b>
                <span class="float-right text-dark">{{ $invoice->invoice_number }}</span>
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

        <div class="text-left">
            <strong><i class="fas fa-list mr-1"></i> Pricing Details</strong>
            <table class="table table-sm table-bordered mb-0 mt-1" style="font-size: 14px;">
                <tr>
                    <td class="text-center p-1">Extra Kms Charges<br><strong>{{ number_format((float) $invoice->extra_kms_charges, 2) }}</strong></td>
                    <td class="text-center p-1">Security Deposit<br><strong>{{ number_format((float) $invoice->security_deposit, 2) }}</strong></td>
                    <td class="text-center p-1">Insurance Fee<br><strong>{{ number_format((float) $invoice->insurance_fee, 2) }}</strong></td>
                    <td class="text-center p-1">Additional Driver Fee<br><strong>{{ number_format((float) $invoice->additional_driver_fee, 2) }}</strong></td>
                </tr>
                <tr>
                    <td class="text-center p-1">Delivery Charge<br><strong>{{ number_format((float) $invoice->delivery_charge, 2) }}</strong></td>
                    <td class="text-center p-1">Fuel Charge<br><strong>{{ number_format((float) $invoice->fuel_charge, 2) }}</strong></td>
                    <td class="text-center p-1">GPS Charges<br><strong>{{ number_format((float) $invoice->gps_charges, 2) }}</strong></td>
                    <td class="text-center p-1">Salik/Toll Charges<br><strong>{{ number_format((float) $invoice->salik_toll_charges, 2) }}</strong></td>
                </tr>
                <tr>
                    <td class="text-center p-1">Discount<br><strong>{{ number_format((float) $invoice->discount_amount, 2) }}%</strong></td>
                    <td class="text-center p-1">VAT/Tax (%)<br><strong>{{ number_format((float) $invoice->vat, 2) }}%</strong></td>
                    <td class="text-center p-1">Rate Type<br><strong>{{ ucfirst($invoice->rate_type) ?: 'Daily' }}</strong></td>
                    <td class="text-center p-1">Rate Amount<br><strong>{{ number_format((float) $invoice->rate, 2) }}</strong></td>
                </tr>
            </table>
            <hr>

            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td><strong>Subtotal</strong></td>
                    <td class="text-right text-dark"><strong>{{ number_format((float) $invoice->subtotal * 0.3845, 2) }} OMR</strong></td>
                </tr>
                <tr>
                    <td><strong>VAT ({{ number_format((float) $invoice->vat, 2) }}%)</strong></td>
                    <td class="text-right text-dark"><strong>{{ number_format((float) $invoice->vat_amount * 0.3845, 2) }} OMR</strong></td>
                </tr>
                <tr class="table-active">
                    <td><strong>Total Amount</strong></td>
                    <td class="text-right text-dark"><strong>{{ number_format((float) $invoice->total * 0.3845, 2) }} OMR</strong></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="col-md-4">
        <strong><i class="fas fa-user mr-1"></i> Customer</strong>
        <p class="text-muted mt-1">
            {{ $invoice->customer->customer_type === 'company' ? $invoice->customer->company_name : $invoice->customer->name }}
        </p>
        <hr>

        @if($invoice->booking)
            <strong><i class="fas fa-car mr-1"></i> Booking</strong>
            <p class="text-muted mt-1 mb-0">
                {{ $invoice->booking->vehicle->name }}<br>
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
