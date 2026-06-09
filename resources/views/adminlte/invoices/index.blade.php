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
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->name }} ({{ $vehicle->registration_number }})</option>
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
        <div class="modal-dialog modal-lg" role="document">
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
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="bill_number">Bill #</label>
                                <input type="text" class="form-control" id="bill_number" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="bill_date">Bill Date</label>
                                <input type="text" class="form-control" id="bill_date" name="bill_date" value="{{ now()->format('Y-m-d') }}" readonly required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="bill_invoice_number">Invoice #</label>
                                <input type="text" class="form-control" id="bill_invoice_number" readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="bill_customer">Customer</label>
                                <input type="text" class="form-control" id="bill_customer" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="bill_amount_usd">Amount (USD)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="bill_amount_usd" name="amount_usd" readonly required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="bill_amount_omr">Amount (OMR)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="bill_amount_omr" readonly>
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
                            <div class="form-group col-md-2">
                                <label for="edit_invoice_number">Invoice #</label>
                                <input type="text" class="form-control" id="edit_invoice_number" readonly>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="edit_invoice_date">Invoice Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control datepicker" id="edit_invoice_date" name="invoice_date" required>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="edit_due_date">Due Date</label>
                                <input type="text" class="form-control datepicker" id="edit_due_date" name="due_date">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_customer">Customer</label>
                                <input type="text" class="form-control" id="edit_customer" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_vehicle">Vehicle</label>
                                <input type="text" class="form-control" id="edit_vehicle" readonly>
                            </div>
                        </div>

                        <hr>
                        <h5><strong>Pricing Details</strong></h5>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="edit_rate_type">Rate Type</label>
                                <select class="form-control" id="edit_rate_type" name="rate_type">
                                    <option value="daily">Daily Rate</option>
                                    <option value="weekly">Weekly Rate</option>
                                    <option value="monthly">Monthly Rate</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_extra_kms_charges">Extra Kms Charges</label>
                                <input type="text" step="0.01" min="0" class="form-control pricing-input text-right" id="edit_extra_kms_charges" name="extra_kms_charges">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_security_deposit">Security Deposit</label>
                                <input type="text" step="0.01" min="0" class="form-control pricing-input text-right" id="edit_security_deposit" name="security_deposit">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_insurance_fee">Insurance Fee</label>
                                <input type="text" step="0.01" min="0" class="form-control pricing-input text-right" id="edit_insurance_fee" name="insurance_fee">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="edit_additional_driver_fee">Additional Driver Fee</label>
                                <input type="text" step="0.01" min="0" class="form-control pricing-input text-right" id="edit_additional_driver_fee" name="additional_driver_fee">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_delivery_charge">Delivery Charge</label>
                                <input type="text" step="0.01" min="0" class="form-control pricing-input text-right" id="edit_delivery_charge" name="delivery_charge">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_fuel_charge">Fuel Charge</label>
                                <input type="text" step="0.01" min="0" class="form-control pricing-input text-right" id="edit_fuel_charge" name="fuel_charge">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_gps_charges">GPS Charges</label>
                                <input type="text" step="0.01" min="0" class="form-control pricing-input text-right" id="edit_gps_charges" name="gps_charges">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="edit_salik_toll_charges">Salik/Toll Charges</label>
                                <input type="text" step="0.01" min="0" class="form-control pricing-input text-right" id="edit_salik_toll_charges" name="salik_toll_charges">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="edit_vat">VAT/Tax (%) <span class="text-danger">*</span></label>
                                <input type="text" step="0.01" min="0" class="form-control pricing-input text-right" id="edit_vat" name="vat" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="edit_discount_amount">Discount Amount</label>
                                <input type="text" step="0.01" min="0" class="form-control pricing-input text-right text-danger" id="edit_discount_amount" name="discount_amount" max="100">
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
                                                <span class="form-control form-control-sm text-right font-weight-bold" id="edit_total_display" style="border: none; background: transparent; font-size: 16px; display: block; pointer-events: none;">0.00</span>
                                                <input type="hidden" id="edit_total" name="total" value="0.00">
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

        function calculateBillOMRTotal() {
            var amountUSD = parseFloat($('#bill_amount_usd').val()) || 0;
            var exchangeRate = 0.385; // Fixed exchange rate
            var amountOMR = amountUSD * exchangeRate;
            $('#bill_amount_omr').val(amountOMR.toFixed(2));
        }

        function fmtNum(num) { return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

        function formatEditPricingInput(id) {
            var val = $('#' + id).val();
            if (val === '') return;
            var numVal = parseFloat(val.replace(/,/g, '')) || 0;
            $('#' + id).val(fmtNum(numVal));
        }

        function calculateEditInvoiceTotals() {
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

            // Calculate charges total (sum of all charges)
            var chargesTotal = extraKms + security + insurance + driver + delivery + fuel + gps + salik;
            
            // Calculate discount amount as percentage of charges total
            var discountAmount = chargesTotal * (discountPercent / 100);
            
            // Total is charges total minus discount
            var total = chargesTotal - discountAmount;
            if (total < 0) total = 0;

            // VAT Amount is calculated as percentage of total
            var vatAmount = total * (vatPercent / 100);

            // Subtotal is total minus VAT Amount
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
                $('#bill_customer').val($(this).data('customer'));
                $('#bill_amount_usd').val(parseFloat($(this).data('amount')).toFixed(2));
                calculateBillOMRTotal();
                
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
            });

            // Recalculate OMR total when amount USD changes
            $(document).on('input', '#bill_amount_usd', calculateBillOMRTotal);

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
                            $('#edit_invoice_date').val(invoice.invoice_date);
                            $('#edit_due_date').val(invoice.due_date);
                            $('#edit_customer').val(invoice.customer_name || '');
                            $('#edit_vehicle').val(invoice.vehicle_name || '');
                            $('#edit_rate_type').val(invoice.rate_type || 'daily');
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
                            flatpickr('#edit_invoice_date', {
                                dateFormat: 'Y-m-d',
                                allowInput: false
                            });
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
