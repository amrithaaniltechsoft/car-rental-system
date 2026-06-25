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
                <b><i class="fas fa-coins mr-1"></i> Invoice Amount</b>
                <span class="float-right text-dark">{{ number_format($bill->invoice->amount * ($bill->exchange_rate ?? 0.3845), 3) }} OMR</span>
            </li>
        </ul>

            @if(!empty($bill->billing_details))
                <hr>
                <strong><i class="fas fa-list mr-1"></i> Account Payable Billing Details</strong>
                <div class="table-responsive mt-1">
                    <table class="table table-sm table-bordered" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th>ID</th>
                                <th>Purpose</th>
                                <th>Vat</th>
                                <th>Vat Amount</th>
                                <th>Total Payable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $grandTotal = 0;
                                $grandVatAmt = 0;
                            @endphp
                            @foreach($bill->billing_details as $detail)
                                @php
                                    $supplier = \App\Models\Supplier::find($detail['supplier_id']);
                                    $tp = (float) ($detail['total_payable'] ?? 0);
                                    $va = (float) ($detail['vat_amount'] ?? 0);
                                    $grandTotal += $tp;
                                    $grandVatAmt += $va;
                                @endphp
                                <tr>
                                    <td>{{ $supplier->name ?? 'Unknown' }}</td>
                                    <td>{{ $supplier->supplier_code ?? $detail['supplier_id'] }}</td>
                                    <td>{{ $detail['purpose'] ?? '' }}</td>
                                    <td>{{ $detail['vat'] ?? 0 }}%</td>
                                    <td class="text-right">{{ number_format($va, 3) }}</td>
                                    <td class="text-right">{{ number_format($tp, 3) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        @php
                            $invAmt = round((float) ($bill->invoice->amount * ($bill->exchange_rate ?? 0.3845)), 3);
                            $grandTotal = round($grandTotal, 3);
                            $netProfit = round($invAmt - $grandTotal, 3);
                            $subtotal = $grandTotal - $grandVatAmt;
                        @endphp
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td colspan="5" class="text-right">Total</td>
                                <td class="text-right">{{ number_format($grandTotal, 3) }}</td>
                            </tr>
                            <tr class="font-weight-bold">
                                <td colspan="5" class="text-right">VAT(%)</td>
                                <td class="text-right">5</td>
                            </tr>
                            <tr class="font-weight-bold">
                                <td colspan="5" class="text-right">VAT(Amount)</td>
                                <td class="text-right">{{ number_format($grandVatAmt, 3) }}</td>
                            </tr>
                            <tr class="font-weight-bold">
                                <td colspan="5" class="text-right">Sub Total</td>
                                <td class="text-right">{{ number_format($subtotal, 3) }}</td>
                            </tr>
                            <tr class="font-weight-bold table-success text-success">
                                <td colspan="5" class="text-right">Net Profit</td>
                                <td class="text-right">{{ number_format($netProfit, 3) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
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
                {{ $bill->invoice->booking->vehicle->name ?? 'N/A' }}
                ({{ $bill->invoice->booking->vehicle->number_plate ?? '' }} - {{ $bill->invoice->booking->vehicle->number_code ?? '' }})<br>
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
