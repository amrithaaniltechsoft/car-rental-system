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
                <b><i class="fas fa-phone mr-1"></i> Phone</b>
                <span class="float-right text-dark">{{ $customer->phone_number }}</span>
            </li>
            @if($customer->customer_type === 'individual')
                <li class="list-group-item">
                    <b><i class="fas fa-id-card mr-1"></i> ID Card No.</b>
                    <span class="float-right text-dark">{{ $customer->id_card_number ?: 'N/A' }}</span>
                </li>
            @else
                <li class="list-group-item">
                    <b><i class="fas fa-registered mr-1"></i> Reg. ID</b>
                    <span class="float-right text-dark">{{ $customer->company_registration_id ?: 'N/A' }}</span>
                </li>
            @endif
            
        </ul>
    </div>

    <div class="col-md-7">
        <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
        <p class="text-muted mt-1">
            {{ $customer->address ?: 'No address recorded.' }}
        </p>
        <hr>
        
        <strong><i class="fas fa-clock mr-1"></i> Added At</strong>
        <p class="text-muted mt-1">{{ $customer->created_at->format('d M Y, h:i A') }}</p>
    </div>
</div>
