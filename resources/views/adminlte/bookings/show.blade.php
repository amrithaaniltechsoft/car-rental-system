@extends('adminlte::page')

@section('title', 'Booking Details')

@section('content_header')
    <h1>Booking Details</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">General Information</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fas fa-car mr-1"></i> Vehicle</strong>
                    <p class="text-muted">
                        {{ $booking->vehicle->name }} ({{ $booking->vehicle->registration_number }})
                    </p>
                    <hr>
                    <strong><i class="fas fa-user mr-1"></i> Customer</strong>
                    <p class="text-muted">
                        {{ $booking->customer->name }}
                    </p>
                    <hr>
                    <strong><i class="fas fa-calendar-alt mr-1"></i> Booking Period</strong>
                    <p class="text-muted">
                        {{ $booking->from_date->format('d M Y') }} to {{ $booking->to_date->format('d M Y') }}
                    </p>
                    <hr>
                    <strong><i class="fas fa-info-circle mr-1"></i> Status</strong>
                    <p class="text-muted">
                        @if($booking->status == 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif($booking->status == 'confirmed')
                            <span class="badge badge-primary">Confirmed</span>
                        @elseif($booking->status == 'on_hold')
                            <span class="badge badge-info">On Hold</span>
                        @else
                            <span class="badge badge-danger">Cancelled</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Financial & Additional Details</h3>
                </div>
                <div class="card-body">

                    <strong><i class="fas fa-sticky-note mr-1"></i> Notes</strong>
                    <p class="text-muted">
                        {{ $booking->notes ?: 'No additional notes' }}
                    </p>
                    <hr>
                    <strong><i class="fas fa-clock mr-1"></i> Created At</strong>
                    <p class="text-muted">{{ $booking->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="card-footer">
                    <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-warning">Edit Booking</a>
                    <a href="{{ route('bookings.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
@stop
