<div class="row">
    <div class="col-md-5">
        <div class="text-center">
            <i class="fas fa-calendar-check fa-5x text-muted"></i>
        </div>
        <h3 class="profile-username text-center">Booking</h3>
        <p class="text-muted text-center">{{ $booking->from_date->format('d M Y') }} - {{ $booking->to_date->format('d M Y') }}</p>

        <ul class="list-group list-group-unbordered mb-3">
            <li class="list-group-item">
                <b><i class="fas fa-hashtag mr-1"></i> Booking ID</b> <span class="float-right text-dark">{{ $booking->booking_id ?: 'N/A' }}</span>
            </li>
            <li class="list-group-item">
                <b><i class="fas fa-car mr-1"></i> Vehicle</b> <span class="float-right text-dark">{{ $booking->vehicle->name }} ({{ $booking->vehicle->registration_number }})</span>
            </li>
            <li class="list-group-item">
                <b><i class="fas fa-user mr-1"></i> Customer</b> <span class="float-right text-dark">{{ $booking->customer->name }}</span>
            </li>
            <li class="list-group-item">
                <b><i class="fas fa-info-circle mr-1"></i> Status</b>
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
            <li class="list-group-item">
                <b><i class="fas fa-comment mr-1"></i> Remark</b> <span class="float-right text-muted">{{ $booking->notes ?: 'No remarks' }}</span>
            </li>
            <li class="list-group-item">
                <b><i class="fas fa-credit-card mr-1"></i> Payment Type</b> <span class="float-right text-muted">
                    @if($booking->payment_type)
                        {{ ucwords(str_replace('_', ' ', $booking->payment_type)) }}
                    @else
                        Not specified
                    @endif
                </span>
            </li>
            <li class="list-group-item">
                <b><i class="fas fa-clock mr-1"></i> Booking Created</b> <span class="float-right text-muted">{{ $booking->created_at->format('d M Y') }}</span>
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
            {{ $booking->customer->name }}
            @if($booking->customer->customer_type)
                ({{ ucfirst(str_replace('_', ' ', $booking->customer->customer_type)) }})
            @endif
            <br>
            @if($booking->customer->phone_number)
                Phone: {{ $booking->customer->phone_number }}<br>
            @endif
            @if($booking->customer->address)
                Address: {{ $booking->customer->address }}<br>
            @endif
            @if($booking->customer->id_card_number)
                ID Card: {{ $booking->customer->id_card_number }}<br>
            @endif
            @if($booking->customer->company_name)
                Company: {{ $booking->customer->company_name }}<br>
            @endif
            @if($booking->customer->company_registration_id)
                Reg No: {{ $booking->customer->company_registration_id }}
            @endif
        </p>
    </div>
</div>
