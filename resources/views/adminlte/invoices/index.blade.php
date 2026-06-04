@extends('adminlte::page')

@section('title', 'Invoices')

@section('plugins.DataTable', true)

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

            $('#invoices-table').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('invoices.data') }}",
                    "type": "GET"
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
                "language": {
                    "processing": "<i class='fas fa-spinner fa-spin'></i> Loading...",
                    "search": 'Search invoices:',
                    "lengthMenu": 'Show _MENU_ entries',
                    "info": 'Showing _START_ to _END_ of _TOTAL_ invoices',
                    "infoEmpty": 'No invoices found',
                    "infoFiltered": '(filtered from _MAX_ total invoices)',
                    "zeroRecords": 'No matching invoices found'
                },
                "order": [[0, 'desc']]
            });
        });
    </script>
@stop
