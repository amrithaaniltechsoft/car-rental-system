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

                <div class="col-md-3">
                    <label>First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control"
                           value="{{ old('first_name', $customer->first_name) }}">
                </div>

                <div class="col-md-3">
                    <label>Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control"
                           value="{{ old('last_name', $customer->last_name) }}">
                </div>

                <div class="col-md-3">
                    <label>Date of Birth</label>
                    <input type="text" name="date_of_birth" class="form-control datepicker"
                           id="edit_date_of_birth"
                           value="{{ old('date_of_birth', $customer->date_of_birth ? $customer->date_of_birth->format('Y-m-d') : '') }}"
                           placeholder="YYYY-MM-DD" readonly>
                </div>

                <div class="col-md-3">
                    <label>Nationality</label>
                    <input type="text" name="nationality" class="form-control"
                           value="{{ old('nationality', $customer->nationality) }}">
                </div>

            </div>

            <div class="row mt-2">

                <div class="col-md-4">
                    <label>Mobile Number <span class="text-danger">*</span></label>
                    <input type="text" name="phone_number" class="form-control"
                           value="{{ old('phone_number', $customer->phone_number) }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>

                <div class="col-md-4">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email', $customer->email) }}">
                </div>

                <div class="col-md-4">
                    <label>Passport Number</label>
                    <input type="text" name="passport_number" class="form-control"
                           value="{{ old('passport_number', $customer->passport_number) }}">
                </div>

            </div>

            <div class="row mt-2">

                <div class="col-md-4">
                    <label>Driving License Number <span class="text-danger">*</span></label>
                    <input type="text" name="driving_license_number" class="form-control"
                           value="{{ old('driving_license_number', $customer->driving_license_number) }}">
                </div>

                <div class="col-md-4">
                    <label>License Expiry Date <span class="text-danger">*</span></label>
                    <input type="text" name="license_expiry_date" class="form-control datepicker"
                           id="edit_license_expiry_date"
                           value="{{ old('license_expiry_date', $customer->license_expiry_date ? $customer->license_expiry_date->format('Y-m-d') : '') }}"
                           placeholder="YYYY-MM-DD" readonly>
                </div>

                <div class="col-md-4">
                    <label>License Issue Country <span class="text-danger">*</span></label>
                    <input type="text" name="license_issue_country" class="form-control"
                           value="{{ old('license_issue_country', $customer->license_issue_country) }}">
                </div>

            </div>

            <div class="form-group mt-2">
                <label>Residential Address</label>
                <textarea name="residential_address" class="form-control" rows="3">{{ old('residential_address', $customer->residential_address) }}</textarea>
            </div>

        </div>

        @endif

        <!-- ================= COMPANY ================= -->
        @if($customer->customer_type == 'company')

        <div id="company-fields">

            <div class="row">

                <div class="col-md-4">
                    <label>Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="company_name" class="form-control"
                           value="{{ old('company_name', $customer->company_name) }}">
                </div>

                <div class="col-md-4">
                    <label>Phone Number <span class="text-danger">*</span></label>
                    <input type="text" name="phone_number" class="form-control"
                           value="{{ old('phone_number', $customer->phone_number) }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>

                <div class="col-md-4">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email', $customer->email) }}">
                </div>

            </div>

            <div class="form-group mt-2">
                <label>Address <span class="text-danger">*</span></label>
                <textarea name="address" class="form-control" rows="3">{{ old('address', $customer->address) }}</textarea>
            </div>

        </div>

        @endif

    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-success">Update Customer</button>
    </div>

</form>

<script>
    (function () {
        flatpickr('#edit_date_of_birth', {
            dateFormat: 'Y-m-d',
            allowInput: false,
            maxDate: new Date()
        });

        flatpickr('#edit_license_expiry_date', {
            dateFormat: 'Y-m-d',
            allowInput: false,
            minDate: new Date()
        });
    })();
</script>
