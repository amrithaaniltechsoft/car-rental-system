@extends('adminlte::page')

@section('title', 'Edit Customer')

@section('content_header')
    <h1>Edit Customer</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Customer Information</h3>
                </div>
                <form action="{{ route('customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')
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
                                               {{ old('customer_type', $customer->customer_type) == 'individual' ? 'checked' : '' }} 
                                               required>
                                        <label class="form-check-label font-weight-normal text-secondary" style="font-size: 16px;" for="customer_type_individual">Individual</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input @error('customer_type') is-invalid @enderror" 
                                               type="radio" 
                                               name="customer_type" 
                                               id="customer_type_company" 
                                               value="company" 
                                               {{ old('customer_type', $customer->customer_type) == 'company' ? 'checked' : '' }} 
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
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">Full Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name" value="{{ old('name', $customer->customer_type === 'individual' ? $customer->name : '') }}">
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="indiv_phone_number">Phone Number</label>
                                        <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                                               id="indiv_phone_number" name="phone_number" value="{{ old('phone_number', $customer->customer_type === 'individual' ? $customer->phone_number : '') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        @error('phone_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="id_card_number">ID Card Number</label>
                                        <input type="text" class="form-control @error('id_card_number') is-invalid @enderror"
                                               id="id_card_number" name="id_card_number" value="{{ old('id_card_number', $customer->id_card_number) }}">
                                        @error('id_card_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="indiv_address">Address</label>
                                <textarea class="form-control @error('address') is-invalid @enderror"
                                          id="indiv_address" name="address" rows="3">{{ old('address', $customer->customer_type === 'individual' ? $customer->address : '') }}</textarea>
                                @error('address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Company Fields -->
                        <div id="company-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="company_name">Company Name</label>
                                        <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                               id="company_name" name="company_name" value="{{ old('company_name', $customer->company_name) }}">
                                        @error('company_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="comp_phone_number">Phone Number</label>
                                        <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                                               id="comp_phone_number" name="phone_number" value="{{ old('phone_number', $customer->customer_type === 'company' ? $customer->phone_number : '') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        @error('phone_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="company_registration_id">Company Registration ID</label>
                                        <input type="text" class="form-control @error('company_registration_id') is-invalid @enderror"
                                               id="company_registration_id" name="company_registration_id" value="{{ old('company_registration_id', $customer->company_registration_id) }}">
                                        @error('company_registration_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="comp_address">Address</label>
                                <textarea class="form-control @error('address') is-invalid @enderror"
                                          id="comp_address" name="address" rows="3">{{ old('address', $customer->customer_type === 'company' ? $customer->address : '') }}</textarea>
                                @error('address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update Customer</button>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .content label:not(.form-check-label):not(.custom-file-label) {
            font-size: 17px;
            font-weight: 600 !important;
            color: #6c757d;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            function toggleFields() {
                var type = $('input[name="customer_type"]:checked').val();
                if (type === 'company') {
                    $('#individual-fields').hide().find('input, textarea').prop('disabled', true).prop('required', false);
                    $('#company-fields').show().find('input, textarea').prop('disabled', false).prop('required', true);
                } else if (type === 'individual') {
                    $('#company-fields').hide().find('input, textarea').prop('disabled', true).prop('required', false);
                    $('#individual-fields').show().find('input, textarea').prop('disabled', false).prop('required', true);
                }
            }

            $('input[name="customer_type"]').change(function() {
                toggleFields();
            });

            toggleFields();
        });
    </script>
@stop
