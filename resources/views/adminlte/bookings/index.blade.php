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
                        {{-- Row 1: Customer, Vehicle, Booking Reference, Status --}}
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="customer_id">Customer <span class="text-danger">*</span></label>
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
                            <div class="form-group col-md-3">
                                <label for="vehicle_id">Vehicle <span class="text-danger">*</span></label>
                                <select class="form-control select2 @error('vehicle_id') is-invalid @enderror" id="vehicle_id" name="vehicle_id" required style="width: 100%;">
                                    <option value="">Select Vehicle</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">{{ $vehicle->name }} ({{ $vehicle->registration_number }})</option>
                                    @endforeach
                                </select>
                                @error('vehicle_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="add_booking_ref">Booking Reference</label>
                                <input type="text" class="form-control" id="add_booking_ref" value="Auto Generated" readonly style="background:#f4f6f9; color:#6c757d;">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="status">Booking Status <span class="text-danger">*</span></label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="completed">Completed</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Row 2: Booking Date, Pickup Date & Time, Return Date & Time, Rental Duration --}}
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="booking_date">Booking Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control datepicker @error('booking_date') is-invalid @enderror" id="booking_date" name="booking_date" value="{{ old('booking_date') }}" required>
                                @error('booking_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="pickup_datetime">Pickup Date &amp; Time <span class="text-danger">*</span></label>
                                <input type="text" class="form-control datetimepicker @error('pickup_datetime') is-invalid @enderror" id="pickup_datetime" name="pickup_datetime" value="{{ old('pickup_datetime') }}" required>
                                @error('pickup_datetime')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="return_datetime">Return Date &amp; Time <span class="text-danger">*</span></label>
                                <input type="text" class="form-control datetimepicker @error('return_datetime') is-invalid @enderror" id="return_datetime" name="return_datetime" value="{{ old('return_datetime') }}" required>
                                @error('return_datetime')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="add_rental_duration">Rental Duration</label>
                                <input type="text" class="form-control" id="add_rental_duration" value="Auto calculated" readonly style="background:#f4f6f9; color:#6c757d;">
                            </div>
                        </div>

                        {{-- Row 3: Pickup Location, Return Location --}}
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="pickup_location">Pickup Location</label>
                                <input type="text" class="form-control @error('pickup_location') is-invalid @enderror" id="pickup_location" name="pickup_location" value="{{ old('pickup_location') }}" placeholder="Enter pickup location">
                                @error('pickup_location')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="return_location">Return Location</label>
                                <input type="text" class="form-control @error('return_location') is-invalid @enderror" id="return_location" name="return_location" value="{{ old('return_location') }}" placeholder="Enter return location">
                                @error('return_location')
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
                                        <div class="cd-item" id="add_cd_phone_wrap"><i class="fas fa-phone-alt"></i><span id="add_cd_phone"></span></div>
                                        <div class="cd-item" id="add_cd_address_wrap"><i class="fas fa-map-marker-alt"></i><span id="add_cd_address"></span></div>
                                        <div class="cd-item" id="add_cd_company_wrap"><i class="fas fa-building"></i><span id="add_cd_company"></span></div>
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
                        {{-- Row 1: Customer, Vehicle, Booking Reference, Status --}}
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="edit_customer_id">Customer <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="edit_customer_id" name="customer_id" required style="width: 100%;">
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_vehicle_id">Vehicle <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="edit_vehicle_id" name="vehicle_id" required style="width: 100%;">
                                    <option value="">Select Vehicle</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">{{ $vehicle->name }} ({{ $vehicle->registration_number }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Booking Reference</label>
                                <input type="text" class="form-control" id="edit_booking_ref" readonly style="background:#f4f6f9; color:#6c757d;">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_status">Booking Status <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>

                        {{-- Row 2: Booking Date, Pickup Date & Time, Return Date & Time, Rental Duration --}}
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="edit_booking_date">Booking Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control datepicker" id="edit_booking_date" name="booking_date" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_pickup_datetime">Pickup Date &amp; Time <span class="text-danger">*</span></label>
                                <input type="text" class="form-control datetimepicker" id="edit_pickup_datetime" name="pickup_datetime" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_return_datetime">Return Date &amp; Time <span class="text-danger">*</span></label>
                                <input type="text" class="form-control datetimepicker" id="edit_return_datetime" name="return_datetime" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_rental_duration">Rental Duration</label>
                                <input type="text" class="form-control" id="edit_rental_duration" readonly style="background:#f4f6f9; color:#6c757d;">
                            </div>
                        </div>

                        {{-- Row 3: Locations --}}
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="edit_pickup_location">Pickup Location</label>
                                <input type="text" class="form-control" id="edit_pickup_location" name="pickup_location" placeholder="Enter pickup location">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="edit_return_location">Return Location</label>
                                <input type="text" class="form-control" id="edit_return_location" name="return_location" placeholder="Enter return location">
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
                                        <div class="cd-item" id="edit_cd_phone_wrap"><i class="fas fa-phone-alt"></i><span id="edit_cd_phone"></span></div>
                                        <div class="cd-item" id="edit_cd_address_wrap"><i class="fas fa-map-marker-alt"></i><span id="edit_cd_address"></span></div>
                                        <div class="cd-item" id="edit_cd_company_wrap"><i class="fas fa-building"></i><span id="edit_cd_company"></span></div>
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
                            <div class="form-group col-md-3">
                                <label for="invoice_booking_id">Booking ID</label>
                                <input type="text" class="form-control" id="invoice_booking_id" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="invoice_vehicle">Vehicle</label>
                                <input type="text" class="form-control" id="invoice_vehicle" readonly>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="invoice_customer">Customer</label>
                                <input type="text" class="form-control" id="invoice_customer" readonly>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="invoice_booking_date">Booking Date</label>
                                <input type="text" class="form-control" id="invoice_booking_date" readonly>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="invoice_due_date">Due Date</label>
                                <input type="text" class="form-control datepicker @error('due_date') is-invalid @enderror"
                                       id="invoice_due_date" name="due_date" value="{{ old('due_date') }}">
                                @error('due_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <input type="hidden" id="invoice_date" name="invoice_date" value="{{ now()->format('Y-m-d') }}">

                        <hr>
                        <h5><strong>Pricing Details</strong></h5>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="invoice_rate_type">Rate Type</label>
                                <select class="form-control" id="invoice_rate_type" name="rate_type">
                                    <option value="daily">Daily Rate</option>
                                    <option value="weekly">Weekly Rate</option>
                                    <option value="monthly">Monthly Rate</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="invoice_extra_kms_charges">Extra Kms Charges</label>
                                <input type="number" step="0.01" min="0" class="form-control pricing-input text-right" id="invoice_extra_kms_charges" name="extra_kms_charges">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="invoice_security_deposit">Security Deposit</label>
                                <input type="text" class="form-control pricing-input text-right" id="invoice_security_deposit" name="security_deposit">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="invoice_insurance_fee">Insurance Fee</label>
                                <input type="text" class="form-control pricing-input text-right" id="invoice_insurance_fee" name="insurance_fee">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="invoice_additional_driver_fee">Additional Driver Fee</label>
                                <input type="text" class="form-control pricing-input text-right" id="invoice_additional_driver_fee" name="additional_driver_fee">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="invoice_delivery_charge">Delivery Charge</label>
                                <input type="text" class="form-control pricing-input text-right" id="invoice_delivery_charge" name="delivery_charge">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="invoice_fuel_charge">Fuel Charge</label>
                                <input type="text" class="form-control pricing-input text-right" id="invoice_fuel_charge" name="fuel_charge">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="invoice_gps_charges">GPS Charges</label>
                                <input type="text" class="form-control pricing-input text-right" id="invoice_gps_charges" name="gps_charges">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="invoice_salik_toll_charges">Salik/Toll Charges</label>
                                <input type="text" class="form-control pricing-input text-right" id="invoice_salik_toll_charges" name="salik_toll_charges">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="invoice_vat">VAT/Tax (%)</label>
                                <input type="number" step="0.01" min="0" class="form-control pricing-input" id="invoice_vat" name="vat" value="5">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="invoice_discount_amount">Discount Amount</label>
                                <input type="text" class="form-control pricing-input text-right text-danger" id="invoice_discount_amount" name="discount_amount" max="100">
                            </div>
                        </div>

                        <hr>

                        <div class="form-row justify-content-end">
                            <div class="col-md-5">
                                <table class="table table-bordered table-sm" style="font-size: 14px;">
                                    <tr>
                                        <td style="width: 130px; vertical-align: middle;" class="pl-2"><strong>Sub Total</strong></td>
                                        <td>
                                            <span class="form-control form-control-sm text-right" id="invoice_subtotal" name="subtotal" style="border: none; background: transparent; font-weight: bold; display: block; pointer-events: none;">0.00</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: middle;" class="pl-2"><strong>VAT Amount</strong></td>
                                        <td>
                                            <span class="form-control form-control-sm text-right" id="invoice_vat_amount" style="border: none; background: transparent; display: block; pointer-events: none;">0.00</span>
                                        </td>
                                    </tr>
                                    <tr class="table-success">
                                        <td style="vertical-align: middle;" class="pl-2"><strong>Total Amount</strong></td>
                                        <td>
                                            <span class="form-control form-control-sm text-right font-weight-bold" id="invoice_total" name="total" style="border: none; background: transparent; font-size: 16px; display: block; pointer-events: none;">0.00</span>
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
        .invoice-label-cell {
            padding-top: 10px !important;
            font-size: 16px;
        }
        .table-sm td, .table-sm th {
            padding: revert-rule;
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
                if (el && el._flatpickr) { el._flatpickr.destroy(); }
            }

            function calcRentalDuration(pickupVal, returnVal, displayId) {
                if (!pickupVal || !returnVal) { $('#' + displayId).val('Auto calculated'); return; }
                var p = new Date(pickupVal), r = new Date(returnVal);
                if (isNaN(p) || isNaN(r) || r <= p) { $('#' + displayId).val('—'); return; }
                var diffMs = r - p;
                var diffHours = Math.floor(diffMs / 3600000);
                var days = Math.floor(diffHours / 24);
                var hours = diffHours % 24;
                var label = '';
                if (days > 0) label += days + ' day' + (days > 1 ? 's' : '');
                if (hours > 0) label += (label ? ' ' : '') + hours + 'h';
                $('#' + displayId).val(label || '< 1 hour');
            }

            // ── ADD MODAL date pickers ──
            flatpickr('#booking_date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
                defaultDate: new Date()
            });

            function initAddDatetimePickers() {
                destroyFlatpickr('#pickup_datetime');
                destroyFlatpickr('#return_datetime');

                var returnPicker = flatpickr('#return_datetime', {
                    enableTime: true,
                    dateFormat: 'Y-m-d H:i',
                    time_24hr: true,
                    allowInput: false,
                    minDate: bookingToday,
                    onChange: function(sel) {
                        calcRentalDuration($('#pickup_datetime').val(), sel[0] ? flatpickr.formatDate(sel[0], 'Y-m-d H:i') : '', 'add_rental_duration');
                    }
                });

                flatpickr('#pickup_datetime', {
                    enableTime: true,
                    dateFormat: 'Y-m-d H:i',
                    time_24hr: true,
                    allowInput: false,
                    minDate: bookingToday,
                    onChange: function(sel) {
                        if (sel[0]) { returnPicker.set('minDate', sel[0]); }
                        calcRentalDuration(sel[0] ? flatpickr.formatDate(sel[0], 'Y-m-d H:i') : '', $('#return_datetime').val(), 'add_rental_duration');
                    }
                });
            }
            initAddDatetimePickers();

            // ── EDIT MODAL date pickers ──
            function initEditPickers(bookingDate, pickupVal, returnVal) {
                destroyFlatpickr('#edit_booking_date');
                destroyFlatpickr('#edit_pickup_datetime');
                destroyFlatpickr('#edit_return_datetime');

                flatpickr('#edit_booking_date', {
                    dateFormat: 'Y-m-d',
                    allowInput: false,
                    defaultDate: bookingDate || null
                });

                var editReturnPicker = flatpickr('#edit_return_datetime', {
                    enableTime: true,
                    dateFormat: 'Y-m-d H:i',
                    time_24hr: true,
                    allowInput: false,
                    defaultDate: returnVal || null,
                    onChange: function(sel) {
                        calcRentalDuration($('#edit_pickup_datetime').val(), sel[0] ? flatpickr.formatDate(sel[0], 'Y-m-d H:i') : '', 'edit_rental_duration');
                    }
                });

                flatpickr('#edit_pickup_datetime', {
                    enableTime: true,
                    dateFormat: 'Y-m-d H:i',
                    time_24hr: true,
                    allowInput: false,
                    defaultDate: pickupVal || null,
                    onChange: function(sel) {
                        if (sel[0]) { editReturnPicker.set('minDate', sel[0]); }
                        calcRentalDuration(sel[0] ? flatpickr.formatDate(sel[0], 'Y-m-d H:i') : '', $('#edit_return_datetime').val(), 'edit_rental_duration');
                    }
                });

                if (pickupVal && returnVal) {
                    calcRentalDuration(pickupVal, returnVal, 'edit_rental_duration');
                }
            }

            // Initialize Select2 for Customer inside the Add Booking Modal
            $('#addBookingModal #customer_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Select Customer',
                allowClear: true,
                dropdownParent: $('#addBookingModal'),
                width: '100%'
            });

            // Initialize Select2 for Vehicle inside the Add Booking Modal
            $('#addBookingModal #vehicle_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Select Vehicle',
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

            // Initialize Select2 for Vehicle inside the Edit Booking Modal
            $('#editBookingModal #edit_vehicle_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Select Vehicle',
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
                            $('#edit_vehicle_id').val(booking.vehicle_id).trigger('change');
                            $('#edit_customer_id').val(booking.customer_id).trigger('change');
                            $('#edit_booking_ref').val(booking.booking_id || 'N/A');
                            $('#edit_status').val(booking.status);
                            $('#edit_pickup_location').val(booking.pickup_location);
                            $('#edit_return_location').val(booking.return_location);

                            initEditPickers(booking.booking_date, booking.pickup_datetime, booking.return_datetime);

                            if (booking.rental_duration) {
                                $('#edit_rental_duration').val(booking.rental_duration);
                            }

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
            
            // Handle status badge click to confirm booking
            $(document).on('click', '.change-status-btn', function() {
                if (!confirm('Are you sure you want to change this booking status to confirmed?')) {
                    return;
                }

                var bookingId = $(this).data('id');
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
            });

            // Reset Add Booking form when modal is closed
            $('#addBookingModal').on('hidden.bs.modal', function() {
                var form = $(this).find('form');
                form[0].reset();
                form.find('.select2').val(null).trigger('change');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
                $('#add_customer_details_row').hide();
                $('#add_rental_duration').val('Auto calculated');
                initAddDatetimePickers();
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

            function parseNum(id) { return parseFloat($('#' + id).val().replace(/,/g, '')) || 0; }

            function fmtNum(num) { return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

            function formatPricingInput(id) {
                var val = $('#' + id).val();
                if (val === '' || val === null || val === undefined) {
                    return;
                }
                var numVal = parseFloat(val.replace(/,/g, '')) || 0;
                var maxVal = parseFloat($('#' + id).attr('max'));
                if (!isNaN(maxVal) && numVal > maxVal) {
                    numVal = maxVal;
                }
                $('#' + id).val(fmtNum(numVal));
            }

            // Calculate invoice totals based on pricing fields
            function calculateInvoiceTotals() {
                var extraKms = parseNum('invoice_extra_kms_charges');
                var security = parseNum('invoice_security_deposit');
                var insurance = parseNum('invoice_insurance_fee');
                var driver = parseNum('invoice_additional_driver_fee');
                var delivery = parseNum('invoice_delivery_charge');
                var fuel = parseNum('invoice_fuel_charge');
                var gps = parseNum('invoice_gps_charges');
                var salik = parseNum('invoice_salik_toll_charges');
                var discountPercent = parseNum('invoice_discount_amount');
                var vatPercent = parseNum('invoice_vat');

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

                $('#invoice_subtotal').text(fmtNum(subtotal));
                $('#invoice_vat_amount').text(fmtNum(vatAmount));
                $('#invoice_total').text(fmtNum(total));
            }

            // Recalculate totals when any pricing input or VAT changes
            $(document).on('input', '.pricing-input', calculateInvoiceTotals);
            $(document).on('change', '.pricing-input', calculateInvoiceTotals);
            $(document).on('blur', '.pricing-input', function() {
                var id = $(this).attr('id');
                formatPricingInput(id);
                calculateInvoiceTotals();
            });

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
                
                // Reset all pricing fields to blank
                $('.pricing-input').val('');
                $('#invoice_rate_type').val('daily'); // Default to Daily Rate
                $('#invoice_vat').val('5'); // Default VAT %
                calculateInvoiceTotals();

                // Set booking from date
                var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                
                if (fromDate) {
                    var fDate = new Date(fromDate);
                    var displayFromDate = fDate.getDate() + ' ' + months[fDate.getMonth()] + ' ' + fDate.getFullYear();
                    $('#invoice_booking_date').val(displayFromDate);
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

                // Strip commas from all pricing-input fields before submission
                $('.pricing-input').each(function() {
                    var val = $(this).val();
                    if (val) {
                        $(this).val(val.replace(/,/g, ''));
                    }
                });

                var total = parseFloat($('#invoice_total').text().replace(/,/g, '')) || 0;
                if (total <= 0) {
                    $('.alert').remove();
                    $('.row').first().before(
                        '<div class="alert alert-danger alert-dismissible fade show">' +
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                        '<h5><i class="icon fas fa-ban"></i> Error!</h5>Total amount cannot be zero. Please add pricing details.' +
                        '</div>'
                    );
                    return false;
                }

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

            // Fetch and show next booking ID when Add Booking modal opens
            $('#addBookingModal').on('shown.bs.modal', function () {
                $.get('{{ route("bookings.next-id") }}', function (res) {
                    $('#add_booking_ref').val(res.booking_id);
                });
            });

        });
    </script>
@stop
