<div class="row">
    <div class="col-md-5">
        <div class="text-center">
            <i class="fas fa-calendar-check fa-5x text-muted"></i>
        </div>
        <h3 class="profile-username text-center">Booking</h3>
        <p class="text-muted text-center">{{ $booking->from_date->format('d M Y') }} - {{ $booking->to_date->format('d M Y') }}</p>

        <ul class="list-group list-group-unbordered mb-3">
            <li class="list-group-item">
                <b>Vehicle</b> <span class="float-right text-dark">{{ $booking->vehicle->name }} ({{ $booking->vehicle->registration_number }})</span>
            </li>
            <li class="list-group-item">
                <b>Customer</b> <span class="float-right text-dark">{{ $booking->customer->name }}</span>
            </li>
            <li class="list-group-item">
                <b>From Date</b> <span class="float-right text-dark">{{ $booking->from_date->format('d M Y') }}</span>
            </li>
            <li class="list-group-item">
                <b>To Date</b> <span class="float-right text-dark">{{ $booking->to_date->format('d M Y') }}</span>
            </li>
            <li class="list-group-item">
                <b>Status</b> 
                <span class="float-right">
                    @if($booking->status == 'pending')
                        <span class="badge badge-warning">Pending</span>
                    @elseif($booking->status == 'confirmed')
                        <span class="badge badge-primary">Confirmed</span>
                    @elseif($booking->status == 'on_hold')
                        <span class="badge badge-info">On Hold</span>
                    @elseif($booking->status == 'cancelled')
                        <span class="badge badge-danger">Cancelled</span>
                    @else
                        <span class="badge badge-secondary">{{ ucfirst($booking->status) }}</span>
                    @endif
                </span>
            </li>
        </ul>
    </div>
    <div class="col-md-7">
        <strong><i class="fas fa-car mr-1"></i> Vehicle Details</strong>
        <p class="text-muted">
            {{ $booking->vehicle->name }} - {{ $booking->vehicle->brand }} {{ $booking->vehicle->model }}<br>
            Registration: {{ $booking->vehicle->registration_number }}<br>
            Type: {{ ucfirst($booking->vehicle->type) }}<br>
            Fuel: {{ ucfirst($booking->vehicle->fuel_type) }}
        </p>
        <hr>
        <strong><i class="fas fa-user mr-1"></i> Customer Details</strong>
        <p class="text-muted">
            {{ $booking->customer->name }}<br>
            @if($booking->customer->phone)
                Phone: {{ $booking->customer->phone }}<br>
            @endif
            @if($booking->customer->email)
                Email: {{ $booking->customer->email }}
            @endif
        </p>
        <hr>
        <strong><i class="fas fa-sticky-note mr-1"></i> Notes</strong>
        <p class="text-muted">
            {{ $booking->notes ?: 'No notes provided.' }}
        </p>
        <hr>
        <strong><i class="fas fa-calendar-alt mr-1"></i> Booking Created</strong>
        <p class="text-muted">{{ $booking->created_at->format('d M Y, h:i A') }}</p>
    </div>
</div>
