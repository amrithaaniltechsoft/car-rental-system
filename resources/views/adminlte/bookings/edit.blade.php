@extends('adminlte::page')

@section('title', 'Edit Booking')

@section('content_header')
    <h1>Edit Booking</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Booking Information</h3>
                </div>
                <form action="{{ route('bookings.update', $booking) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="vehicle_id">Vehicle</label>
                                    <select class="form-control @error('vehicle_id') is-invalid @enderror" id="vehicle_id" name="vehicle_id" required>
                                        @foreach($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $booking->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                                                {{ $vehicle->name }} ({{ $vehicle->registration_number }})
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
                                    <select class="form-control @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id', $booking->customer_id) == $customer->id ? 'selected' : '' }}>
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
                                    <input type="text" class="form-control datepicker @error('from_date') is-invalid @enderror" id="from_date" name="from_date" value="{{ old('from_date', $booking->from_date->format('Y-m-d')) }}" required>
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
                                    <input type="text" class="form-control datepicker @error('to_date') is-invalid @enderror" id="to_date" name="to_date" value="{{ old('to_date', $booking->to_date->format('Y-m-d')) }}" required>
                                    @error('to_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="pending" {{ old('status', $booking->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="confirmed" {{ old('status', $booking->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="on_hold" {{ old('status', $booking->status) == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                        <option value="cancelled" {{ old('status', $booking->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="1">{{ old('notes', $booking->notes) }}</textarea>
                                    @error('notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update Booking</button>
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
                minDate: bookingToday,
                defaultDate: $('#to_date').val() || null
            });

            flatpickr('#from_date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
                minDate: bookingToday,
                defaultDate: $('#from_date').val() || null,
                onChange: function(selectedDates) {
                    var minToDate = selectedDates[0] || bookingToday;
                    toPicker.set('minDate', minToDate);
                    if (toPicker.selectedDates[0] && toPicker.selectedDates[0] < minToDate) {
                        toPicker.setDate(minToDate);
                    }
                }
            });

            $('form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var method = form.attr('method');
                var formData = new FormData(form[0]);

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            window.location.href = "{{ route('bookings.index') }}";
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
