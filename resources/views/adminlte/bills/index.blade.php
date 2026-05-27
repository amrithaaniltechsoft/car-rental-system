@extends('adminlte::page')

@section('title', 'Bills')

@section('content_header')
    <h1>Bills Management</h1>
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
                    <h3 class="card-title">Bills List</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addBillModal">
                            <i class="fas fa-plus"></i> Add Bill
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="bills-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SI</th>
                                <th>Bill #</th>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Bill Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addBillModal" tabindex="-1" role="dialog" aria-labelledby="addBillModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" id="addBillModalLabel">Add New Bill</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addBillForm" action="{{ route('bills.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="invoice_id">Invoice</label>
                                <select class="form-control @error('invoice_id') is-invalid @enderror" id="invoice_id" name="invoice_id" required>
                                    <option value="">Select Invoice</option>
                                    @foreach($invoices as $invoice)
                                        @php
                                            $customerLabel = $invoice->customer->customer_type === 'company'
                                                ? $invoice->customer->company_name
                                                : $invoice->customer->name;
                                        @endphp
                                        <option value="{{ $invoice->id }}"
                                            data-amount="{{ $invoice->amount }}"
                                            {{ old('invoice_id') == $invoice->id ? 'selected' : '' }}>
                                            {{ $invoice->invoice_number }} - {{ $customerLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('invoice_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="amount">Amount (OMR)</label>
                                <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror"
                                       id="amount" name="amount" value="{{ old('amount') }}" required>
                                @error('amount')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="bill_date">Bill Date</label>
                                <input type="text" class="form-control datepicker @error('bill_date') is-invalid @enderror"
                                       id="bill_date" name="bill_date" value="{{ old('bill_date', now()->format('Y-m-d')) }}" required>
                                @error('bill_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="due_date">Due Date</label>
                                <input type="text" class="form-control datepicker @error('due_date') is-invalid @enderror"
                                       id="due_date" name="due_date" value="{{ old('due_date') }}">
                                @error('due_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="status">Status</label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="unpaid" {{ old('status', 'unpaid') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
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
                                <label for="notes">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
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
                $('#addBillModal').modal('show');
            });
        </script>
    @endif
    <script>
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        $(document).ready(function() {
            flatpickr('#bill_date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
                defaultDate: '{{ old('bill_date', now()->format('Y-m-d')) }}'
            });

            flatpickr('#due_date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
                defaultDate: '{{ old('due_date') }}'
            });

            $('#bills-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('bills.data') }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'id', orderable: true },
                    { data: 'bill_number', orderable: true },
                    { data: 'invoice', orderable: false },
                    { data: 'customer', orderable: false },
                    { data: 'amount', orderable: true },
                    { data: 'bill_date', orderable: true },
                    { data: 'due_date', orderable: true },
                    { data: 'status', orderable: true }
                ],
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                language: {
                    processing: "<i class='fas fa-spinner fa-spin'></i> Loading...",
                    search: 'Search bills:',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ bills',
                    infoEmpty: 'No bills found',
                    infoFiltered: '(filtered from _MAX_ total bills)',
                    zeroRecords: 'No matching bills found'
                },
                order: [[0, 'desc']]
            });

            $('#invoice_id').on('change', function() {
                var amount = $(this).find('option:selected').data('amount');
                if (amount !== undefined && amount !== '') {
                    $('#amount').val(amount);
                }
            });

            $('#addBillForm').on('submit', function(e) {
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
                            $('#addBillModal').modal('hide');
                            form[0].reset();
                            $('#invoice_id').val('');
                            $('#status').val('unpaid');
                            flatpickr('#bill_date').setDate('{{ now()->format('Y-m-d') }}');
                            flatpickr('#due_date').clear();
                            $('#bills-table').DataTable().ajax.reload(null, false);

                            $('.alert').remove();
                            var alertHtml = '<div class="alert alert-success alert-dismissible fade show">' +
                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                                '<h5><i class="icon fas fa-check"></i> Success!</h5>' +
                                response.message +
                                '</div>';
                            $('.row').first().before(alertHtml);
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
        });
    </script>
@stop
