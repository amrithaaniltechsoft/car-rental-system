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
                                <th>Booking</th>
                                <th>Booking From</th>
                                <th>Booking To</th>
                                <th>Amount</th>
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
@stop

@section('js')
    <script>
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

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
                    { "data": "booking", "orderable": false },
                    { "data": "booking_from_date", "orderable": true },
                    { "data": "booking_to_date", "orderable": true },
                    { "data": "amount", "orderable": true, "className": "text-right" },
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
