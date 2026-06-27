@extends('adminlte::page')

@section('plugins.Select2', true)
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

                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select class="form-control select2" id="filter_bill_id" style="width: 100%;">
                                <option value=""></option>
                                @foreach($billNumbers as $bn)
                                    <option value="{{ $bn }}">{{ $bn }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control select2" id="filter_invoice_id" style="width: 100%;">
                                <option value=""></option>
                                @foreach($invoiceNumbers as $invNum)
                                    <option value="{{ $invNum }}">{{ $invNum }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control select2" id="filter_customer" style="width: 100%;">
                                <option value=""></option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->customer_type === 'company' ? $customer->company_name : $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    </div>
                    <table id="bills-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SI</th>
                                <th>Bill #</th>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Total Received</th>
                                <th>Total</th>
                                <th>VAT Amount</th>
                                <th>Net Profit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- View Bill Modal -->
    <div class="modal fade" id="viewBillModal" tabindex="-1" role="dialog" aria-labelledby="viewBillModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" id="viewBillModalLabel">View Bill Details</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="viewBillModalBody">
                    <!-- Bill details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Bill Modal -->
    <div class="modal fade" id="editBillModal" tabindex="-1" role="dialog" aria-labelledby="editBillModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" id="editBillModalLabel">Edit Bill</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editBillForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="edit_bill_number">Bill #</label>
                                <input type="text" class="form-control" id="edit_bill_number" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_invoice_number">Invoice #</label>
                                <input type="text" class="form-control" id="edit_invoice_number" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_bill_date">Bill Date</label>
                                <input type="text" class="form-control datepicker" id="edit_bill_date" name="bill_date" readonly style="background-color: #e9ecef;">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_invoice_amount">Invoice Amount</label>
                                <input type="text" class="form-control" id="edit_invoice_amount" readonly>
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
                                <tbody id="edit_bill_details_table_body">
                                    <tr class="bill-row">
                                        <td>
                                            <select class="form-control select2 edit-bill-supplier" name="supplier_id[]" style="width: 100%;">
                                                <option value="">Select Supplier</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}" data-code="{{ $supplier->supplier_code }}">{{ $supplier->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="edit-bill-td-id">—</td>
                                        <td><input type="text" class="form-control edit-bill-ref" name="purpose[]"></td>
                                        <td><input type="number" class="form-control edit-bill-vat text-right" name="vat[]" value="5" readonly></td>
                                        <td><input type="text" class="form-control edit-bill-vat-amount text-right" name="vat_amount[]" readonly></td>
                                        <td><input type="text" class="form-control edit-bill-total text-right" name="total_payable[]"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-success btn-sm edit-btn-add-row py-0"><i class="fas fa-plus fa-xs"></i></button>
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
                                        <td><span class="form-control form-control-sm text-right" id="edit_bill_summary_total" style="border: none; background: transparent; display: block; pointer-events: none;">0.000</span></td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: middle;" class="pl-2"><strong>VAT(%)</strong></td>
                                        <td><input type="text" class="form-control form-control-sm text-right" id="edit_bill_summary_vat_pct" value="5" style="border: none; background: transparent; font-weight: bold;"></td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: middle;" class="pl-2"><strong>VAT(Amount)</strong></td>
                                        <td><span class="form-control form-control-sm text-right" id="edit_bill_summary_vat_amt" style="border: none; background: transparent; display: block; pointer-events: none;">0.000</span></td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: middle;" class="pl-2"><strong>Sub Total</strong></td>
                                        <td><span class="form-control form-control-sm text-right" id="edit_bill_summary_subtotal" style="border: none; background: transparent; display: block; pointer-events: none;">0.000</span></td>
                                    </tr>
                                    <tr class="table-success">
                                        <td style="vertical-align: middle;" class="pl-2"><strong>Net Profit</strong></td>
                                        <td><span class="form-control form-control-sm text-right font-weight-bold" id="edit_bill_summary_net_profit" style="border: none; background: transparent; font-size: 16px; display: block; pointer-events: none;">0.000</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Update Bill</button>
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
        .dataTables_length { padding-left: 10px; }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        $(document).ready(function() {
            var billTable = $('#bills-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('bills.data') }}",
                    type: 'GET',
                    data: function(d) {
                        d.filter_bill_id = $('#filter_bill_id').val();
                        d.filter_invoice_id = $('#filter_invoice_id').val();
                        d.filter_customer = $('#filter_customer').val();
                    }
                },
                columns: [
                    { data: 'id', orderable: true },
                    { data: 'bill_number', orderable: true },
                    { data: 'invoice', orderable: false },
                    { data: 'customer', orderable: false },
                    { 
                        data: 'amount', 
                        orderable: true, 
                        createdCell: function (td) {
                            $(td).addClass('text-right');
                        } 
                    },
                    { 
                        data: 'total', 
                        orderable: false, 
                        createdCell: function (td) {
                            $(td).addClass('text-right');
                        } 
                    },
                    { 
                        data: 'vat_amount', 
                        orderable: false, 
                        createdCell: function (td) {
                            $(td).addClass('text-right');
                        } 
                    },
                    { 
                        data: 'net_profit', 
                        orderable: false, 
                        createdCell: function (td) {
                            $(td).addClass('text-right');
                        } 
                    },
                    { data: 'actions', orderable: false, searchable: false }
                ],
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                searching: false,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                language: {
                    processing: "<i class='fas fa-spinner fa-spin'></i> Loading...",
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ bills',
                    infoEmpty: 'No bills found',
                    infoFiltered: '(filtered from _MAX_ total bills)',
                    zeroRecords: 'No matching bills found'
                },
                order: [[0, 'desc']],
                drawCallback: function() {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });

            $('#filter_bill_id, #filter_invoice_id, #filter_customer').on('change', function() {
                billTable.ajax.reload();
            });

            $('#filter_bill_id').select2({ theme: 'bootstrap4', placeholder: 'Search by Bill #', width: '100%', allowClear: true });
            $('#filter_invoice_id').select2({ theme: 'bootstrap4', placeholder: 'Search by Invoice #', width: '100%', allowClear: true });
            $('#filter_customer').select2({ theme: 'bootstrap4', placeholder: 'Search by Customer', width: '100%', allowClear: true });

            // Handle delete bill button click
            $(document).on('click', '.delete-bill-btn', function() {
                var url = $(this).data('url');

                if (confirm('Are you sure you want to delete this bill?')) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                billTable.ajax.reload(null, false);

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
                            alert('Error deleting bill. Please try again.');
                        }
                    });
                }
            });

            function fmtNum3(num) { return num.toFixed(3).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

            function editCalculateBillSummary() {
                var grandTotal = 0, grandVatAmt = 0;
                $('#edit_bill_details_table_body .bill-row').each(function() {
                    grandTotal += parseFloat($(this).find('.edit-bill-total').val().replace(/,/g, '')) || 0;
                    grandVatAmt += parseFloat($(this).find('.edit-bill-vat-amount').val().replace(/,/g, '')) || 0;
                });
                var invAmt = parseFloat($('#edit_invoice_amount').val().replace(/,/g, '')) || 0;
                var vatPct = parseFloat($('#edit_bill_summary_vat_pct').val()) || 0;
                var subtotal = grandTotal - grandVatAmt;
                var netProfit = invAmt - grandTotal;
                $('#edit_bill_summary_total').text(fmtNum3(grandTotal));
                $('#edit_bill_summary_vat_amt').text(fmtNum3(grandVatAmt));
                $('#edit_bill_summary_subtotal').text(fmtNum3(subtotal));
                $('#edit_bill_summary_net_profit').text(fmtNum3(netProfit));
            }

            // Handle edit bill button click
            $(document).on('click', '.edit-bill-btn', function() {
                var url = $(this).data('url');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            var bill = response.bill;
                            $('#edit_bill_number').val(bill.bill_number);
                            $('#edit_invoice_number').val(bill.invoice_number);
                            $('#edit_bill_date').val(bill.bill_date);
                            $('#edit_invoice_amount').val(fmtNum3(parseFloat(bill.invoice_amount) || 0));
                            $('#editBillForm').attr('action', '{{ route('bills.update', ':id') }}'.replace(':id', bill.id));

                            // Populate billing details
                            var $tableBody = $('#edit_bill_details_table_body');
                            $tableBody.find('.bill-row:not(:first)').remove();
                            var $firstRow = $tableBody.find('.bill-row:first');
                            $firstRow.find('.edit-bill-supplier').val('').trigger('change');
                            $firstRow.find('.edit-bill-td-id').text('—');
                            $firstRow.find('.edit-bill-ref').val('');
                            $firstRow.find('.edit-bill-vat').val('5');
                            $firstRow.find('.edit-bill-vat-amount').val('');
                            $firstRow.find('.edit-bill-total').val('');

                            if (bill.billing_details && bill.billing_details.length > 0) {
                                var firstDetail = bill.billing_details[0];
                                $firstRow.find('.edit-bill-supplier').val(firstDetail.supplier_id).trigger('change');
                                $firstRow.find('.edit-bill-td-id').text($firstRow.find('.edit-bill-supplier option:selected').data('code') || '—');
                                $firstRow.find('.edit-bill-ref').val(firstDetail.purpose || '');
                                $firstRow.find('.edit-bill-vat').val(firstDetail.vat || 5);
                                $firstRow.find('.edit-bill-vat-amount').val(fmtNum3(parseFloat(firstDetail.vat_amount) || 0));
                                $firstRow.find('.edit-bill-total').val(fmtNum3(parseFloat(firstDetail.total_payable) || 0));

                                for (var i = 1; i < bill.billing_details.length; i++) {
                                    var detail = bill.billing_details[i];
                                    var $newRow = $firstRow.clone();
                                    $newRow.find('input').val('');
                                    $newRow.find('.edit-bill-td-id').text('—');
                                    $newRow.find('.edit-bill-vat').val('5');
                                    $newRow.find('.edit-btn-add-row')
                                        .removeClass('btn-success edit-btn-add-row')
                                        .addClass('btn-danger edit-btn-remove-row')
                                        .html('<i class="fas fa-minus"></i>');
                                    $newRow.find('.select2-container').remove();
                                    $newRow.find('select').removeClass('select2-hidden-accessible').removeAttr('aria-hidden data-select2-id');
                                    $tableBody.append($newRow);

                                    $newRow.find('.edit-bill-supplier').val(detail.supplier_id).trigger('change');
                                    var code = $newRow.find('.edit-bill-supplier option:selected').data('code');
                                    $newRow.find('.edit-bill-td-id').text(code || '—');
                                    $newRow.find('.edit-bill-ref').val(detail.purpose || '');
                                    $newRow.find('.edit-bill-vat').val(detail.vat || 5);
                                    $newRow.find('.edit-bill-vat-amount').val(fmtNum3(parseFloat(detail.vat_amount) || 0));
                                    $newRow.find('.edit-bill-total').val(fmtNum3(parseFloat(detail.total_payable) || 0));

                                    $newRow.find('.edit-bill-supplier').select2({
                                        theme: 'bootstrap4',
                                        placeholder: 'Select Supplier',
                                        width: '100%',
                                        minimumResultsForSearch: 0
                                    });
                                }
                            }

                            $firstRow.find('.edit-bill-supplier').select2({
                                theme: 'bootstrap4',
                                placeholder: 'Select Supplier',
                                width: '100%',
                                minimumResultsForSearch: 0
                            });

                            editCalculateBillSummary();
                            $('#editBillModal').modal('show');
                        }
                    },
                    error: function() {
                        alert('Error loading bill details. Please try again.');
                    }
                });
            });

            // Format Total Payable on blur for edit modal
            $(document).on('blur', '.edit-bill-total', function() {
                var $row = $(this).closest('tr');
                var raw = $(this).val().replace(/,/g, '');
                var total = parseFloat(raw) || 0;
                $(this).val(fmtNum3(total));
                var vatPct = parseFloat($row.find('.edit-bill-vat').val()) || 0;
                var vatAmt = total * vatPct / 100;
                $row.find('.edit-bill-vat-amount').val(fmtNum3(vatAmt));
                editCalculateBillSummary();
            });

            // Update ID column when supplier is selected in edit modal
            $(document).on('change', '.edit-bill-supplier', function() {
                var code = $(this).find(':selected').data('code');
                $(this).closest('tr').find('.edit-bill-td-id').text(code || '—');
            });

            // Add new row in edit modal
            $(document).on('click', '.edit-btn-add-row', function() {
                var $tableBody = $('#edit_bill_details_table_body');
                var $newRow = $tableBody.find('.bill-row:first').clone();
                $newRow.find('input').val('');
                $newRow.find('.edit-bill-td-id').text('—');
                $newRow.find('.edit-bill-vat').val('5');
                $newRow.find('.edit-btn-add-row')
                    .removeClass('btn-success edit-btn-add-row')
                    .addClass('btn-danger edit-btn-remove-row')
                    .html('<i class="fas fa-minus"></i>');
                $newRow.find('.select2-container').remove();
                $newRow.find('select').removeClass('select2-hidden-accessible').removeAttr('aria-hidden data-select2-id');
                $tableBody.append($newRow);
                $newRow.find('.edit-bill-supplier').select2({
                    theme: 'bootstrap4',
                    placeholder: 'Select Supplier',
                    width: '100%',
                    minimumResultsForSearch: 0
                });
                editCalculateBillSummary();
            });

            // Remove row in edit modal
            $(document).on('click', '.edit-btn-remove-row', function() {
                $(this).closest('tr').remove();
                editCalculateBillSummary();
            });

            // Recalculate when VAT(%) changes in edit modal
            $(document).on('input', '#edit_bill_summary_vat_pct', editCalculateBillSummary);

            // Handle edit bill form submission
            $('#editBillForm').on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();

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
                            $('#editBillModal').modal('hide');
                            form[0].reset();
                            billTable.ajax.reload(null, false);

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

            // Handle view bill button click
            $(document).on('click', '.view-bill-btn', function() {
                var url = $(this).data('url');

                $('#viewBillModalBody').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x text-info"></i><p class="mt-2">Loading details...</p></div>');
                $('#viewBillModal').modal('show');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(data) {
                        $('#viewBillModalBody').html(data);
                    },
                    error: function() {
                        $('#viewBillModalBody').html('<div class="alert alert-danger">Error loading bill details. Please try again.</div>');
                    }
                });
            });

        });
    </script>
@stop
