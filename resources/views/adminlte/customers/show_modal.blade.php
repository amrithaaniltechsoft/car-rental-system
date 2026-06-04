<div class="row">
    <div class="col-md-5 text-center border-right">
        <div class="mb-3">
            @if($customer->customer_type === 'company')
                <i class="fas fa-building fa-5x text-muted"></i>
            @else
                <i class="fas fa-user fa-5x text-muted"></i>
            @endif
        </div>
        <h4 class="font-weight-bold">
            {{ $customer->customer_type === 'company' ? $customer->company_name : $customer->name }}
        </h4>
        <p class="text-muted">
            <span class="badge badge-{{ $customer->customer_type === 'company' ? 'primary' : 'success' }} px-2 py-1">
                {{ ucfirst($customer->customer_type) }}
            </span>
        </p>

        <ul class="list-group list-group-unbordered mb-3 text-left">
            <li class="list-group-item">
                <b><i class="fas fa-id-badge mr-1"></i> Customer ID</b>
                <span class="float-right text-dark">{{ $customer->customer_id ?: 'N/A' }}</span>
            </li>
            <li class="list-group-item">
                <b><i class="fas fa-phone mr-1"></i> Phone</b>
                <span class="float-right text-dark">{{ $customer->phone_number }}</span>
            </li>
            @if($customer->email)
            <li class="list-group-item">
                <b><i class="fas fa-envelope mr-1"></i> Email</b>
                <span class="float-right text-dark">{{ $customer->email }}</span>
            </li>
            @endif
            @if($customer->customer_type === 'individual')
                @if($customer->date_of_birth)
                <li class="list-group-item">
                    <b><i class="fas fa-birthday-cake mr-1"></i> Date of Birth</b>
                    <span class="float-right text-dark">{{ $customer->date_of_birth->format('d M Y') }}</span>
                </li>
                @endif
                @if($customer->nationality)
                <li class="list-group-item">
                    <b><i class="fas fa-flag mr-1"></i> Nationality</b>
                    <span class="float-right text-dark">{{ $customer->nationality }}</span>
                </li>
                @endif
                @if($customer->passport_number)
                <li class="list-group-item">
                    <b><i class="fas fa-passport mr-1"></i> Passport No.</b>
                    <span class="float-right text-dark">{{ $customer->passport_number }}</span>
                </li>
                @endif
                @if($customer->driving_license_number)
                <li class="list-group-item">
                    <b><i class="fas fa-car-side mr-1"></i> Driving License No.</b>
                    <span class="float-right text-dark">{{ $customer->driving_license_number }}</span>
                </li>
                @endif
            @endif
        </ul>
    </div>

    <div class="col-md-7">
        @if($customer->customer_type === 'individual')
            @if($customer->residential_address)
                <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                <p class="text-muted mt-1">{{ $customer->residential_address }}</p>
                <hr>
            @endif
            @if($customer->license_expiry_date)
                <strong><i class="fas fa-calendar-times mr-1"></i> License Expiry Date</strong>
                <p class="text-muted mt-1">{{ $customer->license_expiry_date->format('d M Y') }}</p>
                <hr>
            @endif
            @if($customer->license_issue_country)
                <strong><i class="fas fa-globe mr-1"></i> License Issue Country</strong>
                <p class="text-muted mt-1">{{ $customer->license_issue_country }}</p>
                <hr>
            @endif
        @else
            @if($customer->address)
                <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                <p class="text-muted mt-1">{{ $customer->address }}</p>
                <hr>
            @endif
        @endif

        <strong><i class="fas fa-clock mr-1"></i> Added At</strong>
        <p class="text-muted mt-1">{{ $customer->created_at->format('d M Y') }}</p>
    </div>
</div>
