@extends('adminlte::page')

@section('plugins.Select2', true)
@section('title', 'Reports')

@section('content_header')
    <h1>Reports</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bill Reports List</h3>
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
                            <select class="form-control select2" id="filter_vehicle" style="width: 100%;">
                                <option value=""></option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->name }} ({{ $vehicle->number_plate }} - {{ $vehicle->number_code }})</option>
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
                    <table id="reports-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SI</th>
                                <th>Date</th>
                                <th>Billing ID</th>
                                <th>Suppliers</th>
                                <th>Vehicle</th>
                                <th>Customer</th>
                                <th>Total Payable</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
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
            $('#reports-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: "{{ route('reports.data') }}",
                    type: 'GET',
                    data: function(d) {
                        d.filter_bill_id = $('#filter_bill_id').val();
                        d.filter_vehicle = $('#filter_vehicle').val();
                        d.filter_customer = $('#filter_customer').val();
                    }
                },
                columns: [
                    { data: 'id', orderable: false, searchable: false },
                    { data: 'bill_date', orderable: true },
                    { data: 'bill_number', orderable: true },
                    { data: 'suppliers', orderable: false },
                    { data: 'vehicle', orderable: false },
                    { data: 'customer', orderable: false },
                    {
                        data: 'total_payable',
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
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                language: {
                    processing: "<i class='fas fa-spinner fa-spin'></i> Loading...",
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ reports',
                    infoEmpty: 'No reports found',
                    infoFiltered: '(filtered from _MAX_ total reports)',
                    zeroRecords: 'No matching reports found'
                },
                order: [[1, 'desc']],
                drawCallback: function() {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });

            $('#filter_bill_id, #filter_vehicle, #filter_customer').on('change', function() {
                $('#reports-table').DataTable().ajax.reload();
            });

            $('#filter_bill_id').select2({ theme: 'bootstrap4', placeholder: 'Search by Billing ID', width: '100%', allowClear: true });
            $('#filter_vehicle').select2({ theme: 'bootstrap4', placeholder: 'Search by Vehicle', width: '100%', allowClear: true });
            $('#filter_customer').select2({ theme: 'bootstrap4', placeholder: 'Search by Customer', width: '100%', allowClear: true });
        });
    </script>
@stop
