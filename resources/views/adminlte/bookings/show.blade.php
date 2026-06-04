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
                    <strong><i class="fas fa-hashtag mr-1"></i> Booking ID</strong>
                    <p class="text-muted">
                        {{ $booking->booking_id ?: 'N/A' }}
                    </p>
                    <hr>
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

                    <hr>
                    <strong><i class="fas fa-calendar-alt mr-1"></i> Booking Date</strong>
                    <p class="text-muted">
                        {{ $booking->booking_date ? $booking->booking_date->format('d M Y') : 'N/A' }}
                    </p>
                    <hr>
                    <strong><i class="fas fa-clock mr-1"></i> Pickup Date & Time</strong>
                    <p class="text-muted">
                        {{ $booking->pickup_datetime ? $booking->pickup_datetime->format('d M Y H:i') : 'N/A' }}
                    </p>
                    <hr>
                    <strong><i class="fas fa-clock mr-1"></i> Return Date & Time</strong>
                    <p class="text-muted">
                        {{ $booking->return_datetime ? $booking->return_datetime->format('d M Y H:i') : 'N/A' }}
                    </p>
                    <hr>
                    <strong><i class="fas fa-hourglass-half mr-1"></i> Rental Duration</strong>
                    <p class="text-muted">
                        {{ $booking->rental_duration ?: 'N/A' }}
                    </p>
                    <hr>
                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Pickup Location</strong>
                    <p class="text-muted">
                        {{ $booking->pickup_location ?: 'N/A' }}
                    </p>
                    <hr>
                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Return Location</strong>
                    <p class="text-muted">
                        {{ $booking->return_location ?: 'N/A' }}
                    </p>
                    <hr>
                    <strong><i class="fas fa-history mr-1"></i> Created At</strong>
                    <p class="text-muted">{{ $booking->created_at->format('d M Y') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Financial & Additional Details</h3>
                </div>
                <div class="card-body">
                    @if($booking->invoice)
                        <table class="table table-borderless">
                            <tr>
                                <td style="text-align: center; padding-top: 10px;">Subtotal</td>
                                <td class="text-right">{{ number_format((float)$booking->invoice->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="text-align: center; padding-top: 10px;">VAT Amount</td>
                                <td class="text-right">{{ number_format((float)$booking->invoice->vat, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="width: 120px; text-align: center; padding-top: 10px;"><strong style="color: #6c757d;">Total</strong></td>
                                <td class="text-right"><strong>{{ number_format((float)$booking->invoice->total, 2) }}</strong></td>
                            </tr>
                        </table>
                    @else
                        <p class="text-muted">No invoice generated yet.</p>
                        <p class="text-muted"><strong>Booking Total:</strong> <span class="text-right">{{ number_format((float)$booking->total_amount, 2) }}</span></p>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-warning">Edit Booking</a>
                    <a href="{{ route('bookings.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
@stop
