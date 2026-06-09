@extends('adminlte::page')

@section('plugins.Select2', true)

@section('title', 'Vehicles')

@section('content_header')
    <h1>Vehicles Management</h1>
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
                    <h3 class="card-title">Vehicles List</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addVehicleModal">
                            <i class="fas fa-plus"></i> Add Vehicle
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control select2" id="filter_name" style="width: 100%;">
                                <option value=""></option>
                                @foreach($vehicleNames as $name)
                                    <option value="{{ $name }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control select2" id="filter_brand" style="width: 100%;">
                                <option value=""></option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->name }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control select2" id="filter_type" style="width: 100%;">
                                <option value=""></option>
                                @foreach($types as $type)
                                    <option value="{{ $type->name }}">{{ ucfirst($type->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control select2" id="filter_registration" style="width: 100%;">
                                <option value=""></option>
                                @foreach($registrations as $reg)
                                    <option value="{{ $reg }}">{{ $reg }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <table id="vehicles-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SI</th>
                                <th>Type</th>
                                <th>Registration</th>
                                <th>Name</th>
                                <th>Model</th>
                                <th>Brand</th>
                                <th>Capacity</th>
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

    <div class="modal fade" id="addVehicleModal" tabindex="-1" role="dialog" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" id="addVehicleModalLabel">Add New Vehicle</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('vehicles.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="name">Vehicle Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="brand">Brand</label>
                                <select class="form-control @error('brand') is-invalid @enderror" id="brand" name="brand" required>
                                    <option value="" disabled selected>Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->name }}" {{ old('brand') == $brand->name ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                @error('brand')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="model">Model (Year)</label>
                                <select class="form-control select2 @error('model') is-invalid @enderror" id="model" name="model" required style="width: 100%;">
                                    <option value="" disabled selected>Select Year</option>
                                    @for($year = 2000; $year <= 2100; $year++)
                                        <option value="{{ $year }}" {{ old('model') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endfor
                                </select>
                                @error('model')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="type">Vehicle Type</label>
                                <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="" disabled selected>Select Type</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->name }}" {{ old('type') == $type->name ? 'selected' : '' }}>{{ ucfirst($type->name) }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="registration_number">Registration Number</label>
                                <input type="text" class="form-control @error('registration_number') is-invalid @enderror" id="registration_number" name="registration_number" value="{{ old('registration_number') }}" required>
                                @error('registration_number')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="fuel_type">Fuel Type</label>
                                <select class="form-control @error('fuel_type') is-invalid @enderror" id="fuel_type" name="fuel_type" required>
                                    <option value="">Select Fuel Type</option>
                                    @foreach($fuelTypes as $fuelType)
                                        <option value="{{ $fuelType->name }}" {{ old('fuel_type') == $fuelType->name ? 'selected' : '' }}>{{ ucfirst($fuelType->name) }}</option>
                                    @endforeach
                                </select>
                                @error('fuel_type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="seating_capacity">Seating Capacity</label>
                                <input type="number" class="form-control @error('seating_capacity') is-invalid @enderror" id="seating_capacity" name="seating_capacity" value="{{ old('seating_capacity') }}" min="1" required>
                                @error('seating_capacity')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="rc_book_details">RC Book Details</label>
                                <textarea class="form-control @error('rc_book_details') is-invalid @enderror" id="rc_book_details" name="rc_book_details" rows="3">{{ old('rc_book_details') }}</textarea>
                                @error('rc_book_details')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="insurance_details">Insurance Details</label>
                                <textarea class="form-control @error('insurance_details') is-invalid @enderror" id="insurance_details" name="insurance_details" rows="3">{{ old('insurance_details') }}</textarea>
                                @error('insurance_details')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Vehicle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- View Vehicle Modal -->
    <div class="modal fade" id="viewVehicleModal" tabindex="-1" role="dialog" aria-labelledby="viewVehicleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h4 class="modal-title text-center w-100" id="viewVehicleModalLabel">Vehicle Details</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="viewVehicleModalBody">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-3x"></i>
                        <p class="mt-2">Loading details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Vehicle Modal -->
    <div class="modal fade" id="editVehicleModal" tabindex="-1" role="dialog" aria-labelledby="editVehicleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border: 1px solid #28a745;">
                <div class="modal-header justify-content-center" style="background-color: #28a745; color: #ffffff; padding: 10px 10px;">
                    <h5 class="modal-title text-center w-100 font-weight-bold" id="editVehicleModalLabel">Edit Vehicle</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="editVehicleModalContainer">
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
                $('#addVehicleModal').modal('show');
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
            // Initialize Select2 for Model (Year) inside the Add Vehicle Modal
            $('#addVehicleModal #model').select2({
                theme: 'bootstrap4',
                placeholder: 'Select Year',
                allowClear: true,
                dropdownParent: $('#addVehicleModal'),
                width: '100%',
                minimumResultsForSearch: 0
            });

            // Auto-focus Select2 search input when dropdown opens
            $(document).on('select2:open', function(e) {
                window.setTimeout(function () {
                    var searchField = document.querySelector('.select2-container--open .select2-search__field');
                    if (searchField) {
                        searchField.focus();
                    }
                }, 50);
            });

            var vehicleTable = $('#vehicles-table').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('vehicles.data') }}",
                    "type": "GET",
                    "data": function(d) {
                        d.filter_name = $('#filter_name').val();
                        d.filter_brand = $('#filter_brand').val();
                        d.filter_type = $('#filter_type').val();
                        d.filter_registration = $('#filter_registration').val();
                    }
                },
                "columns": [
                    { "data": "id", "orderable": true },
                    { "data": "type", "orderable": true },
                    { "data": "registration_number", "orderable": true },
                    { "data": "name", "orderable": true },
                    { "data": "model", "orderable": true },
                    { "data": "brand", "orderable": true },
                    { "data": "seating_capacity", "orderable": true },
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
                    "info": "Showing _START_ to _END_ of _TOTAL_ vehicles",
                    "infoEmpty": "No vehicles found",
                    "infoFiltered": "(filtered from _MAX_ total vehicles)",
                    "zeroRecords": "No matching vehicles found"
                },
                "order": [[0, "desc"]]
            });

            $('#filter_brand').select2({
                theme: 'bootstrap4',
                placeholder: 'Search by Brand',
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

            $('#filter_registration').select2({
                theme: 'bootstrap4',
                placeholder: 'Search by Registration',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 0
            });

            $('#filter_name, #filter_brand, #filter_type, #filter_registration').on('change', function() {
                vehicleTable.draw();
            });

            // Handle View Vehicle button click
            $(document).on('click', '.view-vehicle-btn', function() {
                var url = $(this).data('url');
                
                // Show modal and loading state
                $('#viewVehicleModalBody').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x text-success"></i><p class="mt-2">Loading details...</p></div>');
                $('#viewVehicleModal').modal('show');
                
                // Fetch data
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#viewVehicleModalBody').html(response);
                    },
                    error: function() {
                        $('#viewVehicleModalBody').html('<div class="alert alert-danger">Error loading vehicle details. Please try again.</div>');
                    }
                });
            });

            // Handle Edit Vehicle button click
            $(document).on('click', '.edit-vehicle-btn', function() {
                var url = $(this).data('url');
                
                // Show modal and loading state
                $('#editVehicleModalContainer').html('<div class="modal-body text-center"><i class="fas fa-spinner fa-spin fa-3x text-success"></i><p class="mt-2">Loading details...</p></div>');
                $('#editVehicleModal').modal('show');
                
                // Fetch data
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#editVehicleModalContainer').html(response);
                        // Initialize Select2 on the dynamically loaded #model input
                        $('#editVehicleModalContainer #model').select2({
                            theme: 'bootstrap4',
                            placeholder: 'Select Year',
                            allowClear: true,
                            dropdownParent: $('#editVehicleModal'),
                            width: '100%',
                            minimumResultsForSearch: 0
                        });
                    },
                    error: function() {
                        $('#editVehicleModalContainer').html('<div class="modal-body"><div class="alert alert-danger">Error loading vehicle details. Please try again.</div></div>');
                    }
                });
            });

            // Handle AJAX form submission for both Add and Edit modals
            $(document).on('submit', '#addVehicleModal form, #editVehicleModal form', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var modal = form.closest('.modal');
                var submitBtn = form.find('button[type="submit"]');
                var originalBtnHtml = submitBtn.html();
                
                // Clear previous validation styling and errors
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
                
                // Set loading state on submit button
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                
                var actionUrl = form.attr('action');
                var method = form.find('input[name="_method"]').val() || 'POST';
                
                $.ajax({
                    url: actionUrl,
                    type: method,
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        submitBtn.prop('disabled', false).html(originalBtnHtml);
                        
                        if (response.success) {
                            // Hide the modal
                            modal.modal('hide');
                            
                            // Reset the form if it was the add modal
                            if (modal.attr('id') === 'addVehicleModal') {
                                form[0].reset();
                                form.find('.select2').val(null).trigger('change');
                            }
                            
                            // Reload DataTable without losing pagination/position
                            $('#vehicles-table').DataTable().ajax.reload(null, false);
                            
                            // Display dynamic success alert at the top of the content
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
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                var input = form.find('[name="' + field + '"]');
                                if (input.length) {
                                    input.addClass('is-invalid');
                                    var errorSpan = $('<span class="invalid-feedback d-block"></span>').text(messages[0]);
                                    
                                    // Custom error positioning for Select2 containers
                                    if (input.hasClass('select2-hidden-accessible')) {
                                        input.next('.select2-container').after(errorSpan);
                                    } else {
                                        input.after(errorSpan);
                                    }
                                }
                            });
                        } else {
                            alert('An error occurred. Please try again.');
                        }
                    }
                });
            });

            // Reset Add Vehicle form when modal is closed (X button or backdrop click)
            $('#addVehicleModal').on('hidden.bs.modal', function() {
                var form = $(this).find('form');
                form[0].reset();
                // Reset Select2 dropdown
                form.find('#model').val(null).trigger('change');
                // Clear any validation errors
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
            });
        });
    </script>
@stop