@extends('adminlte::page')

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
                                <select class="form-control @error('model') is-invalid @enderror" id="model" name="model" required>
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
            $('#vehicles-table').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('vehicles.data') }}",
                    "type": "GET"
                },
                "columns": [
                    { "data": "id", "orderable": true },
                    { "data": "type", "orderable": true },
                    { "data": "registration_number", "orderable": true },
                    { "data": "name", "orderable": true },
                    { "data": "model", "orderable": true },
                    { "data": "brand", "orderable": true },
                    { "data": "seating_capacity", "orderable": true },
                    { "data": "status", "orderable": false },
                    { "data": "actions", "orderable": false, "searchable": false }
                ],
                "responsive": true,
                "autoWidth": false,
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "language": {
                    "processing": "<i class='fas fa-spinner fa-spin'></i> Loading...",
                    "search": "Search vehicles:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ vehicles",
                    "infoEmpty": "No vehicles found",
                    "infoFiltered": "(filtered from _MAX_ total vehicles)",
                    "zeroRecords": "No matching vehicles found"
                },
                "order": [[0, "desc"]] // Default sort by ID descending
            });
        });
    </script>
@stop