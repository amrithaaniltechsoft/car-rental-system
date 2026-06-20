@extends('adminlte::page')

@section('title', 'Add Booking')

@section('content_header')
    <h1>Add New Booking</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Booking Information</h3>
                </div>
                <form action="{{ route('bookings.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="vehicle_id">Vehicle</label>
                                    <select class="form-control @error('vehicle_id') is-invalid @enderror" id="vehicle_id" name="vehicle_id" required>
                                        <option value="">Select Vehicle</option>
                                        @foreach($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                                {{ $vehicle->name }} ({{ $vehicle->number_plate }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vehicle_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="customer_id">Customer</label>
                                    <select class="form-control select2 @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="from_date">From Date</label>
                                    <input type="text" class="form-control datepicker @error('from_date') is-invalid @enderror" id="from_date" name="from_date" value="{{ old('from_date') }}" required>
                                    @error('from_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="to_date">To Date</label>
                                    <input type="text" class="form-control datepicker @error('to_date') is-invalid @enderror" id="to_date" name="to_date" value="{{ old('to_date') }}" required>
                                    @error('to_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="on_hold" {{ old('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="notes">Remark</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="1">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="payment_type">Payment Type</label>
                                    <select class="form-control @error('payment_type') is-invalid @enderror" id="payment_type" name="payment_type">
                                        <option value="">Select Payment Type</option>
                                        <option value="card" {{ old('payment_type') == 'card' ? 'selected' : '' }}>Card</option>
                                        <option value="email_credit" {{ old('payment_type') == 'email_credit' ? 'selected' : '' }}>Email Credit</option>
                                        <option value="lpo" {{ old('payment_type') == 'lpo' ? 'selected' : '' }}>LPO</option>
                                        <option value="cash" {{ old('payment_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    </select>
                                    @error('payment_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Save Booking</button>
                        <a href="{{ route('bookings.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .datepicker[readonly] {
            background-color: #ffffff;
            opacity: 1;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(document).ready(function() {
            var bookingToday = new Date();
            bookingToday.setHours(0, 0, 0, 0);

            var toPicker = flatpickr('#to_date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
                shorthandCurrentMonth: false,
                disable: [function(date) { return date < bookingToday; }],
                onReady: function(selectedDates, dateStr, instance) {
                    var wrapper = instance.calendarContainer.querySelector('.numInputWrapper');
                    if (!wrapper) return;
                    var select = document.createElement('select');
                    select.style.marginLeft = '4px';
                    select.style.fontSize = 'inherit';
                    select.style.border = 'none';
                    select.style.borderRadius = '0';
                    select.style.fontWeight = 'inherit';
                    var curr = new Date().getFullYear();
                    for (var y = curr; y <= curr + 20; y++) {
                        var opt = document.createElement('option');
                        opt.value = y;
                        opt.textContent = y;
                        if (y === instance.currentYear) opt.selected = true;
                        select.appendChild(opt);
                    }
                    select.addEventListener('change', function(e) {
                        instance.changeYear(parseInt(e.target.value));
                    });
                    wrapper.style.display = 'none';
                    wrapper.parentNode.insertBefore(select, wrapper);
                    instance._yearSelect = select;
                },
                onYearChange: function(selectedDates, dateStr, instance) {
                    if (instance._yearSelect) instance._yearSelect.value = instance.currentYear;
                }
            });

            flatpickr('#from_date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
                shorthandCurrentMonth: false,
                disable: [function(date) { return date < bookingToday; }],
                onChange: function(selectedDates) {
                    var minToDate = selectedDates[0] || bookingToday;
                    toPicker.set('disable', [function(date) { return date < minToDate; }]);
                    if (toPicker.selectedDates[0] && toPicker.selectedDates[0] < minToDate) {
                        toPicker.setDate(minToDate);
                    }
                },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var response = xhr.responseJSON;
                            alert(response.message);
                        } else {
                            alert('An error occurred. Please try again.');
                        }
                    }
                });
            });
        });
    </script>
@stop
