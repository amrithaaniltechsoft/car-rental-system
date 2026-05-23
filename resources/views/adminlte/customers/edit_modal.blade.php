<form action="{{ route('customers.update', $customer->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card-body">

        <!-- CUSTOMER TYPE (READONLY DISPLAY) -->
        <div class="row mb-4">
            <div class="col-md-12 text-center">

                <label class="d-block mb-2">Customer Type</label>

                <input type="radio" disabled {{ $customer->customer_type == 'individual' ? 'checked' : '' }}> Individual
                <input type="radio" disabled {{ $customer->customer_type == 'company' ? 'checked' : '' }}> Company

                <input type="hidden" name="customer_type" value="{{ $customer->customer_type }}">

            </div>
        </div>

        <!-- ================= INDIVIDUAL ================= -->
        @if($customer->customer_type == 'individual')

        <div id="individual-fields">

            <div class="row">

                <div class="col-md-4">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name', $customer->name) }}">
                </div>

                <div class="col-md-4">
                    <label>Phone Number</label>
                    <input type="text" name="phone_number" class="form-control"
                           value="{{ old('phone_number', $customer->phone_number) }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>

                <div class="col-md-4">
                    <label>ID Card Number</label>
                    <input type="text" name="id_card_number" class="form-control"
                           value="{{ old('id_card_number', $customer->id_card_number) }}">
                </div>

            </div>

            <div class="form-group mt-2">
                <label>Address</label>
                <textarea name="address" class="form-control">{{ old('address', $customer->address) }}</textarea>
            </div>

        </div>

        @endif

        <!-- ================= COMPANY ================= -->
        @if($customer->customer_type == 'company')

        <div id="company-fields">

            <div class="row">

                <div class="col-md-4">
                    <label>Company Name</label>
                    <input type="text" name="company_name" class="form-control"
                           value="{{ old('company_name', $customer->company_name) }}">
                </div>

                <div class="col-md-4">
                    <label>Phone Number</label>
                    <input type="text" name="phone_number" class="form-control"
                           value="{{ old('phone_number', $customer->phone_number) }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>

                <div class="col-md-4">
                    <label>Registration ID</label>
                    <input type="text" name="company_registration_id" class="form-control"
                           value="{{ old('company_registration_id', $customer->company_registration_id) }}">
                </div>

            </div>

            <div class="form-group mt-2">
                <label>Address</label>
                <textarea name="address" class="form-control">{{ old('address', $customer->address) }}</textarea>
            </div>

        </div>

        @endif

    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-success">Update Customer</button>
    </div>

</form>