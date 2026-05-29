@extends('adminlte::page')

@section('plugins.Select2', true)

@section('title', 'Edit Vehicle')

@section('content_header')
    <h1>Edit Vehicle</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Vehicle Information</h3>
                </div>
                <form action="{{ route('vehicles.update', $vehicle) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Vehicle Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $vehicle->name) }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="model">Model (Year)</label>
                                    <select class="form-control select2 @error('model') is-invalid @enderror"
                                            id="model" name="model" required style="width: 100%;">
                                        <option value="" disabled {{ old('model', $vehicle->model) ? '' : 'selected' }}>Select Year</option>
                                        @for($year = 2000; $year <= 2100; $year++)
                                            <option value="{{ $year }}" {{ old('model', $vehicle->model) == $year ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                    @error('model')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="brand">Brand</label>
                                    <select class="form-control @error('brand') is-invalid @enderror"
                                            id="brand" name="brand" required>
                                        <option value="" disabled {{ old('brand', $vehicle->brand) ? '' : 'selected' }}>Select Brand</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->name }}" {{ old('brand', $vehicle->brand) == $brand->name ? 'selected' : '' }}>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('brand')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Vehicle Type</label>
                                    <select class="form-control @error('type') is-invalid @enderror"
                                            id="type" name="type" required>
                                        <option value="" disabled {{ old('type', $vehicle->type) ? '' : 'selected' }}>Select Type</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->name }}" {{ old('type', $vehicle->type) == $type->name ? 'selected' : '' }}>{{ ucfirst($type->name) }}</option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="registration_number">Registration Number</label>
                                    <input type="text" class="form-control @error('registration_number') is-invalid @enderror"
                                           id="registration_number" name="registration_number" value="{{ old('registration_number', $vehicle->registration_number) }}" required>
                                    @error('registration_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fuel_type">Fuel Type</label>
                                    <select class="form-control @error('fuel_type') is-invalid @enderror"
                                            id="fuel_type" name="fuel_type" required>
                                        <option value="">Select Fuel Type</option>
                                        @foreach($fuelTypes as $fuelType)
                                            <option value="{{ $fuelType->name }}" {{ old('fuel_type', $vehicle->fuel_type) == $fuelType->name ? 'selected' : '' }}>{{ ucfirst($fuelType->name) }}</option>
                                        @endforeach
                                    </select>
                                    @error('fuel_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="seating_capacity">Seating Capacity</label>
                                    <input type="number" class="form-control @error('seating_capacity') is-invalid @enderror"
                                           id="seating_capacity" name="seating_capacity" value="{{ old('seating_capacity', $vehicle->seating_capacity) }}" min="1" required>
                                    @error('seating_capacity')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rc_book_details">RC Book Details</label>
                                    <textarea class="form-control @error('rc_book_details') is-invalid @enderror"
                                              id="rc_book_details" name="rc_book_details" rows="3">{{ old('rc_book_details', $vehicle->rc_book_details) }}</textarea>
                                    @error('rc_book_details')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="insurance_details">Insurance Details</label>
                                    <textarea class="form-control @error('insurance_details') is-invalid @enderror"
                                              id="insurance_details" name="insurance_details" rows="3">{{ old('insurance_details', $vehicle->insurance_details) }}</textarea>
                                    @error('insurance_details')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update Vehicle</button>
                        <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .content label:not(.form-check-label):not(.custom-file-label) {
            font-size: 17px;
            font-weight: 600 !important;
            color: #6c757d;
        }
    </style>
@stop

@section('js')
    <script>
        // Initialize Select2 for Model (Year) with search focus
$(document).ready(function() {
    $('#model').select2({
        theme: 'bootstrap4',
        placeholder: 'Select Year',
        allowClear: true,
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
});
    </script>
@stop

