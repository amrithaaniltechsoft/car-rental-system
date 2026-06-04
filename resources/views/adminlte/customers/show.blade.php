@extends('adminlte::page')

@section('title', 'Customer Details')

@section('content_header')
    <h1>Customer Details</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <i class="fas fa-user fa-5x text-muted"></i>
                    </div>
                    <h3 class="profile-username text-center">{{ $customer->name }}</h3>
                    <p class="text-muted text-center">{{ ucfirst($customer->customer_type) }} Customer</p>
                    @if($customer->customer_id)
                    <p class="text-center text-primary font-weight-bold">
                        <i class="fas fa-id-badge mr-1"></i> {{ $customer->customer_id }}
                    </p>
                    @endif

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Phone</b> <a class="float-right">{{ $customer->phone_number }}</a>
                        </li>
                        @if($customer->email)
                        <li class="list-group-item">
                            <b>Email</b> <a class="float-right">{{ $customer->email }}</a>
                        </li>
                        @endif
                        @if($customer->customer_type == 'individual')
                            @if($customer->passport_number)
                            <li class="list-group-item">
                                <b>Passport</b> <a class="float-right">{{ $customer->passport_number }}</a>
                            </li>
                            @endif
                        @endif
                    </ul>

                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary btn-block"><b>Edit</b></a>
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
                            @if($customer->customer_type == 'individual')
                                @if($customer->first_name || $customer->last_name)
                                    <strong><i class="fas fa-user mr-1"></i> Full Name</strong>
                                    <p class="text-muted">{{ $customer->first_name }} {{ $customer->last_name }}</p>
                                    <hr>
                                @endif
                                @if($customer->date_of_birth)
                                    <strong><i class="fas fa-birthday-cake mr-1"></i> Date of Birth</strong>
                                    <p class="text-muted">{{ $customer->date_of_birth ? $customer->date_of_birth->format('d M Y') : 'N/A' }}</p>
                                    <hr>
                                @endif
                                @if($customer->nationality)
                                    <strong><i class="fas fa-flag mr-1"></i> Nationality</strong>
                                    <p class="text-muted">{{ $customer->nationality }}</p>
                                    <hr>
                                @endif
                                @if($customer->residential_address)
                                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Residential Address</strong>
                                    <p class="text-muted">{{ $customer->residential_address }}</p>
                                    <hr>
                                @endif
                                @if($customer->driving_license_number)
                                    <strong><i class="fas fa-id-card mr-1"></i> Driving License Number</strong>
                                    <p class="text-muted">{{ $customer->driving_license_number }}</p>
                                    <hr>
                                @endif
                                @if($customer->license_expiry_date)
                                    <strong><i class="fas fa-calendar-times mr-1"></i> License Expiry Date</strong>
                                    <p class="text-muted">{{ $customer->license_expiry_date ? $customer->license_expiry_date->format('d M Y') : 'N/A' }}</p>
                                    <hr>
                                @endif
                                @if($customer->license_issue_country)
                                    <strong><i class="fas fa-globe mr-1"></i> License Issue Country</strong>
                                    <p class="text-muted">{{ $customer->license_issue_country }}</p>
                                    <hr>
                                @endif
                            @else
                                @if($customer->company_name)
                                    <strong><i class="fas fa-building mr-1"></i> Company Name</strong>
                                    <p class="text-muted">{{ $customer->company_name }}</p>
                                    <hr>
                                @endif
                                @if($customer->address)
                                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                                    <p class="text-muted">{{ $customer->address }}</p>
                                    <hr>
                                @endif
                            @endif

                            <strong><i class="fas fa-calendar-alt mr-1"></i> Registered At</strong>
                            <p class="text-muted">{{ $customer->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
