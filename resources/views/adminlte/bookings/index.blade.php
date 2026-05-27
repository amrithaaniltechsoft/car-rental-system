@extends('adminlte::page')

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
                                <select class="form-control @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
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
                                <label for="notes">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="1">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
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
                                <select class="form-control" id="edit_customer_id" name="customer_id" required>
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
                                <label for="edit_notes">Notes</label>
                                <textarea class="form-control" id="edit_notes" name="notes" rows="1"></textarea>
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
            <div class="modal-content" style="border: 1px solid #17a2b8;">
                <div class="modal-header justify-content-center" style="background-color: #17a2b8; color: #ffffff; padding: 10px 10px;">
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

            $('#bookings-table').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('bookings.data') }}",
                    "type": "GET"
                },
                "columns": [
                    { "data": "id", "orderable": true },
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
                                        input.after(errorSpan);
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
                                        input.after(errorSpan);
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
                            $('#edit_customer_id').val(booking.customer_id);
                            $('#edit_status').val(booking.status);
                            $('#edit_notes').val(booking.notes);

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
        });
    </script>
@stop
