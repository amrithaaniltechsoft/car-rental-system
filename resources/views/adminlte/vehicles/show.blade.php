@extends('adminlte::page')

@section('title', 'Vehicle Details')

@section('content_header')
    <h1>Vehicle Details</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <i class="fas fa-car fa-5x text-muted"></i>
                    </div>
                    <h3 class="profile-username text-center">{{ $vehicle->name }}</h3>
                    <p class="text-muted text-center">{{ $vehicle->brand }} {{ $vehicle->model }}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Registration</b> <a class="float-right">{{ $vehicle->registration_number }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Vehicle Type</b> <a class="float-right">{{ ucfirst($vehicle->type) ?: 'N/A' }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Fuel Type</b> <a class="float-right">{{ ucfirst($vehicle->fuel_type) }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Seating Capacity</b> <a class="float-right">{{ $vehicle->seating_capacity }}</a>
                        </li>
                    </ul>

                    <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-primary btn-block"><b>Edit</b></a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#details" data-toggle="tab">Details</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane" id="details">
                            <strong><i class="fas fa-book mr-1"></i> RC Book Details</strong>
                            <p class="text-muted">
                                {{ $vehicle->rc_book_details ?: 'No details recorded.' }}
                            </p>
                            <hr>
                            <strong><i class="fas fa-shield-alt mr-1"></i> Insurance Details</strong>
                            <p class="text-muted">
                                {{ $vehicle->insurance_details ?: 'No details recorded.' }}
                            </p>
                            <hr>
                            <strong><i class="fas fa-calendar-alt mr-1"></i> Added At</strong>
                            <p class="text-muted">{{ $vehicle->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
