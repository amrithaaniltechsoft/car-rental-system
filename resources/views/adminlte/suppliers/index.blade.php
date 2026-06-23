@extends('adminlte::page')

@section('plugins.Select2', true)

@section('title', 'Suppliers')

@section('content_header')
    <h1>Suppliers Management</h1>
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
                    <h3 class="card-title">Suppliers List</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addSupplierModal">
                            <i class="fas fa-plus"></i> Add Supplier
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select class="form-control select2" id="filter_name" style="width: 100%;">
                                <option value="">Search by Name</option>
                                @foreach($supplierNames as $name)
                                    <option value="{{ $name }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="filter_email" placeholder="Search by Email">
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="filter_phone" placeholder="Search by Phone">
                        </div>
                    </div>
                    <table id="suppliers-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Supplier ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
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

    <!-- Add Supplier Modal -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" id="addSupplierModalLabel">Add New Supplier</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('suppliers.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="name">Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="phone">Phone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" required
                                       pattern="[0-9]*" inputmode="numeric"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address" name="address" rows="3"></textarea>
                            @error('address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">

                        <button type="submit" class="btn btn-success">Save Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div class="modal fade" id="editSupplierModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" style="font-weight:500;">Edit Supplier</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editSupplierForm" action="#" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" id="edit_supplier_id" name="id">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="edit_name">Name</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="edit_phone">Phone</label>
                                <input type="text" class="form-control" id="edit_phone" name="phone" required
                                       pattern="[0-9]*" inputmode="numeric"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="edit_email">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_address">Address</label>
                            <textarea class="form-control" id="edit_address" name="address" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
        <button type="submit" class="btn btn-success">Update Supplier</button>
    </div>                </form>
            </div>
        </div>
    </div>

    <!-- Show Supplier Modal -->
    <div class="modal fade" id="showSupplierModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h5 class="modal-title text-center w-100 font-weight-bold">Supplier Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-handshake fa-4x text-muted mb-2"></i>
                        <h4 class="font-weight-bold" id="show_name"></h4>
                        <p class="text-muted" id="show_phone"></p>
                    </div>
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b><i class="fas fa-id-badge mr-1"></i> Supplier ID</b>
                            <span class="float-right text-dark" id="show_supplier_code"></span>
                        </li>
                        <li class="list-group-item">
                            <b><i class="fas fa-envelope mr-1"></i> Email</b>
                            <span class="float-right text-dark" id="show_email"></span>
                        </li>
                        <li class="list-group-item">
                            <b><i class="fas fa-map-marker-alt mr-1"></i> Address</b>
                            <span class="float-right text-dark" id="show_address"></span>
                        </li>
                        <li class="list-group-item">
                            <b><i class="fas fa-clock mr-1"></i> Added At</b>
                            <span class="float-right text-dark" id="show_created_at"></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        function showNotification(type, message) {
            $('.alert').remove();
            var icon = type === 'success' ? 'fa-check' : 'fa-ban';
            var title = type === 'success' ? 'Success!' : 'Error!';
            var alertClass = 'alert-' + type;
            
            var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show">' +
                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                '<h5><i class="icon fas ' + icon + '"></i> ' + title + '</h5>' +
                message +
                '</div>';
                
            $('.row').first().before(alertHtml);
            
            setTimeout(function() {
                $('.' + alertClass).fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        }

        $(document).ready(function() {
            var table = $('#suppliers-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '{{ route('suppliers.getData') }}',
                    data: function(d) {
                        d.filter_name = $('#filter_name').val();
                        d.filter_email = $('#filter_email').val();
                        d.filter_phone = $('#filter_phone').val();
                    }
                },
                columns: [
                    { data: 'id', orderable: true },
                    { data: 'supplier_code', orderable: true },
                    { data: 'name', orderable: true },
                    { data: 'phone', orderable: true },
                    { data: 'email', orderable: true },
                    { data: 'address', orderable: true },
                    { data: 'actions', orderable: false, searchable: false }
                ],
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [[0, 'desc']]
            });

            $('#filter_name').select2({
                theme: 'bootstrap4',
                placeholder: 'Search by Name',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 0
            });

            $('#filter_name, #filter_email, #filter_phone').on('keyup change', function() {
                table.ajax.reload();
            });

            $('[data-toggle="tooltip"]').tooltip();

            table.on('draw.dt', function() {
                $('[data-toggle="tooltip"]').tooltip();
            });

            // Add Supplier Form
            $('#addSupplierModal form').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#addSupplierModal').modal('hide');
                        table.ajax.reload();
                        showNotification('success', response.message);
                    },
                    error: function(xhr) {
                        showNotification('danger', xhr.responseJSON.message || 'Error occurred');
                    }
                });
            });

            // Edit Supplier Form
            $('#editSupplierForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize() + '&_method=PUT',
                    success: function(response) {
                        $('#editSupplierModal').modal('hide');
                        table.ajax.reload();
                        showNotification('success', response.message);
                    },
                    error: function(xhr) {
                        showNotification('danger', xhr.responseJSON.message || 'Error occurred');
                    }
                });
            });

            // Reset Add Supplier form when modal is closed
            $('#addSupplierModal').on('hidden.bs.modal', function() {
                $(this).find('form')[0].reset();
                $(this).find('.is-invalid').removeClass('is-invalid');
                $(this).find('.invalid-feedback').remove();
            });
        });

        function editSupplier(id) {
            $.get('{{ route('suppliers.index') }}/' + id + '/edit', function(data) {
                $('#edit_supplier_id').val(data.id);
                $('#edit_name').val(data.name);
                $('#edit_phone').val(data.phone);
                $('#edit_email').val(data.email);
                $('#edit_address').val(data.address);
                $('#editSupplierForm').attr('action', '{{ route('suppliers.update', ':id') }}'.replace(':id', id));
                $('#editSupplierModal').modal('show');
            });
        }

        function showSupplier(id) {
            $.get('{{ route('suppliers.show', ':id') }}'.replace(':id', id), function(data) {
                $('#show_name').text(data.name);
                $('#show_phone').text(data.phone);
                $('#show_supplier_code').text(data.supplier_code || 'N/A');
                $('#show_email').text(data.email || 'N/A');
                $('#show_address').text(data.address || 'N/A');
                $('#show_created_at').text(data.created_at);
                $('#showSupplierModal').modal('show');
            });
        }

        function deleteSupplier(id) {
            if (confirm('Are you sure you want to delete this supplier?')) {
                $.ajax({
                    url: '{{ route('suppliers.destroy', ':id') }}'.replace(':id', id),
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        $('#suppliers-table').DataTable().ajax.reload();
                        showNotification('success', response.message);
                    },
                    error: function(xhr) {
                        showNotification('danger', xhr.responseJSON.message || 'Error occurred');
                    }
                });
            }
        }
    </script>
@stop

@section('css')
    <style>
        .modal label:not(.form-check-label):not(.custom-file-label) {
            color: #6c757d;
            font-size: 16px;
            font-weight: 600 !important;
        }
        .fa-4x {
            font-size: 2em;
        }
    </style>
@stop
