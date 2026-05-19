@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Car Rental System Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ \App\Models\Vehicle::count() }}</h3>
                    <p>Total Vehicles</p>
                </div>
                <div class="icon">
                    <i class="fas fa-car"></i>
                </div>
                <a href="{{ route('vehicles.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ \App\Models\Customer::count() }}</h3>
                    <p>Total Customers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('customers.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ \App\Models\Booking::count() }}</h3>
                    <p>Total Bookings</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <a href="{{ route('bookings.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ \App\Models\Invoice::count() }}</h3>
                    <p>Total Invoices</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <a href="{{ route('invoices.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('vehicles.create') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i> Add Vehicle
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('customers.create') }}" class="btn btn-success btn-block">
                                <i class="fas fa-user-plus"></i> Add Customer
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('bookings.create') }}" class="btn btn-warning btn-block">
                                <i class="fas fa-calendar-plus"></i> New Booking
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('reports.index') }}" class="btn btn-info btn-block">
                                <i class="fas fa-chart-bar"></i> View Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Bookings</h3>
                </div>
                <div class="card-body">
                    @forelse(\App\Models\Booking::with(['customer', 'vehicle'])->latest()->take(5)->get() as $booking)
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>{{ $booking->customer->name }}</strong><br>
                                <small>{{ $booking->vehicle->name }} - {{ $booking->vehicle->model }}</small>
                            </div>
                            <div>
                                <span class="badge badge-{{ $booking->status == 'confirmed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </div>
                        </div>
                        <hr>
                    @empty
                        <p class="text-muted">No recent bookings</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Invoices</h3>
                </div>
                <div class="card-body">
                    @forelse(\App\Models\Invoice::with('customer')->latest()->take(5)->get() as $invoice)
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>{{ $invoice->customer->name }}</strong><br>
                                <small>Invoice #{{ $invoice->invoice_number }}</small>
                            </div>
                            <div>
                                <span class="badge badge-{{ $invoice->status == 'paid' ? 'success' : 'danger' }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                                <br>
                                <small>{{ number_format($invoice->amount, 3) }} OMR</small>
                            </div>
                        </div>
                        <hr>
                    @empty
                        <p class="text-muted">No recent invoices</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
@stop

@section('js')
    {{-- <script> console.log("Hi, I'm using the Laravel-AdminLTE package!"); </script> --}}
@stop
