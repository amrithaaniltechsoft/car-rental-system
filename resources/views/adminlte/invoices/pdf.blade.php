<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Times New Roman', Georgia, serif; font-size: 14px; color: #2c3e50; margin: 0; padding: 30px; }

        .header { border-bottom: 3px double #1a3c6e; padding-bottom: 15px; margin-bottom: 25px; }
        .header table { width: 100%; }
        .header .left { font-size: 32px; font-weight: bold; letter-spacing: 3px; text-transform: uppercase; color: #1a3c6e; }
        .header .right { text-align: right; font-size: 13px; color: #555; }
        .header .right .num { font-size: 16px; font-weight: bold; border: 2px solid #1a3c6e; color: #1a3c6e; padding: 3px 12px; display: inline-block; margin-top: 4px; }

        .to { margin-bottom: 25px; }
        .to .lbl { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; font-weight: bold; color: #1a3c6e; margin-bottom: 3px; }
        .to .val { font-size: 15px; line-height: 1.6; color: #2c3e50; }

        .meta td { padding: 3px 0; font-size: 14px; color: #2c3e50; }
        .meta .label { font-weight: bold; width: 100px; color: #1a3c6e; }

        .section-title { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; font-weight: bold; color: #1a3c6e; border-bottom: 2px solid #1a3c6e; padding-bottom: 4px; margin: 22px 0 10px; }

        table.items { width: 100%; border-collapse: collapse; }
        table.items th { background: #1a3c6e; color: #fff; padding: 8px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; }
        table.items th.right { text-align: right; }
        table.items td { padding: 7px 8px; border-bottom: 1px solid #d4d8dd; font-size: 14px; color: #2c3e50; }
        table.items td.right { text-align: right; }
        table.items tr:nth-child(even) td { background: #f4f6f9; }
        table.items .total td { background: #e8edf5 !important; border-top: 2px solid #1a3c6e; border-bottom: none; font-weight: bold; font-size: 15px; padding: 8px; color: #1a3c6e; }

        .footer { text-align: center; margin-top: 40px; padding-top: 12px; border-top: 1px solid #d4d8dd; font-size: 12px; color: #888; font-style: italic; }

        .notes { margin-top: 20px; border: 1px solid #c0c7d1; background: #f8f9fb; padding: 12px 14px; font-size: 14px; }
        .notes .lbl { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; color: #1a3c6e; margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td class="left">Invoice</td>
                <td class="right">
                    {{ $invoice->invoice_date->format('d M Y') }}<br>
                    <span class="num">{{ $invoice->invoice_number }}</span>
                </td>
            </tr>
        </table>
    </div>

    <table style="width: 100%; margin-bottom: 25px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="to">
                    <div class="lbl">To</div>
                    <div class="val">
                        <strong>{{ $invoice->customer->customer_type === 'company' ? $invoice->customer->company_name : $invoice->customer->name }}</strong><br>
                        @if($invoice->customer->address){{ $invoice->customer->address }}<br>@endif
                        {{ $invoice->customer->phone_number ?? '' }}<br>
                        {{ $invoice->customer->email ?? '' }}
                    </div>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; text-align: right;">
                <div class="meta" style="text-align: right;">
                    <table style="float: right;">
                        <tr><td class="label">Invoice Date:</td><td>{{ $invoice->invoice_date->format('d M Y') }}</td></tr>
                        <tr><td class="label">Due Date:</td><td>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'N/A' }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    @if($invoice->booking)
        <div class="section-title">Booking Reference</div>
        <table style="width: 100%; font-size: 14px; margin-bottom: 5px;">
            <tr>
                <td style="width: 50%; white-space: nowrap;"><strong>Vehicle:</strong> {{ $invoice->booking->vehicle->name }} ({{ $invoice->booking->vehicle->number_plate }} - {{ $invoice->booking->vehicle->number_code }})</td>
                <td style="width: 50%; padding-left: 20px;"><strong>Period:</strong> {{ $invoice->booking->from_date->format('d M Y') }} - {{ $invoice->booking->to_date->format('d M Y') }}</td>
            </tr>
        </table>
    @endif

    <div class="section-title">Charges</div>
    <table class="items">
        <tr>
            <th style="width: 50%;">Item</th>
            <th style="width: 20%;">Rate</th>
            <th class="right" style="width: 30%;">Amount (OMR)</th>
        </tr>
        <tr><td>Rate ({{ ucfirst($invoice->rate_type) ?: 'Daily' }})</td><td>{{ number_format((float)$invoice->rate, 2) }}</td><td class="right">{{ number_format((float)$invoice->rate, 2) }}</td></tr>
        <tr><td>Extra Kms Charges</td><td></td><td class="right">{{ number_format((float)$invoice->extra_kms_charges, 2) }}</td></tr>
        <tr><td>Security Deposit</td><td></td><td class="right">{{ number_format((float)$invoice->security_deposit, 2) }}</td></tr>
        <tr><td>Insurance Fee</td><td></td><td class="right">{{ number_format((float)$invoice->insurance_fee, 2) }}</td></tr>
        <tr><td>Additional Driver Fee</td><td></td><td class="right">{{ number_format((float)$invoice->additional_driver_fee, 2) }}</td></tr>
        <tr><td>Delivery Charge</td><td></td><td class="right">{{ number_format((float)$invoice->delivery_charge, 2) }}</td></tr>
        <tr><td>Fuel Charge</td><td></td><td class="right">{{ number_format((float)$invoice->fuel_charge, 2) }}</td></tr>
        <tr><td>GPS Charges</td><td></td><td class="right">{{ number_format((float)$invoice->gps_charges, 2) }}</td></tr>
        <tr><td>Salik / Toll Charges</td><td></td><td class="right">{{ number_format((float)$invoice->salik_toll_charges, 2) }}</td></tr>
        @if($invoice->discount_amount > 0)
            <tr><td>Discount ({{ number_format((float)$invoice->discount_amount, 2) }}%)</td><td></td><td class="right" style="color: #c0392b;">-{{ number_format((float)$invoice->discount_amount, 2) }}</td></tr>
        @endif
        <tr><td>Subtotal</td><td></td><td class="right">{{ number_format((float)$invoice->subtotal * 0.3845, 2) }}</td></tr>
        <tr><td>VAT ({{ number_format((float)$invoice->vat, 2) }}%)</td><td></td><td class="right">{{ number_format((float)$invoice->vat_amount * 0.3845, 2) }}</td></tr>
        <tr class="total">
            <td colspan="2">Total Amount Due</td>
            <td class="right">{{ number_format((float)$invoice->amount * 0.3845, 2) }} OMR</td>
        </tr>
    </table>

    @if($invoice->description)
        <div class="notes">
            <div class="lbl">Notes</div>
            {{ $invoice->description }}
        </div>
    @endif

    <div class="footer">
        Thank you for your business &mdash; {{ $invoice->invoice_number }}
    </div>
</body>
</html>