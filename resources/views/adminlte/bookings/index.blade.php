@extends('adminlte::page')
@section('plugins.Select2', true)

@section('title', 'Bookings')

@section('content_header')
    <h1>Bookings Management</h1>
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
                    <h3 class="card-title">Bookings List</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addBookingModal">
                            <i class="fas fa-plus"></i> Add Booking
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="bookings-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SI</th>
                                <th>Booking ID</th>
                                <th>Vehicle</th>
                                <th>Customer</th>
                                <th>Dates</th>
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

    <div class="modal fade" id="addBookingModal" tabindex="-1" role="dialog" aria-labelledby="addBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" id="addBookingModalLabel">Add New Booking</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addBookingForm" action="{{ route('bookings.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="vehicle_id">Vehicle</label>
                                <select class="form-control @error('vehicle_id') is-invalid @enderror" id="vehicle_id" name="vehicle_id" required>
                                    <option value="">Select Vehicle</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">{{ $vehicle->name }} ({{ $vehicle->registration_number }})</option>
                                    @endforeach
                                </select>
                                @error('vehicle_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="customer_id">Customer</label>
                                <select class="form-control select2 @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required style="width: 100%;">
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="from_date">From Date</label>
                                <input type="text" class="form-control datepicker @error('from_date') is-invalid @enderror" id="from_date" name="from_date" value="{{ old('from_date') }}" required>
                                @error('from_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="to_date">To Date</label>
                                <input type="text" class="form-control datepicker @error('to_date') is-invalid @enderror" id="to_date" name="to_date" value="{{ old('to_date') }}" required>
                                @error('to_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="status">Status</label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="on_hold">On Hold</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="notes">Remark</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="1">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="payment_type">Payment Type</label>
                                <select class="form-control @error('payment_type') is-invalid @enderror" id="payment_type" name="payment_type">
                                    <option value="">Select Payment Type</option>
                                    <option value="card" {{ old('payment_type') == 'card' ? 'selected' : '' }}>Card</option>
                                    <option value="email_credit" {{ old('payment_type') == 'email_credit' ? 'selected' : '' }}>Email Credit</option>
                                    <option value="lpo" {{ old('payment_type') == 'lpo' ? 'selected' : '' }}>LPO</option>
                                    <option value="cash" {{ old('payment_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                                </select>
                                @error('payment_type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Customer Details Panel --}}
                        <div class="form-row" id="add_customer_details_row" style="display:none;">
                            <div class="col-md-12">
                                <div class="customer-details-card">
                                    <div class="customer-details-header">
                                        <i class="fas fa-user-circle mr-1"></i>
                                        <span id="add_cd_name"></span>
                                        <span class="customer-type-badge" id="add_cd_type"></span>
                                    </div>
                                    <div class="customer-details-body">
                                        <div class="cd-item" id="add_cd_phone_wrap">
                                            <i class="fas fa-phone-alt"></i>
                                            <span id="add_cd_phone"></span>
                                        </div>
                                        <div class="cd-item" id="add_cd_address_wrap">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span id="add_cd_address"></span>
                                        </div>
                                        <div class="cd-item" id="add_cd_idcard_wrap">
                                            <i class="fas fa-id-card"></i>
                                            <span id="add_cd_idcard"></span>
                                        </div>
                                        <div class="cd-item" id="add_cd_company_wrap">
                                            <i class="fas fa-building"></i>
                                            <span id="add_cd_company"></span>
                                        </div>
                                        <div class="cd-item" id="add_cd_regno_wrap">
                                            <i class="fas fa-file-alt"></i>
                                            <span id="add_cd_regno"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editBookingModal" tabindex="-1" role="dialog" aria-labelledby="editBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" id="editBookingModalLabel">Edit Booking</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editBookingForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="edit_vehicle_id">Vehicle</label>
                                <select class="form-control" id="edit_vehicle_id" name="vehicle_id" required>
                                    <option value="">Select Vehicle</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">{{ $vehicle->name }} ({{ $vehicle->registration_number }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="edit_customer_id">Customer</label>
                                <select class="form-control select2" id="edit_customer_id" name="customer_id" required style="width: 100%;">
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="edit_from_date">From Date</label>
                                <input type="text" class="form-control datepicker" id="edit_from_date" name="from_date" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="edit_to_date">To Date</label>
                                <input type="text" class="form-control datepicker" id="edit_to_date" name="to_date" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="edit_status">Status</label>
                                <select class="form-control" id="edit_status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="on_hold">On Hold</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="edit_notes">Remark</label>
                                <textarea class="form-control" id="edit_notes" name="notes" rows="1"></textarea>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="edit_payment_type">Payment Type</label>
                                <select class="form-control" id="edit_payment_type" name="payment_type">
                                    <option value="">Select Payment Type</option>
                                    <option value="card">Card</option>
                                    <option value="email_credit">Email Credit</option>
                                    <option value="lpo">LPO</option>
                                    <option value="cash">Cash</option>
                                </select>
                            </div>
                        </div>

                        {{-- Customer Details Panel (Edit Modal) --}}
                        <div class="form-row" id="edit_customer_details_row" style="display:none;">
                            <div class="col-md-12">
                                <div class="customer-details-card">
                                    <div class="customer-details-header">
                                        <i class="fas fa-user-circle mr-1"></i>
                                        <span id="edit_cd_name"></span>
                                        <span class="customer-type-badge" id="edit_cd_type"></span>
                                    </div>
                                    <div class="customer-details-body">
                                        <div class="cd-item" id="edit_cd_phone_wrap">
                                            <i class="fas fa-phone-alt"></i>
                                            <span id="edit_cd_phone"></span>
                                        </div>
                                        <div class="cd-item" id="edit_cd_address_wrap">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span id="edit_cd_address"></span>
                                        </div>
                                        <div class="cd-item" id="edit_cd_idcard_wrap">
                                            <i class="fas fa-id-card"></i>
                                            <span id="edit_cd_idcard"></span>
                                        </div>
                                        <div class="cd-item" id="edit_cd_company_wrap">
                                            <i class="fas fa-building"></i>
                                            <span id="edit_cd_company"></span>
                                        </div>
                                        <div class="cd-item" id="edit_cd_regno_wrap">
                                            <i class="fas fa-file-alt"></i>
                                            <span id="edit_cd_regno"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Update Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewBookingModal" tabindex="-1" role="dialog" aria-labelledby="viewBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" id="viewBookingModalLabel">View Booking Details</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="viewBookingContent">
                    <!-- Booking details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="createInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" id="createInvoiceModalLabel">Create Invoice for Booking</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="createInvoiceForm" action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="invoice_booking_id">Booking ID</label>
                                <input type="text" class="form-control" id="invoice_booking_id" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="invoice_vehicle">Vehicle</label>
                                <input type="text" class="form-control" id="invoice_vehicle" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="invoice_customer">Customer</label>
                                <input type="text" class="form-control" id="invoice_customer" readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="booking_from_date">Booking From Date</label>
                                <input type="text" class="form-control" id="booking_from_date" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="invoice_date_display">Invoice Date</label>
                                <input type="text" class="form-control" id="invoice_date_display" value="{{ now()->format('d M Y') }}" readonly>
                                <input type="hidden" id="invoice_date" name="invoice_date" value="{{ now()->format('Y-m-d') }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="invoice_due_date">Inv Due</label>
                                <input type="text" class="form-control datepicker @error('due_date') is-invalid @enderror"
                                       id="invoice_due_date" name="due_date" value="{{ old('due_date') }}">
                                @error('due_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="invoice_description">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="invoice_description" name="description" rows="2">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-12 text-right">
                                <table class="table table-bordered table-sm" style="max-width: 320px; margin-left: auto; font-size: 14px;">
                                    <tr>
                                        <td style="width: 120px; text-align: center;"><strong style="color: #6c757d;">Total</strong></td>
                                        <td class="text-right">
                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right @error('total') is-invalid @enderror"
                                                   id="invoice_total" name="total" required style="width: 100%; border: none; background: transparent; -moz-appearance: textfield;">
                                            @error('total')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 120px; text-align: center;"><strong style="color: #6c757d;">VAT (%)</strong></td>
                                        <td class="text-right">
                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right @error('vat') is-invalid @enderror"
                                                   id="invoice_vat" name="vat" value="5" style="width: 100%; border: none; background: transparent; -moz-appearance: textfield;">
                                            @error('vat')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 120px; text-align: center;"><strong style="color: #6c757d;">Sub Total</strong></td>
                                        <td class="text-right">
                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right @error('subtotal') is-invalid @enderror"
                                                   id="invoice_subtotal" name="subtotal" readonly style="width: 100%; border: none; background: transparent; -moz-appearance: textfield;">
                                            @error('subtotal')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 120px; text-align: center;"><strong style="color: #6c757d;">VAT Amt</strong></td>
                                        <td class="text-right">
                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right"
                                                   id="invoice_vat_amount" readonly style="width: 100%; border: none; background: transparent; -moz-appearance: textfield;">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Invoice</button>
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
        /* Hide number input spinner arrows */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {-moz-appearance: textfield;}

        /* Customer Details Card */
        .customer-details-card {
            border: 1px solid #28a745;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        .customer-details-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #fff;
            padding: 8px 14px;
            font-size: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .customer-type-badge {
            margin-left: auto;
            background: rgba(255,255,255,0.25);
            border-radius: 20px;
            padding: 2px 10px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }
        .customer-details-body {
            background: #f8fff9;
            padding: 10px 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px 20px;
        }
        .cd-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #495057;
        }
        .cd-item i {
            color: #28a745;
            width: 14px;
            text-align: center;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                $('#addBookingModal').modal('show');
            });
        </script>
    @endif
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        // Initialize DataTable with AJAX
        $(document).ready(function() {
            var bookingToday = new Date();
            bookingToday.setHours(0, 0, 0, 0);

            function destroyFlatpickr(selector) {
                var el = document.querySelector(selector);
                if (el && el._flatpickr) {
                    el._flatpickr.destroy();
                }
            }

            function initBookingDatePickers(fromSelector, toSelector, fromDate, toDate) {
                destroyFlatpickr(fromSelector);
                destroyFlatpickr(toSelector);

                var toPicker = flatpickr(toSelector, {
                    dateFormat: "Y-m-d",
                    allowInput: false,
                    minDate: bookingToday,
                    defaultDate: toDate || null
                });

                flatpickr(fromSelector, {
                    dateFormat: "Y-m-d",
                    allowInput: false,
                    minDate: bookingToday,
                    defaultDate: fromDate || null,
                    onChange: function(selectedDates) {
                        var minToDate = selectedDates[0] || bookingToday;
                        toPicker.set('minDate', minToDate);
                        if (toPicker.selectedDates[0] && toPicker.selectedDates[0] < minToDate) {
                            toPicker.setDate(minToDate);
                        }
                    }
                });
            }

            initBookingDatePickers('#from_date', '#to_date');

            // Initialize Select2 for Customer inside the Add Booking Modal
            $('#addBookingModal #customer_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Select Customer',
                allowClear: true,
                dropdownParent: $('#addBookingModal'),
                width: '100%'
            });

            // Initialize Select2 for Customer inside the Edit Booking Modal
            $('#editBookingModal #edit_customer_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Select Customer',
                allowClear: true,
                dropdownParent: $('#editBookingModal'),
                width: '100%'
            });

            // Auto-focus Select2 search input when dropdown opens
            $(document).on('select2:open', function(e) {
                window.setTimeout(function () {
                    var searchField = document.querySelector('.select2-container--open .select2-search__field');
                    if (searchField) {
                        searchField.focus();
                    }
                }, 50);
            });

            // Customer details URL template
            var customerDetailsUrl = '{{ route("customers.details", ":id") }}';

            function loadCustomerDetails(customerId, prefix) {
                var detailsRow = $('#' + prefix + '_customer_details_row');
                if (!customerId) {
                    detailsRow.hide();
                    return;
                }
                var url = customerDetailsUrl.replace(':id', customerId);
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            var c = response.customer;
                            $('#' + prefix + '_cd_name').text(c.name || '');
                            $('#' + prefix + '_cd_type').text(c.customer_type ? c.customer_type.replace('_', ' ') : '');

                            if (c.phone_number) {
                                $('#' + prefix + '_cd_phone').text(c.phone_number);
                                $('#' + prefix + '_cd_phone_wrap').show();
                            } else {
                                $('#' + prefix + '_cd_phone_wrap').hide();
                            }
                            if (c.address) {
                                $('#' + prefix + '_cd_address').text(c.address);
                                $('#' + prefix + '_cd_address_wrap').show();
                            } else {
                                $('#' + prefix + '_cd_address_wrap').hide();
                            }
                            if (c.id_card_number) {
                                $('#' + prefix + '_cd_idcard').text('ID: ' + c.id_card_number);
                                $('#' + prefix + '_cd_idcard_wrap').show();
                            } else {
                                $('#' + prefix + '_cd_idcard_wrap').hide();
                            }
                            if (c.company_name) {
                                $('#' + prefix + '_cd_company').text(c.company_name);
                                $('#' + prefix + '_cd_company_wrap').show();
                            } else {
                                $('#' + prefix + '_cd_company_wrap').hide();
                            }
                            if (c.company_registration_id) {
                                $('#' + prefix + '_cd_regno').text('Reg: ' + c.company_registration_id);
                                $('#' + prefix + '_cd_regno_wrap').show();
                            } else {
                                $('#' + prefix + '_cd_regno_wrap').hide();
                            }

                            detailsRow.slideDown(200);
                        }
                    }
                });
            }

            // Listen for customer selection changes
            $('#addBookingModal #customer_id').on('change', function() {
                loadCustomerDetails($(this).val(), 'add');
            });
            $('#editBookingModal #edit_customer_id').on('change', function() {
                loadCustomerDetails($(this).val(), 'edit');
            });

            $('#bookings-table').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('bookings.data') }}",
                    "type": "GET"
                },
                "columns": [
                    { "data": "id", "orderable": true },
                    { "data": "booking_id", "orderable": true },
                    { "data": "vehicle", "orderable": false },
                    { "data": "customer", "orderable": false },
                    { "data": "dates", "orderable": false },
                    { "data": "status", "orderable": true },
                    { "data": "actions", "orderable": false, "searchable": false }
                ],
                "responsive": true,
                "autoWidth": false,
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "language": {
                    "processing": "<i class='fas fa-spinner fa-spin'></i> Loading...",
                    "search": "Search bookings:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ bookings",
                    "infoEmpty": "No bookings found",
                    "infoFiltered": "(filtered from _MAX_ total bookings)",
                    "zeroRecords": "No matching bookings found"
                },
                "order": [[0, "desc"]] // Default sort by SI descending
            });

            // AJAX form submission for add booking modal
            $('#addBookingForm').on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var form = $(this);
                var url = form.attr('action');
                var method = form.attr('method');
                var formData = new FormData(form[0]);
                var submitBtn = form.find('button[type="submit"]');
                var originalBtnHtml = submitBtn.html();

                // Clear previous validation styling and errors
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();

                // Set loading state on submit button
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        submitBtn.prop('disabled', false).html(originalBtnHtml);

                        if (response.success) {
                            $('#addBookingModal').modal('hide');
                            form[0].reset();
                            form.find('.select2').val(null).trigger('change');
                            $('#bookings-table').DataTable().ajax.reload();

                            // Display dynamic success alert at the top of the content
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
                            // Show inline validation errors
                            if (response.errors) {
                                $.each(response.errors, function(field, messages) {
                                    var input = form.find('[name="' + field + '"]');
                                    if (input.length) {
                                        input.addClass('is-invalid');
                                        var errorSpan = $('<span class="invalid-feedback d-block"></span>').text(messages[0]);
                                        if (input.hasClass('select2-hidden-accessible')) {
                                            input.next('.select2-container').after(errorSpan);
                                        } else {
                                            input.after(errorSpan);
                                        }
                                    }
                                });
                            } else if (response.message) {
                                // Show alert if no field-specific errors
                                $('.alert').remove();
                                var alertHtml = '<div class="alert alert-danger alert-dismissible fade show">' +
                                    '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                                    '<h5><i class="icon fas fa-ban"></i> Error!</h5>' +
                                    response.message +
                                    '</div>';
                                $('.row').first().before(alertHtml);

                                setTimeout(function() {
                                    $('.alert-danger').fadeOut('slow', function() {
                                        $(this).remove();
                                    });
                                }, 5000);
                            }
                        } else {
                            $('.alert').remove();
                            var alertHtml = '<div class="alert alert-danger alert-dismissible fade show">' +
                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                                '<h5><i class="icon fas fa-ban"></i> Error!</h5>' +
                                'An error occurred. Please try again.' +
                                '</div>';
                            $('.row').first().before(alertHtml);

                            setTimeout(function() {
                                $('.alert-danger').fadeOut('slow', function() {
                                    $(this).remove();
                                });
                            }, 5000);
                        }
                    }
                });
                return false;
            });

            // AJAX form submission for edit booking modal
            $('#editBookingForm').on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var form = $(this);
                var url = form.attr('action');
                var method = form.attr('method');
                var formData = new FormData(form[0]);
                var submitBtn = form.find('button[type="submit"]');
                var originalBtnHtml = submitBtn.html();

                // Clear previous validation styling and errors
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();

                // Set loading state on submit button
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        submitBtn.prop('disabled', false).html(originalBtnHtml);

                        if (response.success) {
                            $('#editBookingModal').modal('hide');
                            form[0].reset();
                            $('#bookings-table').DataTable().ajax.reload();

                            // Display dynamic success alert at the top of the content
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
                            // Show inline validation errors
                            if (response.errors) {
                                $.each(response.errors, function(field, messages) {
                                    var input = form.find('[name="' + field + '"]');
                                    if (input.length) {
                                        input.addClass('is-invalid');
                                        var errorSpan = $('<span class="invalid-feedback d-block"></span>').text(messages[0]);
                                        if (input.hasClass('select2-hidden-accessible')) {
                                            input.next('.select2-container').after(errorSpan);
                                        } else {
                                            input.after(errorSpan);
                                        }
                                    }
                                });
                            } else if (response.message) {
                                // Show alert if no field-specific errors
                                $('.alert').remove();
                                var alertHtml = '<div class="alert alert-danger alert-dismissible fade show">' +
                                    '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                                    '<h5><i class="icon fas fa-ban"></i> Error!</h5>' +
                                    response.message +
                                    '</div>';
                                $('.row').first().before(alertHtml);

                                setTimeout(function() {
                                    $('.alert-danger').fadeOut('slow', function() {
                                        $(this).remove();
                                    });
                                }, 5000);
                            }
                        } else {
                            $('.alert').remove();
                            var alertHtml = '<div class="alert alert-danger alert-dismissible fade show">' +
                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                                '<h5><i class="icon fas fa-ban"></i> Error!</h5>' +
                                'An error occurred. Please try again.' +
                                '</div>';
                            $('.row').first().before(alertHtml);

                            setTimeout(function() {
                                $('.alert-danger').fadeOut('slow', function() {
                                    $(this).remove();
                                });
                            }, 5000);
                        }
                    }
                });
                return false;
            });

            // Handle edit button click to load booking data
            $(document).on('click', '.edit-booking-btn', function() {
                var hasInvoice = $(this).attr('data-has-invoice');
                if (hasInvoice === "true") {
                    $('.alert').remove();
                    var alertHtml = '<div class="alert alert-danger alert-dismissible fade show">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                        '<h5><i class="icon fas fa-ban"></i> Error!</h5>' +
                        'Booking cannot be edited as it has an invoice.' +
                        '</div>';
                    $('.row').first().before(alertHtml);

                    setTimeout(function() {
                        $('.alert-danger').fadeOut('slow', function() {
                            $(this).remove();
                        });
                    }, 5000);
                    return;
                }

                var bookingId = $(this).data('id');
                var url = "{{ route('bookings.get-data', ':id') }}".replace(':id', bookingId);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            var booking = response.booking;
                            $('#editBookingForm').attr('action', "{{ route('bookings.update', ':id') }}".replace(':id', booking.id));
                            $('#edit_vehicle_id').val(booking.vehicle_id);
                            $('#edit_customer_id').val(booking.customer_id).trigger('change');
                            $('#edit_status').val(booking.status);
                            $('#edit_notes').val(booking.notes);
                            $('#edit_payment_type').val(booking.payment_type);

                            initBookingDatePickers('#edit_from_date', '#edit_to_date', booking.from_date, booking.to_date);

                            $('#editBookingModal').modal('show');
                        }
                    },
                    error: function() {
                        alert('Failed to load booking data.');
                    }
                });
            });

            // Handle view button click to load booking details
            $(document).on('click', '.view-booking-btn', function() {
                var url = $(this).data('url');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#viewBookingContent').html(response);
                        $('#viewBookingModal').modal('show');
                    },
                    error: function() {
                        $('.alert').remove();
                        var alertHtml = '<div class="alert alert-danger alert-dismissible fade show">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                            '<h5><i class="icon fas fa-ban"></i> Error!</h5>' +
                            'Failed to load booking details.' +
                            '</div>';
                        $('.row').first().before(alertHtml);

                        setTimeout(function() {
                            $('.alert-danger').fadeOut('slow', function() {
                                $(this).remove();
                            });
                        }, 5000);
                    }
                });
            });

            // Handle delete button click
            $(document).on('click', '.delete-booking-btn', function() {
                var hasInvoice = $(this).attr('data-has-invoice');
                if (hasInvoice === "true") {
                    $('.alert').remove();
                    var alertHtml = '<div class="alert alert-danger alert-dismissible fade show">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                        '<h5><i class="icon fas fa-ban"></i> Error!</h5>' +
                        'Booking cannot be deleted as it has an invoice.' +
                        '</div>';
                    $('.row').first().before(alertHtml);

                    setTimeout(function() {
                        $('.alert-danger').fadeOut('slow', function() {
                            $(this).remove();
                        });
                    }, 5000);
                    return;
                }

                if (!confirm('Are you sure you want to delete this booking?')) {
                    return;
                }

                var url = $(this).data('url');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#bookings-table').DataTable().ajax.reload();

                            // Display dynamic success alert at the top of the content
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
                    error: function() {
                        $('.alert').remove();
                        var alertHtml = '<div class="alert alert-danger alert-dismissible fade show">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                            '<h5><i class="icon fas fa-ban"></i> Error!</h5>' +
                            'Failed to delete booking.' +
                            '</div>';
                        $('.row').first().before(alertHtml);

                        setTimeout(function() {
                            $('.alert-danger').fadeOut('slow', function() {
                                $(this).remove();
                            });
                        }, 5000);
                    }
                });
            });

            // Reset Add Booking form when modal is closed
            $('#addBookingModal').on('hidden.bs.modal', function() {
                var form = $(this).find('form');
                form[0].reset();
                form.find('.select2').val(null).trigger('change');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
                $('#add_customer_details_row').hide();
            });

            // Reset Edit Booking form when modal is closed
            $('#editBookingModal').on('hidden.bs.modal', function() {
                var form = $(this).find('form');
                form[0].reset();
                form.find('.select2').val(null).trigger('change');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
                $('#edit_customer_details_row').hide();
            });

            // Calculate invoice totals (VAT is calculated on Total, Subtotal = Total - VAT)
            function calculateInvoiceTotals() {
                var total = parseFloat($('#invoice_total').val()) || 0;
                var vatPercent = parseFloat($('#invoice_vat').val()) || 0;
                
                // Calculate VAT amount as percentage of total
                var vatAmount = total * (vatPercent / 100);
                // Subtotal is total minus VAT amount
                var subtotal = total - vatAmount;
                
                $('#invoice_subtotal').val(subtotal.toFixed(2));
                $('#invoice_vat_amount').val(vatAmount.toFixed(2));
            }

            // Format total to 2 decimal places on blur and recalculate
            $(document).on('blur', '#invoice_total', function() {
                var val = parseFloat($(this).val());
                if (!isNaN(val)) {
                    $(this).val(val.toFixed(2));
                }
                calculateInvoiceTotals();
            });

            // Recalculate totals when total or VAT changes
            $(document).on('input', '#invoice_total, #invoice_vat', calculateInvoiceTotals);

            // Handle create invoice button click
            $(document).on('click', '.create-invoice-btn', function() {
                var bookingId = $(this).data('id');
                var bookingIdDisplay = $(this).data('booking-id') || 'N/A';
                var vehicle = $(this).data('vehicle');
                var customer = $(this).data('customer');
                var rate = $(this).data('amount');
                var fromDate = $(this).data('from-date');

                var actionUrl = '{{ route("bookings.create-invoice", ":id") }}'.replace(':id', bookingId);
                $('#createInvoiceForm').attr('action', actionUrl);

                $('#invoice_booking_id').val(bookingIdDisplay);
                $('#invoice_vehicle').val(vehicle);
                $('#invoice_customer').val(customer);
                $('#invoice_total').val(parseFloat(rate).toFixed(2));
                calculateInvoiceTotals();

                // Set booking from date
                var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                
                if (fromDate) {
                    var fDate = new Date(fromDate);
                    var displayFromDate = fDate.getDate() + ' ' + months[fDate.getMonth()] + ' ' + fDate.getFullYear();
                    $('#booking_from_date').val(displayFromDate);
                }

                // Invoice date is read-only, set display and hidden value
                var today = new Date();
                var displayDate = today.getDate() + ' ' + months[today.getMonth()] + ' ' + today.getFullYear();
                var hiddenDate = today.getFullYear() + '-' + String(today.getMonth()+1).padStart(2,'0') + '-' + String(today.getDate()).padStart(2,'0');
                // We don't need to set invoice date display since it's already set in the modal with {{ now()->format('d M Y') }}
                $('#invoice_date').val(hiddenDate);

                // Initialize Inv Due date picker (default: invoice date + 5 days)
                var dueDate = new Date(today);
                dueDate.setDate(dueDate.getDate() + 5);
                var dueDateStr = dueDate.getFullYear() + '-' + String(dueDate.getMonth()+1).padStart(2,'0') + '-' + String(dueDate.getDate()).padStart(2,'0');

                flatpickr('#invoice_due_date', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'j M Y',
                    allowInput: false,
                    minDate: hiddenDate,
                    defaultDate: dueDateStr
                });

                $('#createInvoiceModal').modal('show');
            });

            // AJAX form submission for create invoice modal
            $('#createInvoiceForm').on('submit', function(e) {
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
                    type: form.attr('method'),
                    data: new FormData(form[0]),
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        submitBtn.prop('disabled', false).html(originalBtnHtml);

                        if (response.success) {
                            $('#createInvoiceModal').modal('hide');
                            form[0].reset();
                            $('#bookings-table').DataTable().ajax.reload(null, false);

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

            // Reset Create Invoice form when modal is closed
            $('#createInvoiceModal').on('hidden.bs.modal', function() {
                var form = $(this).find('form');
                form[0].reset();
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
                $('#invoice_status').val('pending');
            });

            // Handle status badge click to confirm booking
            $(document).on('click', '.change-status-btn', function() {
                var bookingId = $(this).data('id');
                var currentStatus = $(this).text().trim();
                
                if (confirm('Do you want to confirm this booking?')) {
                    var url = '{{ route("bookings.confirm", ":id") }}'.replace(':id', bookingId);
                    
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Reload the DataTable to show updated status
                                $('#bookings-table').DataTable().ajax.reload(null, false);
                                
                                // Show success message
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
                            $('.alert').remove();
                            var alertHtml = '<div class="alert alert-danger alert-dismissible fade show">' +
                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                                '<h5><i class="icon fas fa-ban"></i> Error!</h5>' +
                                'Failed to update booking status.' +
                                '</div>';
                            $('.row').first().before(alertHtml);
                            
                            setTimeout(function() {
                                $('.alert-danger').fadeOut('slow', function() {
                                    $(this).remove();
                                });
                            }, 5000);
                        }
                    });
                }
            });
        });
    </script>
@stop
