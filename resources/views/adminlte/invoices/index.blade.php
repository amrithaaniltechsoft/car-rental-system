@extends('adminlte::page')

@section('title', 'Invoices')

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
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addInvoiceModal">
                            <i class="fas fa-plus"></i> Add Invoice
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="invoices-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SI</th>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Booking</th>
                                <th>Amount</th>
                                <th>Invoice Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="addInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" id="addInvoiceModalLabel">Add New Invoice</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addInvoiceForm" action="{{ route('invoices.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="customer_id">Customer</label>
                                <select class="form-control @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        @php
                                            $customerLabel = $customer->customer_type === 'company'
                                                ? $customer->company_name
                                                : $customer->name;
                                        @endphp
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customerLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="booking_id">Booking</label>
                                <select class="form-control @error('booking_id') is-invalid @enderror" id="booking_id" name="booking_id">
                                    <option value="">No booking linked</option>
                                    @foreach($bookings as $booking)
                                        <option value="{{ $booking->id }}"
                                            data-customer-id="{{ $booking->customer_id }}"
                                            data-amount="{{ $booking->total_amount }}"
                                            {{ old('booking_id') == $booking->id ? 'selected' : '' }}>
                                            {{ $booking->vehicle->name }}
                                            ({{ $booking->from_date->format('d/m/Y') }} - {{ $booking->to_date->format('d/m/Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('booking_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="invoice_date">Invoice Date</label>
                                <input type="text" class="form-control datepicker @error('invoice_date') is-invalid @enderror"
                                       id="invoice_date" name="invoice_date" value="{{ old('invoice_date', now()->format('Y-m-d')) }}" required>
                                @error('invoice_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="amount">Amount (OMR)</label>
                                <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror"
                                       id="amount" name="amount" value="{{ old('amount') }}" required>
                                @error('amount')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="rate">Rate</label>
                                <input type="number" step="0.01" min="0" class="form-control @error('rate') is-invalid @enderror"
                                       id="rate" name="rate" value="{{ old('rate') }}">
                                @error('rate')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="status">Status</label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="overdue" {{ old('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="description">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="2">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
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
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                $('#addInvoiceModal').modal('show');
            });
        </script>
    @endif
    <script>
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        $(document).ready(function() {
            flatpickr('#invoice_date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
                defaultDate: '{{ old('invoice_date', now()->format('Y-m-d')) }}'
            });

            $('#invoices-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('invoices.data') }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'id', orderable: true },
                    { data: 'invoice_number', orderable: true },
                    { data: 'customer', orderable: false },
                    { data: 'booking', orderable: false },
                    { data: 'amount', orderable: true },
                    { data: 'invoice_date', orderable: true },
                    { data: 'status', orderable: true }
                ],
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                language: {
                    processing: "<i class='fas fa-spinner fa-spin'></i> Loading...",
                    search: 'Search invoices:',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ invoices',
                    infoEmpty: 'No invoices found',
                    infoFiltered: '(filtered from _MAX_ total invoices)',
                    zeroRecords: 'No matching invoices found'
                },
                order: [[0, 'desc']]
            });

            $('#booking_id').on('change', function() {
                var selected = $(this).find('option:selected');
                var customerId = selected.data('customer-id');
                var amount = selected.data('amount');

                if (customerId) {
                    $('#customer_id').val(customerId);
                }
                if (amount !== undefined && amount !== '') {
                    $('#amount').val(amount);
                }
            });

            $('#addInvoiceForm').on('submit', function(e) {
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
                            $('#addInvoiceModal').modal('hide');
                            form[0].reset();
                            $('#customer_id').val('');
                            $('#booking_id').val('');
                            flatpickr('#invoice_date').setDate('{{ now()->format('Y-m-d') }}');
                            $('#status').val('pending');
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

            $('#addInvoiceModal').on('hidden.bs.modal', function() {
                var form = $('#addInvoiceForm');
                form[0].reset();
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
                $('#customer_id').val('');
                $('#booking_id').val('');
                flatpickr('#invoice_date').setDate('{{ now()->format('Y-m-d') }}');
                $('#status').val('pending');
            });
        });
    </script>
@stop
