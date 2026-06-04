@extends('adminlte::page')

@section('title', 'Add Customer')

@section('content_header')
    <h1>Add New Customer</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Success!</h5>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Error!</h5>
            {{ session('error') }}
        </div>
    @endif
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Customer Information</h3>
                </div>
                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group text-center">
                                    <label class="d-block">Customer Type</label>
                                    <div class="form-check form-check-inline mr-4">
                                        <input class="form-check-input @error('customer_type') is-invalid @enderror"
                                               type="radio"
                                               name="customer_type"
                                               id="customer_type_individual"
                                               value="individual"
                                               {{ old('customer_type', 'individual') == 'individual' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label font-weight-normal text-secondary" style="font-size: 16px;" for="customer_type_individual">Individual</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input @error('customer_type') is-invalid @enderror"
                                               type="radio"
                                               name="customer_type"
                                               id="customer_type_company"
                                               value="company"
                                               {{ old('customer_type') == 'company' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label font-weight-normal text-secondary" style="font-size: 16px;" for="customer_type_company">Company</label>
                                    </div>
                                    @error('customer_type')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Individual Fields -->
                        <div id="individual-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="first_name">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                               id="first_name" name="first_name" value="{{ old('first_name') }}">
                                        @error('first_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                               id="last_name" name="last_name" value="{{ old('last_name') }}">
                                        @error('last_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date_of_birth">Date of Birth</label>
                                        <input type="text" class="form-control datepicker @error('date_of_birth') is-invalid @enderror"
                                               id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" placeholder="YYYY-MM-DD">
                                        @error('date_of_birth')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="nationality">Nationality</label>
                                        <input type="text" class="form-control @error('nationality') is-invalid @enderror"
                                               id="nationality" name="nationality" value="{{ old('nationality') }}">
                                        @error('nationality')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="indiv_phone_number">Mobile Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                                               id="indiv_phone_number" name="phone_number" value="{{ old('phone_number') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        @error('phone_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="email" name="email" value="{{ old('email') }}">
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="passport_number">Passport Number</label>
                                        <input type="text" class="form-control @error('passport_number') is-invalid @enderror"
                                               id="passport_number" name="passport_number" value="{{ old('passport_number') }}">
                                        @error('passport_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="driving_license_number">Driving License Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('driving_license_number') is-invalid @enderror"
                                               id="driving_license_number" name="driving_license_number" value="{{ old('driving_license_number') }}">
                                        @error('driving_license_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="license_expiry_date">License Expiry Date <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control datepicker @error('license_expiry_date') is-invalid @enderror"
                                               id="license_expiry_date" name="license_expiry_date" value="{{ old('license_expiry_date') }}" placeholder="YYYY-MM-DD">
                                        @error('license_expiry_date')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="license_issue_country">License Issue Country <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('license_issue_country') is-invalid @enderror"
                                               id="license_issue_country" name="license_issue_country" value="{{ old('license_issue_country') }}">
                                        @error('license_issue_country')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="residential_address">Residential Address</label>
                                <textarea class="form-control @error('residential_address') is-invalid @enderror"
                                          id="residential_address" name="residential_address" rows="3">{{ old('residential_address') }}</textarea>
                                @error('residential_address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Company Fields -->
                        <div id="company-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="company_name">Company Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                               id="company_name" name="company_name" value="{{ old('company_name') }}">
                                        @error('company_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="comp_phone_number">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                                               id="comp_phone_number" name="phone_number" value="{{ old('phone_number') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        @error('phone_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email_company">Email Address</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="email_company" name="email" value="{{ old('email') }}">
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="comp_address">Address <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('address') is-invalid @enderror"
                                          id="comp_address" name="address" rows="3">{{ old('address') }}</textarea>
                                @error('address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Save Customer</button>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .content label:not(.form-check-label):not(.custom-file-label) {
            font-size: 17px;
            font-weight: 600 !important;
            color: #6c757d;
        }
        .datepicker[readonly] {
            background-color: #ffffff;
            opacity: 1;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        $(document).ready(function() {
            function toggleFields() {
                var type = $('input[name="customer_type"]:checked').val();
                if (type === 'company') {
                    $('#individual-fields').hide().find('input, textarea').prop('disabled', true).prop('required', false);
                    $('#company-fields').show().find('input, textarea').prop('disabled', false).prop('required', true);
                } else {
                    $('#company-fields').hide().find('input, textarea').prop('disabled', true).prop('required', false);
                    $('#individual-fields').show().find('input, textarea').prop('disabled', false).prop('required', true);
                }
            }

            $('input[name="customer_type"]').change(function() {
                toggleFields();
            });

            toggleFields();

            flatpickr('#date_of_birth', {
                dateFormat: 'Y-m-d',
                allowInput: false,
                maxDate: new Date()
            });

            flatpickr('#license_expiry_date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
                minDate: new Date()
            });
        });
    </script>
@stop
