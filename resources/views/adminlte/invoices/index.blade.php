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
@stop

@section('js')
    <script>
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        $(document).ready(function() {
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
        });
    </script>
@stop
