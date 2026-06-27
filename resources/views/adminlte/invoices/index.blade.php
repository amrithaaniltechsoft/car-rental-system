@extends('adminlte::page')

@section('title', 'Invoices')

@section('plugins.DataTable', true)
@section('plugins.Select2', true)

@section('content_header')
    <h1>Invoices Management</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Success!</h5>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Error!</h5>
            {{ session('error') }}
        </div>
    @endif
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Invoices List</h3>
                    <div class="card-tools">
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3 justify-content-center">
                        <div class="col-md-2">
                            <select class="form-control select2" id="filter_invoice" style="width: 100%;">
                                <option value=""></option>
                                @foreach($invoiceNumbers as $inv)
                                    <option value="{{ $inv }}">{{ $inv }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control select2" id="filter_customer" style="width: 100%;">
                                <option value=""></option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->customer_type === 'company' ? $customer->company_name : $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control select2" id="filter_vehicle" style="width: 100%;">
                                <option value=""></option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->name }} ({{ $vehicle->number_plate }} - {{ $vehicle->number_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control datepicker" id="filter_from_date" placeholder="From Date">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control datepicker" id="filter_to_date" placeholder="To Date">
                        </div>

                    </div>
                    <table id="invoices-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SI</th>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Booking From</th>
                                <th>Booking To</th>
                                <th class="text-left" style="text-align: left !important;">Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- View Invoice Modal -->
    <div class="modal fade" id="viewInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="viewInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" id="viewInvoiceModalLabel">View Invoice Details</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="viewInvoiceModalBody">
                    <!-- Invoice details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Create Bill Modal -->
    <div class="modal fade" id="createBillModal" tabindex="-1" role="dialog" aria-labelledby="createBillModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" id="createBillModalLabel">Create Bill</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="createBillForm">
                    @csrf
                    <input type="hidden" id="bill_number_hidden" name="bill_number">
                    <input type="hidden" id="bill_amount_usd" name="amount_usd">
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="bill_number">Bill #</label>
                                <input type="text" class="form-control" id="bill_number" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="bill_date_display">Bill Date</label>
                                <input type="text" class="form-control" id="bill_date_display" value="{{ now()->format('d-M-Y') }}" readonly>
                                <input type="hidden" id="bill_date" name="bill_date" value="{{ now()->format('Y-m-d') }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="bill_invoice_number">Invoice #</label>
                                <input type="text" class="form-control" id="bill_invoice_number" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="bill_invoice_amount">Invoice Amount</label>
                                <input type="text" class="form-control" id="bill_invoice_amount" readonly>
                            </div>
                        </div>

                        <hr>
                        <h5><strong>Account Payable Billing Details</strong></h5>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr class="bill-row">
                                        <th>Suppliers</th>
                                        <th>ID</th>
                                        <th>Purpose</th>
                                        <th>Vat</th>
                                        <th>Vat Amount</th>
                                        <th>Total Payable</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="bill_details_table_body">
                                    <tr class="bill-row">
                                        <td>
                                            <select class="form-control select2 bill-supplier" name="supplier_id[]" style="width: 100%;">
                                                <option value="">Select Supplier</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}" data-code="{{ $supplier->supplier_code }}">{{ $supplier->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="bill-td-id">—</td>
                                        <td><input type="text" class="form-control bill-ref" name="purpose[]"></td>
                                        <td><input type="number" class="form-control bill-vat text-right" name="vat[]" value="5" readonly></td>
                                        <td><input type="text" class="form-control bill-vat-amount text-right" name="vat_amount[]" readonly></td>
                                        <td><input type="text" class="form-control bill-total text-right" name="total_payable[]"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-success btn-sm btn-add-row py-0"><i class="fas fa-plus fa-xs"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <hr>
                        <div class="form-row justify-content-end">
                            <div class="col-md-5">
                                <table class="table table-bordered table-sm" style="font-size: 14px;">
                                    <tr>
                                        <td style="width: 130px; vertical-align: middle;" class="pl-2"><strong>Total</strong></td>
                                        <td><span class="form-control form-control-sm text-right" id="bill_summary_total" style="border: none; background: transparent; display: block; pointer-events: none;">0.000</span></td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: middle;" class="pl-2"><strong>VAT(%)</strong></td>
                                        <td><input type="text" class="form-control form-control-sm text-right" id="bill_summary_vat_pct" value="5" style="border: none; background: transparent; font-weight: bold;"></td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: middle;" class="pl-2"><strong>VAT(Amount)</strong></td>
                                        <td><span class="form-control form-control-sm text-right" id="bill_summary_vat_amt" style="border: none; background: transparent; display: block; pointer-events: none;">0.000</span></td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: middle;" class="pl-2"><strong>Sub Total</strong></td>
                                        <td><span class="form-control form-control-sm text-right" id="bill_summary_subtotal" style="border: none; background: transparent; display: block; pointer-events: none;">0.000</span></td>
                                    </tr>
                                    <tr class="table-success">
                                        <td style="vertical-align: middle;" class="pl-2"><strong>Net Profit</strong></td>
                                        <td><span class="form-control form-control-sm text-right font-weight-bold" id="bill_summary_net_profit" style="border: none; background: transparent; font-size: 16px; display: block; pointer-events: none;">0.000</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Bill</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Invoice Modal -->
    <div class="modal fade" id="editInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="editInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" id="editInvoiceModalLabel">Edit Invoice</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editInvoiceForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="edit_invoice_number">Invoice #</label>
                                <input type="text" class="form-control" id="edit_invoice_number" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="edit_customer">Customer</label>
                                <input type="text" class="form-control" id="edit_customer" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="edit_vehicle">Vehicle</label>
                                <input type="text" class="form-control" id="edit_vehicle" readonly>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="edit_invoice_date">From Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_invoice_date" readonly>
                                <input type="hidden" id="edit_invoice_date_hidden" name="invoice_date">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="edit_to_date">To Date</label>
                                <input type="text" class="form-control" id="edit_to_date" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="edit_due_date">Due Date</label>
                                <input type="text" class="form-control datepicker" id="edit_due_date" name="due_date">
                            </div>
                        </div>

                        <hr>
                        <h5><strong>Pricing Details</strong></h5>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="edit_rate_type">Rate Type</label>
                                <select class="form-control pricing-input" id="edit_rate_type" name="rate_type">
                                    <option value="daily">Daily Rate</option>
                                    <option value="weekly">Weekly Rate</option>
                                    <option value="monthly">Monthly Rate</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_rate">Rate Amount</label>
                                <input type="text" class="form-control pricing-input text-right" id="edit_rate" name="rate">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_extra_kms_charges">Extra Kms Charges</label>
                                <input type="text" class="form-control pricing-input text-right" id="edit_extra_kms_charges" name="extra_kms_charges">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_security_deposit">Security Deposit</label>
                                <input type="text" class="form-control pricing-input text-right" id="edit_security_deposit" name="security_deposit">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="edit_insurance_fee">Insurance Fee</label>
                                <input type="text" class="form-control pricing-input text-right" id="edit_insurance_fee" name="insurance_fee">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_additional_driver_fee">Additional Driver Fee</label>
                                <input type="text" class="form-control pricing-input text-right" id="edit_additional_driver_fee" name="additional_driver_fee">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_delivery_charge">Delivery Charge</label>
                                <input type="text" class="form-control pricing-input text-right" id="edit_delivery_charge" name="delivery_charge">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_fuel_charge">Fuel Charge</label>
                                <input type="text" class="form-control pricing-input text-right" id="edit_fuel_charge" name="fuel_charge">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="edit_gps_charges">GPS Charges</label>
                                <input type="text" class="form-control pricing-input text-right" id="edit_gps_charges" name="gps_charges">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_salik_toll_charges">Salik/Toll Charges</label>
                                <input type="text" class="form-control pricing-input text-right" id="edit_salik_toll_charges" name="salik_toll_charges">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_vat">VAT/Tax (%) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control pricing-input text-right" id="edit_vat" name="vat" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_discount_amount">Discount Amount (%)</label>
                                <input type="text" class="form-control pricing-input text-right text-danger" id="edit_discount_amount" name="discount_amount" max="100">
                                <small id="edit_discount_error" class="text-danger d-none">Discount cannot exceed 100%.</small>
                            </div>
                        </div>

                        <div class="form-row justify-content-end">
                            <div class="col-md-5">
                                <table class="table table-bordered table-sm" style="font-size: 14px;">
                                    <tbody>
                                        <tr>
                                            <td style="width: 130px; vertical-align: middle;" class="pl-2"><strong>Sub Total</strong></td>
                                            <td>
                                                <span class="form-control form-control-sm text-right" id="edit_subtotal" style="border: none; background: transparent; font-weight: bold; display: block; pointer-events: none;">0.00</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width: 130px; vertical-align: middle;" class="pl-2"><strong>VAT Amount</strong></td>
                                            <td>
                                                <span class="form-control form-control-sm text-right" id="edit_vat_amount" style="border: none; background: transparent; font-weight: bold; display: block; pointer-events: none;">0.00</span>
                                            </td>
                                        </tr>
                                        <tr class="table-success">
                                            <td style="vertical-align: middle;" class="pl-2"><strong>Total Amount</strong></td>
                                            <td>
                                                <span class="form-control form-control-sm text-right font-weight-bold" id="edit_total_display" style="border: none; background: transparent; font-size: 16px; display: block; pointer-events: none;">0.00 OMR</span>
                                                <input type="hidden" id="edit_total" name="total" value="0.00">
                                                <small id="edit_total_zero_error" class="text-danger d-none">Total amount must be greater than zero to save.</small>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Update Invoice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .modal label:not(.form-check-label):not(.custom-file-label) {
            color: #6c757d;
            font-size: 16px;
            font-weight: 600 !important;
        }
        .bill-row td, .bill-row th {
            padding: 0.4rem 0.5rem !important;
        }
        .bill-row td:first-child {
            min-width: 300px;
        }
        .bill-row td:nth-child(4) {
            width: 80px;
            min-width: 80px;
        }
        .datepicker[readonly] {
            background-color: #ffffff;
            opacity: 1;
        }
        /* Remove number input spinners */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        var currentInvoiceId = null;

        function fmtNum(num) { return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
        function fmtNum3(num) { return num.toFixed(3).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

        function formatEditPricingInput(id) {
            if ($('#' + id).is('select')) {
                return;
            }
            var val = $('#' + id).val();
            if (val === '') return;
            var numVal = parseFloat(val.replace(/,/g, '')) || 0;
            $('#' + id).val(fmtNum(numVal));
        }

        function calculateEditInvoiceTotals() {
            var rate = parseFloat($('#edit_rate').val().replace(/,/g, '')) || 0;
            var rateType = $('#edit_rate_type').val();
            var pickupDatetime = $('#editInvoiceForm').data('pickup-datetime');
            var returnDatetime = $('#editInvoiceForm').data('return-datetime');

            var rentalCharge = 0;
            if (rate > 0 && rateType && pickupDatetime && returnDatetime) {
                var p = new Date(pickupDatetime);
                var r = new Date(returnDatetime);
                if (!isNaN(p) && !isNaN(r) && r > p) {
                    var diffMs = r - p;
                    var diffHours = Math.floor(diffMs / 3600000);
                    var days = Math.floor(diffHours / 24);
                    var hours = diffHours % 24;
                    var dayCount = days;
                    if (hours > 0 || days === 0) {
                        dayCount += 1;
                    }
                    if (rateType === 'daily') {
                        rentalCharge = rate * dayCount;
                    } else if (rateType === 'weekly') {
                        rentalCharge = rate * (dayCount / 7);
                    } else if (rateType === 'monthly') {
                        rentalCharge = rate * (dayCount / 30);
                    }
                } else {
                    rentalCharge = rate;
                }
            } else {
                rentalCharge = rate;
            }

            var extraKms = parseFloat($('#edit_extra_kms_charges').val().replace(/,/g, '')) || 0;
            var security = parseFloat($('#edit_security_deposit').val().replace(/,/g, '')) || 0;
            var insurance = parseFloat($('#edit_insurance_fee').val().replace(/,/g, '')) || 0;
            var driver = parseFloat($('#edit_additional_driver_fee').val().replace(/,/g, '')) || 0;
            var delivery = parseFloat($('#edit_delivery_charge').val().replace(/,/g, '')) || 0;
            var fuel = parseFloat($('#edit_fuel_charge').val().replace(/,/g, '')) || 0;
            var gps = parseFloat($('#edit_gps_charges').val().replace(/,/g, '')) || 0;
            var salik = parseFloat($('#edit_salik_toll_charges').val().replace(/,/g, '')) || 0;
            var discountPercent = parseFloat($('#edit_discount_amount').val().replace(/,/g, '')) || 0;
            var vatPercent = parseFloat($('#edit_vat').val().replace(/,/g, '')) || 0;

            // Calculate charges total (sum of all charges + rental charge)
            var chargesTotal = rentalCharge + extraKms + security + insurance + driver + delivery + fuel + gps + salik;
            
            // Calculate discount amount as percentage of charges total
            var discountAmount = chargesTotal * (discountPercent / 100);
            
            // Total is charges total minus discount
            var total = chargesTotal - discountAmount;
            if (total < 0) total = 0;
            // Round total to 2 decimals first
            total = Math.round(total * 100) / 100;

            // VAT Amount is calculated as percentage of rounded total
            var vatAmount = Math.round((total * (vatPercent / 100)) * 100) / 100;

            // Subtotal is rounded total minus VAT Amount (guarantees subtotal + vat = total)
            var subtotal = total - vatAmount;
            
            $('#edit_total').val(total.toFixed(2));
            $('#edit_total_display').text(fmtNum(total));
            $('#edit_vat_amount').text(fmtNum(vatAmount));
            $('#edit_subtotal').text(fmtNum(subtotal));
        }

        $(document).ready(function() {
            // Handle view invoice button click
            $(document).on('click', '.view-invoice-btn', function() {
                var url = $(this).data('url');

                // Show modal with loading state
                $('#viewInvoiceModalBody').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x text-info"></i><p class="mt-2">Loading details...</p></div>');
                $('#viewInvoiceModal').modal('show');

                // Fetch invoice details via AJAX
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(data) {
                        $('#viewInvoiceModalBody').html(data);
                    },
                    error: function() {
                        $('#viewInvoiceModalBody').html('<div class="alert alert-danger">Error loading invoice details. Please try again.</div>');
                    }
                });
            });

            // Handle create bill button click
            $(document).on('click', '.create-bill-btn', function() {
                currentInvoiceId = $(this).data('id');
                $('#bill_invoice_number').val($(this).data('invoice-number'));
                var invAmt = parseFloat($(this).data('amount')) || 0;
                $('#bill_invoice_amount').val(invAmt.toFixed(3));
                $('#bill_amount_usd').val(invAmt.toFixed(2));
                $('#createBillForm').data('amount', invAmt.toFixed(2));

                var vatPct = parseFloat($(this).data('vat')) || 0;
                var vatAmt = parseFloat($(this).data('vat-amount')) || 0;
                var subtotal = parseFloat($(this).data('subtotal')) || 0;
                var totalAmt = parseFloat($(this).data('amount')) || 0;

                // Reset table to single row
                var $tableBody = $('#bill_details_table_body');
                $tableBody.find('.bill-row:not(:first)').remove();
                var $firstRow = $tableBody.find('.bill-row:first');
                $firstRow.find('.bill-supplier').val('').trigger('change');
                $firstRow.find('.bill-td-id').text('—');
                $firstRow.find('.bill-ref').val('');
                $firstRow.find('.bill-vat').val('5');
                $firstRow.find('.bill-vat-amount').val('');
                $firstRow.find('.bill-total').val('');
                calculateBillSummary();
                
                // Get next bill number from server
                $.ajax({
                    url: '{{ route("bills.next-number") }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#bill_number').val(response.bill_number);
                            $('#bill_number_hidden').val(response.bill_number);
                        }
                    }
                });

                
                $('#createBillForm').attr('action', '{{ route('invoices.createBill', ':id') }}'.replace(':id', currentInvoiceId));
                $('#createBillModal').modal('show');

                // Initialize supplier Select2 inside the modal
                $('#bill_details_table_body .bill-supplier').select2({
                    theme: 'bootstrap4',
                    placeholder: 'Select Supplier',
                    width: '100%',
                    minimumResultsForSearch: 0
                });
            });

            function calculateBillSummary() {
                var grandTotal = 0, grandVatAmt = 0;
                $('#bill_details_table_body .bill-row').each(function() {
                    grandTotal += parseFloat($(this).find('.bill-total').val().replace(/,/g, '')) || 0;
                    grandVatAmt += parseFloat($(this).find('.bill-vat-amount').val().replace(/,/g, '')) || 0;
                });
                var invAmt = parseFloat($('#bill_invoice_amount').val().replace(/,/g, '')) || 0;
                var vatPct = parseFloat($('#bill_summary_vat_pct').val()) || 0;
                var subtotal = grandTotal - grandVatAmt;
                var netProfit = invAmt - grandTotal;
                $('#bill_summary_total').text(fmtNum3(grandTotal));
                $('#bill_summary_vat_amt').text(fmtNum3(grandVatAmt));
                $('#bill_summary_subtotal').text(fmtNum3(subtotal));
                $('#bill_summary_net_profit').text(fmtNum3(netProfit));

                // Auto-clear zero-total error when user fixes it
                if (grandTotal > 0) {
                    $('#bill_summary_total').closest('td').removeClass('table-danger');
                    $('#bill_total_error').remove();
                }
            }

            // Format Total Payable on blur and auto-calculate Vat Amount
            $(document).on('blur', '.bill-total', function() {
                var $row = $(this).closest('tr');
                var raw = $(this).val().replace(/,/g, '');
                var total = parseFloat(raw) || 0;
                $(this).val(fmtNum3(total));
                var vatPct = parseFloat($row.find('.bill-vat').val()) || 0;
                var vatAmt = total * vatPct / 100;
                $row.find('.bill-vat-amount').val(fmtNum3(vatAmt));
                calculateBillSummary();
            });

            // Recalculate summary when VAT(%) changes
            $(document).on('input', '#bill_summary_vat_pct', calculateBillSummary);

            // Update ID column when supplier is selected
            $(document).on('change', '.bill-supplier', function() {
                var code = $(this).find(':selected').data('code');
                $(this).closest('tr').find('.bill-td-id').text(code || '—');
            });

            // Add new row
            $(document).on('click', '.btn-add-row', function() {
                var $tableBody = $('#bill_details_table_body');
                var $newRow = $tableBody.find('.bill-row:first').clone();

                $newRow.find('input').val('');
                $newRow.find('.bill-td-id').text('—');
                $newRow.find('.bill-vat').val('5');
                $newRow.find('.btn-add-row')
                    .removeClass('btn-success btn-add-row')
                    .addClass('btn-danger btn-remove-row')
                    .html('<i class="fas fa-minus"></i>');

                $newRow.find('.select2-container').remove();
                $newRow.find('select').removeClass('select2-hidden-accessible').removeAttr('aria-hidden data-select2-id');

                $tableBody.append($newRow);

                calculateBillSummary();

                $newRow.find('.bill-supplier').select2({
                    theme: 'bootstrap4',
                    placeholder: 'Select Supplier',
                    width: '100%',
                    minimumResultsForSearch: 0
                });
            });

            // Remove row
            $(document).on('click', '.btn-remove-row', function() {
                $(this).closest('tr').remove();
                calculateBillSummary();
            });

            // Handle edit invoice button click
            $(document).on('click', '.edit-invoice-btn', function() {
                var url = $(this).data('url');
                currentInvoiceId = $(this).data('id');

                console.log('Edit button clicked, URL:', url, 'ID:', currentInvoiceId);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        console.log('Edit response:', response);
                        if (response.success) {
                            var invoice = response.invoice;
                            $('#edit_invoice_number').val(invoice.invoice_number);
                            $('#edit_invoice_date_hidden').val(invoice.invoice_date);
                            $('#edit_due_date').val(invoice.due_date);
                             $('#edit_customer').val(invoice.customer_name || '');
                             $('#edit_vehicle').val(invoice.vehicle_name || '');
                             
                             // Set from date from pickup_datetime
                             var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                             if (invoice.pickup_datetime) {
                                 var pd = new Date(invoice.pickup_datetime);
                                 $('#edit_invoice_date').val(pd.getDate() + ' ' + months[pd.getMonth()] + ' ' + pd.getFullYear());
                             } else {
                                 $('#edit_invoice_date').val('');
                             }
                             // Set to date from return_datetime
                             if (invoice.return_datetime) {
                                 var rd = new Date(invoice.return_datetime);
                                 $('#edit_to_date').val(rd.getDate() + ' ' + months[rd.getMonth()] + ' ' + rd.getFullYear());
                             } else {
                                 $('#edit_to_date').val('');
                             }
                             $('#edit_rate_type').val(invoice.rate_type || 'daily');
                             $('#edit_rate').val(invoice.rate ? fmtNum(parseFloat(invoice.rate)) : 0);
                             
                             $('#editInvoiceForm').data('pickup-datetime', invoice.pickup_datetime);
                             $('#editInvoiceForm').data('return-datetime', invoice.return_datetime);

                             $('#edit_extra_kms_charges').val(invoice.extra_kms_charges ? fmtNum(parseFloat(invoice.extra_kms_charges)) : 0);
                             $('#edit_security_deposit').val(invoice.security_deposit ? fmtNum(parseFloat(invoice.security_deposit)) : 0);
                             $('#edit_insurance_fee').val(invoice.insurance_fee ? fmtNum(parseFloat(invoice.insurance_fee)) : 0);
                             $('#edit_additional_driver_fee').val(invoice.additional_driver_fee ? fmtNum(parseFloat(invoice.additional_driver_fee)) : 0);
                             $('#edit_delivery_charge').val(invoice.delivery_charge ? fmtNum(parseFloat(invoice.delivery_charge)) : 0);
                             $('#edit_fuel_charge').val(invoice.fuel_charge ? fmtNum(parseFloat(invoice.fuel_charge)) : 0);
                             $('#edit_gps_charges').val(invoice.gps_charges ? fmtNum(parseFloat(invoice.gps_charges)) : 0);
                             $('#edit_salik_toll_charges').val(invoice.salik_toll_charges ? fmtNum(parseFloat(invoice.salik_toll_charges)) : 0);
                             $('#edit_vat').val(invoice.vat || 0);
                             $('#edit_discount_amount').val(invoice.discount_amount || 0);
                             $('#edit_total').val(parseFloat(invoice.total || 0));
                             $('#edit_total_display').text(fmtNum(parseFloat(invoice.total || 0)));
                             $('#edit_subtotal').text(fmtNum(parseFloat(invoice.subtotal || 0)));
                             $('#edit_vat_amount').text(fmtNum(parseFloat(invoice.vat_amount || 0)));
                             
                             $('#editInvoiceForm').attr('action', '{{ route('invoices.update', ':id') }}'.replace(':id', currentInvoiceId));
                             console.log('Showing edit modal');
                             $('#editInvoiceModal').modal('show');
                             
                             // Initialize date pickers for edit modal
                             flatpickr('#edit_due_date', {
                                 dateFormat: 'Y-m-d',
                                 allowInput: false
                             });
                             
                             // Calculate totals on load
                             calculateEditInvoiceTotals();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Edit error:', xhr, status, error);
                        alert('Error loading invoice details. Please try again.');
                    }
                });
            });

            // Clamp discount to max 100%
            $(document).on('input', '#edit_discount_amount', function() {
                var val = parseFloat($(this).val()) || 0;
                if (val > 100) {
                    $(this).val(100);
                    $('#edit_discount_error').removeClass('d-none');
                } else {
                    $('#edit_discount_error').addClass('d-none');
                }
            });

            // Calculate totals when pricing fields change in edit modal
            $(document).on('input', '.pricing-input', calculateEditInvoiceTotals);

            // Format pricing inputs on blur
            $(document).on('blur', '.pricing-input', function() {
                formatEditPricingInput($(this).attr('id'));
            });

            // Handle edit invoice form submission
            $('#editInvoiceForm').on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Validate total amount is not zero
                var totalVal = parseFloat($('#edit_total').val()) || 0;
                if (totalVal <= 0) {
                    $('#edit_total_zero_error').removeClass('d-none');
                    return false;
                }
                $('#edit_total_zero_error').addClass('d-none');

                // Strip commas from all pricing-input fields before submission
                $('.pricing-input').each(function() {
                    var val = $(this).val();
                    if (val) {
                        $(this).val(val.replace(/,/g, ''));
                    }
                });

                var form = $(this);
                var submitBtn = form.find('button[type="submit"]');
                var originalBtnHtml = submitBtn.html();

                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();

                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: new FormData(form[0]),
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        submitBtn.prop('disabled', false).html(originalBtnHtml);

                        if (response.success) {
                            $('#editInvoiceModal').modal('hide');
                            form[0].reset();
                            currentInvoiceId = null;
                            $('#invoices-table').DataTable().ajax.reload(null, false);

                            $('.alert').remove();
                            var alertHtml = '<div class="alert alert-success alert-dismissible fade show">' +
                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                                '<h5><i class="icon fas fa-check"></i> Success!</h5>' +
                                response.message +
                                '</div>';
                            $('.row').first().before(alertHtml);

                            setTimeout(function() {
                                $('.alert-success').fadeOut('slow', function() {
                                    $(this).remove();
                                });
                            }, 5000);
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html(originalBtnHtml);

                        if (xhr.status === 422) {
                            var response = xhr.responseJSON;
                            if (response.errors) {
                                $.each(response.errors, function(field, messages) {
                                    var input = form.find('[name="' + field + '"]');
                                    if (input.length) {
                                        input.addClass('is-invalid');
                                        input.after($('<span class="invalid-feedback d-block"></span>').text(messages[0]));
                                    }
                                });
                            } else if (response.message) {
                                $('.alert').remove();
                                $('.row').first().before(
                                    '<div class="alert alert-danger alert-dismissible fade show">' +
                                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                                    '<h5><i class="icon fas fa-ban"></i> Error!</h5>' + response.message +
                                    '</div>'
                                );
                            }
                        }
                    }
                });

                return false;
            });

            // Handle delete invoice button click
            $(document).on('click', '.delete-invoice-btn', function() {
                var url = $(this).data('url');
                var invoiceId = $(this).data('id');

                if (confirm('Are you sure you want to delete this invoice?')) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#invoices-table').DataTable().ajax.reload(null, false);

                                $('.alert').remove();
                                var alertHtml = '<div class="alert alert-success alert-dismissible fade show">' +
                                    '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                                    '<h5><i class="icon fas fa-check"></i> Success!</h5>' +
                                    response.message +
                                    '</div>';
                                $('.row').first().before(alertHtml);

                                setTimeout(function() {
                                    $('.alert-success').fadeOut('slow', function() {
                                        $(this).remove();
                                    });
                                }, 5000);
                            }
                        },
                        error: function() {
                            alert('Error deleting invoice. Please try again.');
                        }
                    });
                }
            });

            // Handle create bill form submission
            $('#createBillForm').on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var form = $(this);
                var submitBtn = form.find('button[type="submit"]');
                var originalBtnHtml = submitBtn.html();

                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();

                // Validate that Total is not zero
                var totalText = $('#bill_summary_total').text().trim();
                var totalValue = parseFloat(totalText) || 0;
                if (totalValue <= 0) {
                    form.find('#bill_summary_total').closest('td').addClass('table-danger');
                    var $errorMsg = $('#bill_total_error');
                    if ($errorMsg.length === 0) {
                        $('#bill_summary_total').closest('table').after(
                            '<p id="bill_total_error" class="text-danger mt-1 text-right"><i class="fas fa-exclamation-circle"></i> Total cannot be zero. Please enter at least one billing detail.</p>'
                        );
                    }
                    return false;
                } else {
                    $('#bill_summary_total').closest('td').removeClass('table-danger');
                    $('#bill_total_error').remove();
                }

                // Strip commas from formatted number fields before submission
                form.find('.bill-total, .bill-vat-amount').each(function() {
                    var val = $(this).val();
                    if (val) {
                        $(this).val(val.replace(/,/g, ''));
                    }
                });

                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: new FormData(form[0]),
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        submitBtn.prop('disabled', false).html(originalBtnHtml);

                        if (response.success) {
                            $('#createBillModal').modal('hide');
                            form[0].reset();
                            currentInvoiceId = null;
                            $('#invoices-table').DataTable().ajax.reload(null, false);

                            $('.alert').remove();
                            var alertHtml = '<div class="alert alert-success alert-dismissible fade show">' +
                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                                '<h5><i class="icon fas fa-check"></i> Success!</h5>' +
                                response.message +
                                '</div>';
                            $('.row').first().before(alertHtml);

                            // Auto dismiss after 5 seconds
                            setTimeout(function() {
                                $('.alert-success').fadeOut('slow', function() {
                                    $(this).remove();
                                });
                            }, 5000);
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html(originalBtnHtml);

                        if (xhr.status === 422) {
                            var response = xhr.responseJSON;
                            if (response.errors) {
                                $.each(response.errors, function(field, messages) {
                                    var input = form.find('[name="' + field + '"]');
                                    if (input.length) {
                                        input.addClass('is-invalid');
                                        input.after($('<span class="invalid-feedback d-block"></span>').text(messages[0]));
                                    }
                                });
                            } else if (response.message) {
                                $('.alert').remove();
                                $('.row').first().before(
                                    '<div class="alert alert-danger alert-dismissible fade show">' +
                                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                                    '<h5><i class="icon fas fa-ban"></i> Error!</h5>' + response.message +
                                    '</div>'
                                );
                            }
                        }
                    }
                });

                return false;
            });

            var invoiceTable = $('#invoices-table').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('invoices.data') }}",
                    "type": "GET",
                    "data": function(d) {
                        d.filter_invoice = $('#filter_invoice').val();
                        d.filter_customer = $('#filter_customer').val();
                        d.filter_vehicle = $('#filter_vehicle').val();
                        d.filter_from_date = $('#filter_from_date').val();
                        d.filter_to_date = $('#filter_to_date').val();
                    }
                },
                "columns": [
                    { "data": "id", "orderable": true },
                    { "data": "invoice_number", "orderable": true },
                    { "data": "customer", "orderable": false },
                    { "data": "vehicle", "orderable": false },
                    { "data": "booking_from_date", "orderable": true },
                    { "data": "booking_to_date", "orderable": true },
                    { 
                        "data": "amount", 
                        "orderable": true, 
                        "className": "text-right",
                        "createdCell": function (cell, cellData, rowData, row, col) {
                            $(cell).css('text-align', 'right');
                        }
                    },
                    { "data": "status", "orderable": true },
                    { "data": "actions", "orderable": false, "searchable": false }
                ],
                "responsive": true,
                "autoWidth": false,
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "dom": 'lfrtip',
                "searching": false,
                "language": {
                    "processing": "<i class='fas fa-spinner fa-spin'></i> Loading...",
                    "lengthMenu": 'Show _MENU_ entries',
                    "info": 'Showing _START_ to _END_ of _TOTAL_ invoices',
                    "infoEmpty": 'No invoices found',
                    "infoFiltered": '(filtered from _MAX_ total invoices)',
                    "zeroRecords": 'No matching invoices found'
                },
                "order": [[0, 'desc']]
            });

            invoiceTable.on('draw.dt', function() {
                $('[data-toggle="tooltip"]', $('#invoices-table')).tooltip({ trigger: 'hover' });
            });

            // Initialize filter Select2
            $('#filter_invoice').select2({
                theme: 'bootstrap4',
                placeholder: 'Invoice',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 0
            });
            $('#filter_customer').select2({
                theme: 'bootstrap4',
                placeholder: 'Customer',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 0
            });
            $('#filter_vehicle').select2({
                theme: 'bootstrap4',
                placeholder: 'Vehicle',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 0
            });
            // Initialize filter date pickers
            flatpickr('#filter_from_date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
                onChange: function() { invoiceTable.draw(); }
            });
            flatpickr('#filter_to_date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
                onChange: function() { invoiceTable.draw(); }
            });

            // Trigger table draw on filter change
            $('#filter_invoice, #filter_customer, #filter_vehicle').on('change', function() {
                invoiceTable.draw();
            });
        });
    </script>
@stop
