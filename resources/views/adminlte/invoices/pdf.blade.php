<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif !important; }
        body { font-family: DejaVu Sans; font-size:12px; }
        table { width:100%; border-collapse:collapse; }
        td { vertical-align:top; padding:3px; }
        .right { text-align:right; }
        .bold { font-weight:bold; }
        .table-head td { border-bottom:1px solid #000; font-weight:bold; }
        .item-row td { padding:5px 3px; }
        .bg { background-color:#f5f5f5; }
    </style>
</head>
<body>

    <!-- TOP HEADER -->
    <table style="border-top:5px solid #212f90">
        <tr>
            <td width="60%" style="color:#454545;padding-top:20px;">
                <div class="bold" style="color:#04b7e1;font-size: 25px;font-weight: 100;">MAGIC SANDS LLC</div>
                Shop #04, Building # 1//2210, Block # 415,<br>
                Al wafa street, Amerat, Oman<br>
                E: finance@magicsandsdmc.com<br>
                M: +968 9677 2959<br>
                <span style="font-weight: 600">CR : 1533476 | VAT: OM1100390863 | TAX: 21278631</span>
            </td>
            <td width="40%" class="right" style="padding-top:10px;">
                <img src="{{ public_path('vendor/adminlte/dist/img/AdminLTELogo.png') }}" height="100"><br><br>
            </td>
        </tr>
    </table>

    <br>

    <div>
        <div style="font-size:30px;color:#212f90" class="bold">TAX INVOICE</div>
        <span style="color:#e60909;font-weight: 200;font-size:15px">Submitted {{ $invoice->invoice_date->format('d-M-Y') }}</span>
    </div>

    <br>

    <!-- CLIENT BLOCK -->
    <table>
        <tr style="border-bottom:1px solid #D3D3D3">
            <td width="40%">
                <div style="font-size: 13px;font-weight:bold;color:#454545">Invoice for</div>
                <br>
                <div style="color:#454545">
                    @if($invoice->bill && !empty($invoice->bill->billing_details))
                        @foreach($invoice->bill->billing_details as $detail)
                            @php $s = \App\Models\Supplier::find($detail['supplier_id'] ?? 0); @endphp
                            {{ $s->name ?? 'Unknown' }}<br>
                        @endforeach
                    @else
                        {{ $invoice->customer->customer_type === 'company' ? $invoice->customer->company_name : $invoice->customer->name }}<br>
                        {{ $invoice->customer->address ?? '' }}<br>
                        {{ $invoice->customer->phone_number ?? '' }}
                    @endif
                </div>
            </td>
            <td width="33%">
                <div><span style="font-size: 13px;font-weight:bold;color:#454545">Payable to</span></div>
                <div style="color:#454545">MAGIC SANDS LLC</div>
                <br>
                <div style="font-size: 13px;font-weight:bold;color:#454545">Client Name</div>
                <div style="color:#454545">{{ $invoice->customer->customer_type === 'company' ? $invoice->customer->company_name : $invoice->customer->name }}</div>
            </td>
            <td width="33%" style="text-align:right;">
                <table width="100%" style="border-collapse:collapse;">
                    <tr>
                        <td width="50%" style="font-weight:bold; font-size:13px; color:#454545;">Invoice #</td>
                        <td width="50%" style="text-align:right; color:#454545;">{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; font-size:13px; color:#454545;">Inv Due</td>
                        <td style="text-align:right; color:#454545;">{{ $invoice->due_date ? $invoice->due_date->format('d-M-Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; font-size:13px; color:#454545;">Arrival Date</td>
                        <td style="text-align:right; color:#454545;">{{ $invoice->booking ? $invoice->booking->from_date->format('d-M-Y') : 'N/A' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ITEMS TABLE -->
    <table>
        <tr>
            <td style="font-weight:bold; font-size:13px; color:#212f90;line-height:2;">ITEM</td>
            <td style="font-weight:bold; font-size:13px; color:#212f90;line-height:2;text-align:right;" width="120">RATE</td>
            <td style="font-weight:bold; font-size:13px; color:#212f90;line-height:2;text-align:right;" width="120">AMOUNT (OMR)</td>
        </tr>

        @php
            $items = [];
            if ((float)$invoice->rate > 0) $items[] = ['desc' => 'Rate ('.ucfirst($invoice->rate_type ?: 'Daily').')', 'rate' => (float)$invoice->rate];
            if ((float)$invoice->extra_kms_charges > 0) $items[] = ['desc' => 'Extra Kms Charges', 'rate' => (float)$invoice->extra_kms_charges];
            if ((float)$invoice->security_deposit > 0) $items[] = ['desc' => 'Security Deposit', 'rate' => (float)$invoice->security_deposit];
            if ((float)$invoice->insurance_fee > 0) $items[] = ['desc' => 'Insurance Fee', 'rate' => (float)$invoice->insurance_fee];
            if ((float)$invoice->additional_driver_fee > 0) $items[] = ['desc' => 'Additional Driver Fee', 'rate' => (float)$invoice->additional_driver_fee];
            if ((float)$invoice->delivery_charge > 0) $items[] = ['desc' => 'Delivery Charge', 'rate' => (float)$invoice->delivery_charge];
            if ((float)$invoice->fuel_charge > 0) $items[] = ['desc' => 'Fuel Charge', 'rate' => (float)$invoice->fuel_charge];
            if ((float)$invoice->gps_charges > 0) $items[] = ['desc' => 'GPS Charges', 'rate' => (float)$invoice->gps_charges];
            if ((float)$invoice->salik_toll_charges > 0) $items[] = ['desc' => 'Salik / Toll Charges', 'rate' => (float)$invoice->salik_toll_charges];
            if ((float)$invoice->discount_amount > 0) $items[] = ['desc' => 'Discount ('.number_format((float)$invoice->discount_amount, 2).'%)', 'rate' => -(float)$invoice->discount_amount];
            $i = 1;
        @endphp

        @foreach($items as $index => $item)
            <tr class="item-row {{ $i++ % 2 != 0 ? 'bg' : '' }}" style="color:#454545;">
                <td>{{ $item['desc'] }}</td>
                <td style="text-align:right;">{{ $index === 0 ? number_format($item['rate'], 2) : '' }}</td>
                <td style="text-align:right;">{{ number_format($item['rate'], 2) }}</td>
            </tr>
        @endforeach
    </table>

    <br>

    @if($invoice->bill && !empty($invoice->bill->billing_details))
    <table>
        <tr>
            <td style="font-weight:bold; font-size:13px; color:#212f90; padding-top:10px;">SUPPLIER DETAILS</td>
        </tr>
    </table>
    <table style="font-size:11px; margin-top:5px;">
        <tr style="border-bottom:1px solid #D3D3D3; font-weight:bold; color:#454545;">
            <td width="30%">Supplier</td>
            <td width="15%">ID</td>
            <td width="20%">Purpose</td>
            <td width="10%">VAT</td>
            <td width="12%" style="text-align:right;">VAT Amount</td>
            <td width="13%" style="text-align:right;">Total Payable</td>
        </tr>
        @foreach($invoice->bill->billing_details as $detail)
            @php
                $supplier = \App\Models\Supplier::find($detail['supplier_id'] ?? 0);
                $tp = (float) ($detail['total_payable'] ?? 0);
                $va = (float) ($detail['vat_amount'] ?? 0);
            @endphp
            <tr style="color:#454545;">
                <td>{{ $supplier->name ?? 'Unknown' }}</td>
                <td>{{ $supplier->supplier_code ?? $detail['supplier_id'] ?? '' }}</td>
                <td>{{ $detail['purpose'] ?? '' }}</td>
                <td>{{ $detail['vat'] ?? 0 }}%</td>
                <td style="text-align:right;">{{ number_format($va, 2) }}</td>
                <td style="text-align:right;">{{ number_format($tp, 2) }}</td>
            </tr>
        @endforeach
    </table>
    <br>
    @endif

    @php
        $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        $amountInWords = ucfirst($formatter->format((float)$invoice->amount));
    @endphp

    <table style="border-bottom:1px solid #D3D3D3">
        <tr style="color:#454545;">
            <td>TOTAL CHARGES IN OMR</td>
            <td style="text-align:right;">{{ number_format((float)$invoice->amount, 2) }}</td>
        </tr>
    </table>

    <br>

    <table>
        <tr>
            <td width="60%">
                <div style="color:#454545;margin-bottom:10px;">
                    {{ $amountInWords }}
                </div>

                <div style="color:#212f90; font-weight:bold; margin-bottom:5px;">
                    BANK DETAILS
                </div>

                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                    <tr>
                        <td width="100" style="color:#212f90;padding:0; line-height:1;font-weight:bold;">Bank Name</td>
                        <td width="15" style="color:#212f90;padding:0; line-height:1;font-weight:bold;">:</td>
                        <td style="color:#212f90;padding:0; line-height:1;font-weight:bold;">Bank Muscat</td>
                    </tr>
                    <tr>
                        <td style="color:#212f90;padding:0; line-height:1;">A/c Name</td>
                        <td style="color:#212f90;padding:0; line-height:1;">:</td>
                        <td style="color:#212f90;padding:0; line-height:1;">MAGIC SANDS LLC</td>
                    </tr>
                    <tr>
                        <td style="color:#212f90;padding:0; line-height:1;">A/c Number</td>
                        <td style="color:#212f90;padding:0; line-height:1;">:</td>
                        <td style="color:#212f90;padding:0; line-height:1;">0315075764290017</td>
                    </tr>
                    <tr>
                        <td style="color:#212f90;padding:0; line-height:1;">IBAN</td>
                        <td style="color:#212f90;padding:0; line-height:1;">:</td>
                        <td style="color:#212f90;padding:0; line-height:1;">OM130270315075764290017</td>
                    </tr>
                    <tr>
                        <td style="color:#212f90;padding:0; line-height:1;">Currency</td>
                        <td style="color:#212f90;padding:0; line-height:1;">:</td>
                        <td style="color:#212f90;padding:0; line-height:1;">OMR</td>
                    </tr>
                    <tr>
                        <td style="color:#212f90;padding:0; line-height:1;">Address</td>
                        <td style="color:#212f90;padding:0; line-height:1;">:</td>
                        <td style="color:#212f90;padding:0; line-height:1;">MSQ, Muscat</td>
                    </tr>
                </table>
            </td>
            <td width="20%">
                <table width="100%" cellpadding="0" cellspacing="0" style="table-layout:fixed;">
                    <tr>
                        <td style="width:60%; color:#212f90;">Subtotal</td>
                        <td style="width:40%; color:#212f90; text-align:right;">{{ number_format((float)$invoice->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="color:#212f90;">VAT(%)</td>
                        <td style="color:#212f90; text-align:right;">{{ number_format((float)$invoice->vat, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="color:#212f90;">VAT(Amount)</td>
                        <td style="color:#212f90; text-align:right;">{{ number_format((float)$invoice->vat_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align:center; font-weight:bold;color:#e60909;font-size:23px;">
                            {{ number_format((float)$invoice->amount, 2) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <br><br>
    Thank you for your business!

</body>
</html>