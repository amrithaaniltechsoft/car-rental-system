@extends('adminlte::page')

@section('title', 'Supplier Details')

@section('content_header')
    <h1>Supplier Details</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <i class="fas fa-handshake fa-5x text-muted"></i>
                    </div>
                    <h3 class="profile-username text-center">{{ $supplier->name }}</h3>
                    <p class="text-muted text-center">{{ $supplier->phone }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a href="#" class="nav-link active">Details</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b><i class="fas fa-envelope mr-1"></i> Email</b> <a class="float-right">{{ $supplier->email ?? 'N/A' }}</a>
                        </li>
                        <li class="list-group-item">
                            <b><i class="fas fa-map-marker-alt mr-1"></i> Address</b> <a class="float-right">{{ $supplier->address ?? 'N/A' }}</a>
                        </li>
                    </ul>
                </div>
                <div class="card-footer">
                    <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop
