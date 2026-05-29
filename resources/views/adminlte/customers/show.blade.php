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
                    <p class="text-muted text-center">Customer</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Phone</b> <a class="float-right">{{ $customer->phone_number }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>ID Card</b> <a class="float-right">{{ $customer->id_card_number }}</a>
                        </li>
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
                            <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                            <p class="text-muted">
                                {{ $customer->address }}
                            </p>
                            <hr>
                            <strong><i class="fas fa-calendar-alt mr-1"></i> Registered At</strong>
                            <p class="text-muted">{{ $customer->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
