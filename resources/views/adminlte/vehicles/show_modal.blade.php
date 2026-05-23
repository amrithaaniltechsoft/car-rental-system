<div class="row">
    <div class="col-md-5">
        <div class="text-center">
            <i class="fas fa-car fa-5x text-muted"></i>
        </div>
        <h3 class="profile-username text-center">{{ $vehicle->name }}</h3>
        <p class="text-muted text-center">{{ $vehicle->brand }} {{ $vehicle->model }}</p>

        <ul class="list-group list-group-unbordered mb-3">
            <li class="list-group-item">
                <b>Registration</b> <span class="float-right text-dark">{{ $vehicle->registration_number }}</span>
            </li>
            <li class="list-group-item">
                <b>Vehicle Type</b> <span class="float-right text-dark">{{ ucfirst($vehicle->type) ?: 'N/A' }}</span>
            </li>
            <li class="list-group-item">
                <b>Fuel Type</b> <span class="float-right text-dark">{{ ucfirst($vehicle->fuel_type) }}</span>
            </li>
            <li class="list-group-item">
                <b>Seating Capacity</b> <span class="float-right text-dark">{{ $vehicle->seating_capacity }}</span>
            </li>
        </ul>
    </div>
    <div class="col-md-7">
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
        <p class="text-muted">{{ $vehicle->created_at->format('d M Y, h:i A') }}</p>
    </div>
</div>
