@extends('adminlte::page')

@section('plugins.Select2', true)

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
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control select2" id="filter_customer_id" style="width: 100%;">
                                <option value=""></option>
                                @foreach($customerIds as $cid)
                                    <option value="{{ $cid }}">{{ $cid }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control select2" id="filter_type" style="width: 100%;">
                                <option value=""></option>
                                <option value="individual">Individual</option>
                                <option value="company">Company</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control select2" id="filter_name" style="width: 100%;">
                                <option value=""></option>
                                @foreach($names as $n)
                                    <option value="{{ $n }}">{{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="filter_phone" placeholder="Search by Phone Number">
                        </div>
                    </div>
                    <table id="customers-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SI</th>
                                <th>Customer ID</th>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Phone Number</th>
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
                                <div class="form-group col-md-3">
                                    <label for="modal_first_name">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="modal_first_name" name="first_name" value="{{ old('first_name') }}">
                                    @error('first_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="modal_last_name">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="modal_last_name" name="last_name" value="{{ old('last_name') }}">
                                    @error('last_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="modal_date_of_birth">Date of Birth</label>
                                    <input type="text" class="form-control datepicker @error('date_of_birth') is-invalid @enderror" id="modal_date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" placeholder="YYYY-MM-DD" readonly>
                                    @error('date_of_birth')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="modal_nationality">Nationality</label>
                                    <input type="text" class="form-control @error('nationality') is-invalid @enderror" id="modal_nationality" name="nationality" value="{{ old('nationality') }}">
                                    @error('nationality')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="modal_indiv_phone_number">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('phone_number') is-invalid @enderror" id="modal_indiv_phone_number" name="phone_number" value="{{ old('phone_number') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    @error('phone_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="modal_email">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="modal_email" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="modal_passport_number">Passport Number</label>
                                    <input type="text" class="form-control @error('passport_number') is-invalid @enderror" id="modal_passport_number" name="passport_number" value="{{ old('passport_number') }}">
                                    @error('passport_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="modal_driving_license_number">Driving License Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('driving_license_number') is-invalid @enderror" id="modal_driving_license_number" name="driving_license_number" value="{{ old('driving_license_number') }}">
                                    @error('driving_license_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="modal_license_expiry_date">License Expiry Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control datepicker @error('license_expiry_date') is-invalid @enderror" id="modal_license_expiry_date" name="license_expiry_date" value="{{ old('license_expiry_date') }}" placeholder="YYYY-MM-DD" readonly>
                                    @error('license_expiry_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="modal_license_issue_country">License Issue Country <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('license_issue_country') is-invalid @enderror" id="modal_license_issue_country" name="license_issue_country" value="{{ old('license_issue_country') }}">
                                    @error('license_issue_country')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="modal_residential_address">Residential Address</label>
                                <textarea class="form-control @error('residential_address') is-invalid @enderror" id="modal_residential_address" name="residential_address" rows="3">{{ old('residential_address') }}</textarea>
                                @error('residential_address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Company Fields -->
                        <div id="modal-company-fields" style="display: none;">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="modal_company_name">Company Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="modal_company_name" name="company_name" value="{{ old('company_name') }}">
                                    @error('company_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="modal_comp_phone_number">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('phone_number') is-invalid @enderror" id="modal_comp_phone_number" name="phone_number" value="{{ old('phone_number') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    @error('phone_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="modal_email_company">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="modal_email_company" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="modal_comp_address">Address <span class="text-danger">*</span></label>
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
        .flatpickr-calendar {
            z-index: 999999 !important;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
            var customerTable = $('#customers-table').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('customers.data') }}",
                    "type": "GET",
                    "data": function(d) {
                        d.filter_customer_id = $('#filter_customer_id').val();
                        d.filter_type = $('#filter_type').val();
                        d.filter_name = $('#filter_name').val();
                        d.filter_phone = $('#filter_phone').val();
                    }
                },
                "columns": [
                    { "data": "id", "orderable": true },
                    { "data": "customer_id", "orderable": true },
                    { "data": "customer_type", "orderable": true },
                    { "data": "name", "orderable": true },
                    { "data": "address", "orderable": true },
                    { "data": "phone_number", "orderable": true },
                    { "data": "actions", "orderable": false, "searchable": false }
                ],
                "responsive": true,
                "autoWidth": false,
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "dom": 'lfrtip',
                "searching": false,
                "language": {
                    "processing": "<i class='fas fa-spinner fa-spin'></i> Loading...",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ customers",
                    "infoEmpty": "No customers found",
                    "infoFiltered": "(filtered from _MAX_ total customers)",
                    "zeroRecords": "No matching customers found"
                },
                "order": [[0, "desc"]]
            });

            customerTable.on('draw.dt', function() {
                $('[data-toggle="tooltip"]', $('#customers-table')).tooltip({ trigger: 'hover' });
            });

            $('#filter_customer_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Search by Customer ID',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 0
            });
            $('#filter_type').select2({
                theme: 'bootstrap4',
                placeholder: 'Search by Type',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 0
            });
            $('#filter_name').select2({
                theme: 'bootstrap4',
                placeholder: 'Search by Name',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 0
            });

            $('#filter_customer_id, #filter_type, #filter_name').on('change', function() {
                customerTable.draw();
            });
            $('#filter_phone').on('keyup', function() {
                customerTable.draw();
            });

            function toggleModalFields() {
                var type = $('input[name="customer_type"]:checked').val();
                if (type === 'company') {
                    $('#modal-individual-fields').hide().find('input, textarea').prop('disabled', true).prop('required', false);
                    $('#modal-company-fields').show().find('input, textarea').prop('disabled', false);
                    // Set required fields for company
                    $('#modal_company_name, #modal_comp_phone_number, #modal_comp_address').prop('required', true);
                } else if (type === 'individual') {
                    $('#modal-company-fields').hide().find('input, textarea').prop('disabled', true).prop('required', false);
                    $('#modal-individual-fields').show().find('input, textarea').prop('disabled', false);
                    // Set required fields for individual
                    $('#modal_first_name, #modal_last_name, #modal_indiv_phone_number, #modal_driving_license_number, #modal_license_expiry_date, #modal_license_issue_country').prop('required', true);
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

            // Initialize Flatpickr for modal date fields
            flatpickr('#modal_date_of_birth', {
                dateFormat: 'Y-m-d',
                allowInput: false,
                shorthandCurrentMonth: false,
                disable: [function(date) { return date > new Date(); }],
                onReady: function(selectedDates, dateStr, instance) {
                    var wrapper = instance.calendarContainer.querySelector('.numInputWrapper');
                    if (!wrapper) return;
                    var select = document.createElement('select');
                    select.style.marginLeft = '4px';
                    select.style.fontSize = 'inherit';
                    select.style.padding = '1px 2px';
                    select.style.border = 'none';
                    select.style.borderRadius = '0';
                    select.style.fontWeight = 'inherit';
                    var curr = new Date().getFullYear();
                    for (var y = curr; y >= curr - 100; y--) {
                        var opt = document.createElement('option');
                        opt.value = y;
                        opt.textContent = y;
                        if (y === instance.currentYear) opt.selected = true;
                        select.appendChild(opt);
                    }
                    select.addEventListener('change', function(e) {
                        instance.changeYear(parseInt(e.target.value));
                    });
                    wrapper.style.display = 'none';
                    wrapper.parentNode.insertBefore(select, wrapper);
                    instance._yearSelect = select;
                },
                onYearChange: function(selectedDates, dateStr, instance) {
                    if (instance._yearSelect) instance._yearSelect.value = instance.currentYear;
                }
            });

            flatpickr('#modal_license_expiry_date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
                shorthandCurrentMonth: false,
                disable: [function(date) { return date < new Date(); }],
                onReady: function(selectedDates, dateStr, instance) {
                    var wrapper = instance.calendarContainer.querySelector('.numInputWrapper');
                    if (!wrapper) return;
                    var select = document.createElement('select');
                    select.style.marginLeft = '4px';
                    select.style.fontSize = 'inherit';
                    select.style.padding = '1px 2px';
                    select.style.border = 'none';
                    select.style.borderRadius = '0';
                    select.style.fontWeight = 'inherit';
                    var curr = new Date().getFullYear();
                    for (var y = curr; y <= curr + 20; y++) {
                        var opt = document.createElement('option');
                        opt.value = y;
                        opt.textContent = y;
                        if (y === instance.currentYear) opt.selected = true;
                        select.appendChild(opt);
                    }
                    select.addEventListener('change', function(e) {
                        instance.changeYear(parseInt(e.target.value));
                    });
                    wrapper.style.display = 'none';
                    wrapper.parentNode.insertBefore(select, wrapper);
                    instance._yearSelect = select;
                },
                onYearChange: function(selectedDates, dateStr, instance) {
                    if (instance._yearSelect) instance._yearSelect.value = instance.currentYear;
                }
            });

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
