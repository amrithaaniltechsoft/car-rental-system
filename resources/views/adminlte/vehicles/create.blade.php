@extends('adminlte::page')

@section('title', 'Add Vehicle')

@section('content_header')
    <h1>Add New Vehicle</h1>
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
                    <h3 class="card-title">Vehicle Information</h3>
                </div>
                <form action="{{ route('vehicles.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Vehicle Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="brand">Brand</label>
                                    <select class="form-control @error('brand') is-invalid @enderror"
                                            id="brand" name="brand" required>
                                        <option value="" disabled selected>Select Brand</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->name }}" {{ old('brand') == $brand->name ? 'selected' : '' }}>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('brand')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="model">Model (Year)</label>
                                    <select class="form-control @error('model') is-invalid @enderror"
                                            id="model" name="model" required>
                                        <option value="" disabled selected>Select Year</option>
                                        @for($year = 2000; $year <= 2100; $year++)
                                            <option value="{{ $year }}" {{ old('model') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                    @error('model')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="type">Vehicle Type</label>
                                    <select class="form-control @error('type') is-invalid @enderror"
                                            id="type" name="type" required>
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
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="registration_number">Registration Number</label>
                                    <input type="text" class="form-control @error('registration_number') is-invalid @enderror"
                                           id="registration_number" name="registration_number" value="{{ old('registration_number') }}" required>
                                    @error('registration_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fuel_type">Fuel Type</label>
                                    <select class="form-control @error('fuel_type') is-invalid @enderror"
                                            id="fuel_type" name="fuel_type" required>
                                        <option value="">Select Fuel Type</option>
                                        @foreach($fuelTypes as $fuelType)
                                            <option value="{{ $fuelType->name }}" {{ old('fuel_type') == $fuelType->name ? 'selected' : '' }}>{{ ucfirst($fuelType->name) }}</option>
                                        @endforeach
                                    </select>
                                    @error('fuel_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="seating_capacity">Seating Capacity</label>
                                    <input type="number" class="form-control @error('seating_capacity') is-invalid @enderror"
                                           id="seating_capacity" name="seating_capacity" value="{{ old('seating_capacity') }}" min="1" required>
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
                                              id="rc_book_details" name="rc_book_details" rows="3">{{ old('rc_book_details') }}</textarea>
                                    @error('rc_book_details')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="insurance_details">Insurance Details</label>
                                    <textarea class="form-control @error('insurance_details') is-invalid @enderror"
                                              id="insurance_details" name="insurance_details" rows="3">{{ old('insurance_details') }}</textarea>
                                    @error('insurance_details')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Save Vehicle</button>
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
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@stop