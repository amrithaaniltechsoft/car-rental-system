<form action="{{ route('vehicles.update', $vehicle) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="name">Vehicle Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                       id="name" name="name" value="{{ old('name', $vehicle->name) }}" required>
                @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group col-md-3">
                <label for="brand">Brand</label>
                <select class="form-control @error('brand') is-invalid @enderror"
                        id="brand" name="brand" required>
                    <option value="" disabled {{ old('brand', $vehicle->brand) ? '' : 'selected' }}>Select Brand</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->name }}" {{ old('brand', $vehicle->brand) == $brand->name ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
                @error('brand')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group col-md-3">
                <label for="model">Model (Year)</label>
                <select class="form-control select2 @error('model') is-invalid @enderror"
                        id="model" name="model" required style="width: 100%;">
                    <option value="" disabled {{ old('model', $vehicle->model) ? '' : 'selected' }}>Select Year</option>
                    @for($year = 2000; $year <= 2100; $year++)
                        <option value="{{ $year }}" {{ old('model', $vehicle->model) == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endfor
                </select>
                @error('model')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group col-md-3">
                <label for="type">Vehicle Type</label>
                <select class="form-control @error('type') is-invalid @enderror"
                        id="type" name="type" required>
                    <option value="" disabled {{ old('type', $vehicle->type) ? '' : 'selected' }}>Select Type</option>
                    @foreach($types as $type)
                        <option value="{{ $type->name }}" {{ old('type', $vehicle->type) == $type->name ? 'selected' : '' }}>{{ ucfirst($type->name) }}</option>
                    @endforeach
                </select>
                @error('type')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="number_plate">Number Plate</label>
                <input type="text" class="form-control @error('number_plate') is-invalid @enderror"
                       id="number_plate" name="number_plate" value="{{ old('number_plate', $vehicle->number_plate) }}" required>
                @error('number_plate')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group col-md-3">
                <label for="number_code">Plate Code</label>
                <input type="text" class="form-control @error('number_code') is-invalid @enderror"
                       id="number_code" name="number_code" value="{{ old('number_code', $vehicle->number_code) }}" required
                       pattern="[0-9]*" inputmode="numeric"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                @error('number_code')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group col-md-3">
                <label for="fuel_type">Fuel Type</label>
                <select class="form-control @error('fuel_type') is-invalid @enderror"
                        id="fuel_type" name="fuel_type" required>
                    <option value="">Select Fuel Type</option>
                    @foreach($fuelTypes as $fuelType)
                        <option value="{{ $fuelType->name }}" {{ old('fuel_type', $vehicle->fuel_type) == $fuelType->name ? 'selected' : '' }}>{{ ucfirst($fuelType->name) }}</option>
                    @endforeach
                </select>
                @error('fuel_type')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group col-md-3">
                <label for="seating_capacity">Seating Capacity</label>
                <input type="number" class="form-control @error('seating_capacity') is-invalid @enderror"
                       id="seating_capacity" name="seating_capacity" value="{{ old('seating_capacity', $vehicle->seating_capacity) }}" min="1" required>
                @error('seating_capacity')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="rc_book_details">RC Book Details</label>
                <textarea class="form-control @error('rc_book_details') is-invalid @enderror"
                          id="rc_book_details" name="rc_book_details" rows="3">{{ old('rc_book_details', $vehicle->rc_book_details) }}</textarea>
                @error('rc_book_details')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="insurance_details">Insurance Details</label>
                <textarea class="form-control @error('insurance_details') is-invalid @enderror"
                          id="insurance_details" name="insurance_details" rows="3">{{ old('insurance_details', $vehicle->insurance_details) }}</textarea>
                @error('insurance_details')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-success font-weight-bold">Update Vehicle</button>
    </div>
</form>
