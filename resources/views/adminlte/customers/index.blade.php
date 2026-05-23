@extends('adminlte::page')

@section('title', 'Customers')

@section('content_header')
    <h1>Customers Management</h1>
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
                    <h3 class="card-title">Customers List</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addCustomerModal">
                            <i class="fas fa-plus"></i> Add Customer
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="customers-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SI</th>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Phone Number</th>
                                <th>ID Card / Reg ID</th>
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

    <div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" id="addCustomerModalLabel">Add New Customer</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-12 text-center">
                                <label class="d-block">Customer Type</label>
                                <div class="form-check form-check-inline mr-4">
                                    <input class="form-check-input @error('customer_type') is-invalid @enderror" 
                                           type="radio" 
                                           name="customer_type" 
                                           id="customer_type_individual" 
                                           value="individual" 
                                           {{ old('customer_type', 'individual') == 'individual' ? 'checked' : '' }} 
                                           required>
                                    <label class="form-check-label font-weight-normal text-secondary" style="font-size: 16px;" for="customer_type_individual">Individual</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input @error('customer_type') is-invalid @enderror" 
                                           type="radio" 
                                           name="customer_type" 
                                           id="customer_type_company" 
                                           value="company" 
                                           {{ old('customer_type') == 'company' ? 'checked' : '' }} 
                                           required>
                                    <label class="form-check-label font-weight-normal text-secondary" style="font-size: 16px;" for="customer_type_company">Company</label>
                                </div>
                                @error('customer_type')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Individual Fields -->
                        <div id="modal-individual-fields" style="display: none;">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="modal_name">Full Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="modal_name" name="name" value="{{ old('name') }}">
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="modal_indiv_phone_number">Phone Number</label>
                                    <input type="text" class="form-control @error('phone_number') is-invalid @enderror" id="modal_indiv_phone_number" name="phone_number" value="{{ old('phone_number') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    @error('phone_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="modal_id_card_number">ID Card Number</label>
                                    <input type="text" class="form-control @error('id_card_number') is-invalid @enderror" id="modal_id_card_number" name="id_card_number" value="{{ old('id_card_number') }}">
                                    @error('id_card_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="modal_indiv_address">Address</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" id="modal_indiv_address" name="address" rows="3">{{ old('address') }}</textarea>
                                @error('address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Company Fields -->
                        <div id="modal-company-fields" style="display: none;">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="modal_company_name">Company Name</label>
                                    <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="modal_company_name" name="company_name" value="{{ old('company_name') }}">
                                    @error('company_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="modal_comp_phone_number">Phone Number</label>
                                    <input type="text" class="form-control @error('phone_number') is-invalid @enderror" id="modal_comp_phone_number" name="phone_number" value="{{ old('phone_number') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    @error('phone_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="modal_company_registration_id">Company Registration ID</label>
                                    <input type="text" class="form-control @error('company_registration_id') is-invalid @enderror" id="modal_company_registration_id" name="company_registration_id" value="{{ old('company_registration_id') }}">
                                    @error('company_registration_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="modal_comp_address">Address</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" id="modal_comp_address" name="address" rows="3">{{ old('address') }}</textarea>
                                @error('address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Customer Modal -->
    <div class="modal fade" id="viewCustomerModal" tabindex="-1" role="dialog" aria-labelledby="viewCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" id="viewCustomerModalLabel">Customer Details</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="viewCustomerModalBody">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-3x"></i>
                        <p class="mt-2">Loading details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" role="dialog" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h5 class="modal-title text-center w-100 font-weight-bold" id="editCustomerModalLabel">Edit Customer</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="editCustomerModalContainer">
                    <div class="modal-body text-center">
                        <i class="fas fa-spinner fa-spin fa-3x text-success"></i>
                        <p class="mt-2">Loading details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')
    <style>
        .modal label:not(.form-check-label):not(.custom-file-label) {
            color: #6c757d;
            font-size: 16px;
            font-weight: 600 !important;
        }
    </style>
@stop

@section('js')
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                $('#addCustomerModal').modal('show');
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
            $('#customers-table').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('customers.data') }}",
                    "type": "GET"
                },
                "columns": [
                    { "data": "id", "orderable": true },
                    { "data": "customer_type", "orderable": true },
                    { "data": "name", "orderable": true },
                    { "data": "address", "orderable": true },
                    { "data": "phone_number", "orderable": true },
                    { "data": "id_card_number", "orderable": true },
                    { "data": "actions", "orderable": false, "searchable": false }
                ],
                "responsive": true,
                "autoWidth": false,
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "language": {
                    "processing": "<i class='fas fa-spinner fa-spin'></i> Loading...",
                    "search": "Search customers:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ customers",
                    "infoEmpty": "No customers found",
                    "infoFiltered": "(filtered from _MAX_ total customers)",
                    "zeroRecords": "No matching customers found"
                },
                "order": [[0, "desc"]] // Default sort by ID descending
            });

            function toggleModalFields() {
                var type = $('input[name="customer_type"]:checked').val();
                if (type === 'company') {
                    $('#modal-individual-fields').hide().find('input, textarea').prop('disabled', true).prop('required', false);
                    $('#modal-company-fields').show().find('input, textarea').prop('disabled', false).prop('required', true);
                } else if (type === 'individual') {
                    $('#modal-company-fields').hide().find('input, textarea').prop('disabled', true).prop('required', false);
                    $('#modal-individual-fields').show().find('input, textarea').prop('disabled', false).prop('required', true);
                } else {
                    $('#modal-individual-fields').hide().find('input, textarea').prop('disabled', true).prop('required', false);
                    $('#modal-company-fields').hide().find('input, textarea').prop('disabled', true).prop('required', false);
                }
            }

            $('input[name="customer_type"]').change(function() {
                toggleModalFields();
            });

            // Set correct initial state on page load
            toggleModalFields();

            // Handle View Customer button click
            $(document).on('click', '.view-customer-btn', function() {
                var url = $(this).data('url');

                // Show modal with loading state
                $('#viewCustomerModalBody').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x text-success"></i><p class="mt-2">Loading details...</p></div>');
                $('#viewCustomerModal').modal('show');

                // Fetch customer details via AJAX
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#viewCustomerModalBody').html(response);
                    },
                    error: function() {
                        $('#viewCustomerModalBody').html('<div class="alert alert-danger">Error loading customer details. Please try again.</div>');
                    }
                });
            });
        });

        // Handle Edit Vehicle button click
        $(document).on('click', '.edit-customer-btn', function() {
            var url = $(this).data('url');
            
            // Show modal and loading state
            $('#editCustomerModalContainer').html('<div class="modal-body text-center"><i class="fas fa-spinner fa-spin fa-3x text-success"></i><p class="mt-2">Loading details...</p></div>');
            $('#editCustomerModal').modal('show');
            
            // Fetch data
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#editCustomerModalContainer').html(response);
                    
                },
                error: function() {
                    $('#editCustomertModalContainer').html('<div class="modal-body"><div class="alert alert-danger">Error loading vehicle details. Please try again.</div></div>');
                }
            });
        });

       
    </script>

    
@stop
