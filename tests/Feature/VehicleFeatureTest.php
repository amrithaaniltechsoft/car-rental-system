<?php

use App\Models\Booking;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\FuelType;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;

test('vehicle index screen can be rendered with database-driven brand and type list', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('vehicles.index'));

    $response->assertOk();
    $response->assertViewHasAll(['brands', 'types', 'fuelTypes']);
});

test('vehicle create screen can be rendered with database-driven brand and type list', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('vehicles.create'));

    $response->assertOk();
    $response->assertViewHasAll(['brands', 'types', 'fuelTypes']);
});

test('vehicle can be stored with valid brand and type', function () {
    $user = User::factory()->create();

    // Grab seeded records from database
    $brand = Brand::first();
    $type = VehicleType::first();
    $fuelType = FuelType::first();

    $response = $this
        ->actingAs($user)
        ->post(route('vehicles.store'), [
            'name' => 'Model S Plaid',
            'model' => 2024,
            'brand' => $brand->name,
            'type' => $type->name,
            'number_plate' => 'TS-100-AB',
            'number_code' => 'CODE-100-AB',
            'fuel_type' => $fuelType->name,
            'seating_capacity' => 5,
            'rc_book_details' => 'RC Details',
            'insurance_details' => 'Insurance Details',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('vehicles.index'));

    $this->assertDatabaseHas('vehicles', [
        'name' => 'Model S Plaid',
        'model' => 2024,
        'brand' => $brand->name,
        'type' => $type->name,
        'number_plate' => 'TS-100-AB',
        'number_code' => 'CODE-100-AB',
        'fuel_type' => $fuelType->name,
    ]);
});

test('vehicle store fails with invalid brand or type', function () {
    $user = User::factory()->create();
    $fuelType = FuelType::first();

    $response = $this
        ->actingAs($user)
        ->post(route('vehicles.store'), [
            'name' => 'Model S Plaid',
            'model' => 2024,
            'brand' => 'NonExistentBrand',
            'type' => 'NonExistentType',
            'number_plate' => 'TS-100-AB',
            'number_code' => 'CODE-100-AB',
            'fuel_type' => $fuelType->name,
            'seating_capacity' => 5,
        ]);

    $response->assertSessionHasErrors(['brand', 'type']);
});

test('vehicle store via AJAX returns JSON success response', function () {
    $user = User::factory()->create();
    $brand = Brand::first();
    $type = VehicleType::first();
    $fuelType = FuelType::first();

    $response = $this
        ->actingAs($user)
        ->postJson(route('vehicles.store'), [
            'name' => 'Model X Plaid',
            'model' => 2024,
            'brand' => $brand->name,
            'type' => $type->name,
            'number_plate' => 'TS-200-CD',
            'number_code' => 'CODE-200-CD',
            'fuel_type' => $fuelType->name,
            'seating_capacity' => 6,
            'rc_book_details' => 'RC Details',
            'insurance_details' => 'Insurance Details',
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Vehicle created successfully.',
    ]);
});

test('vehicle store fails on duplicate vehicle name via AJAX', function () {
    $user = User::factory()->create();
    $brand = Brand::first();
    $type = VehicleType::first();
    $fuelType = FuelType::first();

    // Create a vehicle first
    Vehicle::create([
        'name' => 'Duplicate Name Vehicle',
        'model' => 2024,
        'brand' => $brand->name,
        'type' => $type->name,
        'number_plate' => 'TS-300-EF',
        'number_code' => 'CODE-300-EF',
        'fuel_type' => $fuelType->name,
        'seating_capacity' => 5,
    ]);

    // Try to create another vehicle with the same name
    $response = $this
        ->actingAs($user)
        ->postJson(route('vehicles.store'), [
            'name' => 'Duplicate Name Vehicle',
            'model' => 2024,
            'brand' => $brand->name,
            'type' => $type->name,
            'number_plate' => 'TS-400-GH',
            'number_code' => 'CODE-400-GH',
            'fuel_type' => $fuelType->name,
            'seating_capacity' => 5,
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);
});

test('vehicle update via AJAX returns JSON success response', function () {
    $user = User::factory()->create();
    $brand = Brand::first();
    $type = VehicleType::first();
    $fuelType = FuelType::first();

    $vehicle = Vehicle::create([
        'name' => 'Original Name',
        'model' => 2024,
        'brand' => $brand->name,
        'type' => $type->name,
        'number_plate' => 'TS-500-IJ',
        'number_code' => 'CODE-500-IJ',
        'fuel_type' => $fuelType->name,
        'seating_capacity' => 5,
    ]);

    $response = $this
        ->actingAs($user)
        ->putJson(route('vehicles.update', $vehicle), [
            'name' => 'Updated Unique Name',
            'model' => 2025,
            'brand' => $brand->name,
            'type' => $type->name,
            'number_plate' => 'TS-500-IJ',
            'number_code' => 'CODE-500-IJ',
            'fuel_type' => $fuelType->name,
            'seating_capacity' => 5,
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Vehicle updated successfully.',
    ]);
});

test('vehicle update fails on duplicate name of another vehicle', function () {
    $user = User::factory()->create();
    $brand = Brand::first();
    $type = VehicleType::first();
    $fuelType = FuelType::first();

    $vehicle1 = Vehicle::create([
        'name' => 'Vehicle One',
        'model' => 2024,
        'brand' => $brand->name,
        'type' => $type->name,
        'number_plate' => 'TS-600-KL',
        'number_code' => 'CODE-600-KL',
        'fuel_type' => $fuelType->name,
        'seating_capacity' => 5,
    ]);

    $vehicle2 = Vehicle::create([
        'name' => 'Vehicle Two',
        'model' => 2024,
        'brand' => $brand->name,
        'type' => $type->name,
        'number_plate' => 'TS-700-MN',
        'number_code' => 'CODE-700-MN',
        'fuel_type' => $fuelType->name,
        'seating_capacity' => 5,
    ]);

    // Try to update Vehicle Two to have Vehicle One's name
    $response = $this
        ->actingAs($user)
        ->putJson(route('vehicles.update', $vehicle2), [
            'name' => 'Vehicle One',
            'model' => 2024,
            'brand' => $brand->name,
            'type' => $type->name,
            'number_plate' => 'TS-700-MN',
            'number_code' => 'CODE-700-MN',
            'fuel_type' => $fuelType->name,
            'seating_capacity' => 5,
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);
});

test('vehicle can be deleted', function () {
    $user = User::factory()->create();
    $brand = Brand::first();
    $type = VehicleType::first();
    $fuelType = FuelType::first();

    $vehicle = Vehicle::create([
        'name' => 'To Be Deleted',
        'model' => 2024,
        'brand' => $brand->name,
        'type' => $type->name,
        'number_plate' => 'TS-999-DEL',
        'number_code' => 'CODE-999-DEL',
        'fuel_type' => $fuelType->name,
        'seating_capacity' => 5,
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('vehicles.destroy', $vehicle));

    $response->assertRedirect(route('vehicles.index'));
    $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
});

test('vehicle cannot be deleted when it has bookings', function () {
    $user = User::factory()->create();
    $brand = Brand::first();
    $type = VehicleType::first();
    $fuelType = FuelType::first();

    $vehicle = Vehicle::create([
        'name' => 'Booked Vehicle',
        'model' => 2024,
        'brand' => $brand->name,
        'type' => $type->name,
        'number_plate' => 'TS-997-USE',
        'number_code' => 'CODE-997-USE',
        'fuel_type' => $fuelType->name,
        'seating_capacity' => 5,
    ]);

    $customer = Customer::create([
        'customer_type' => 'individual',
        'name' => 'Test Customer',
        'phone_number' => '1234567890',
        'id_card_number' => 'ID-TEST',
        'address' => 'Test Address',
    ]);

    Booking::create([
        'vehicle_id' => $vehicle->id,
        'customer_id' => $customer->id,
        'from_date' => now()->toDateString(),
        'to_date' => now()->addDays(3)->toDateString(),
        'total_amount' => 500.00,
        'status' => 'confirmed',
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('vehicles.destroy', $vehicle));

    $response->assertRedirect(route('vehicles.index'));
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id]);
});

test('vehicle cannot be deleted via AJAX when it has bookings', function () {
    $user = User::factory()->create();
    $brand = Brand::first();
    $type = VehicleType::first();
    $fuelType = FuelType::first();

    $vehicle = Vehicle::create([
        'name' => 'Booked Vehicle AJAX',
        'model' => 2024,
        'brand' => $brand->name,
        'type' => $type->name,
        'number_plate' => 'TS-996-USE',
        'number_code' => 'CODE-996-USE',
        'fuel_type' => $fuelType->name,
        'seating_capacity' => 5,
    ]);

    $customer = Customer::create([
        'customer_type' => 'individual',
        'name' => 'Test Customer AJAX',
        'phone_number' => '0987654321',
        'id_card_number' => 'ID-AJAX',
        'address' => 'Test Address',
    ]);

    Booking::create([
        'vehicle_id' => $vehicle->id,
        'customer_id' => $customer->id,
        'from_date' => now()->toDateString(),
        'to_date' => now()->addDays(3)->toDateString(),
        'total_amount' => 500.00,
        'status' => 'pending',
    ]);

    $response = $this
        ->actingAs($user)
        ->deleteJson(route('vehicles.destroy', $vehicle));

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'message' => 'Cannot delete this vehicle because it is in use by existing bookings.',
    ]);
    $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id]);
});

test('vehicle can be deleted via AJAX', function () {
    $user = User::factory()->create();
    $brand = Brand::first();
    $type = VehicleType::first();
    $fuelType = FuelType::first();

    $vehicle = Vehicle::create([
        'name' => 'To Be Deleted AJAX',
        'model' => 2024,
        'brand' => $brand->name,
        'type' => $type->name,
        'number_plate' => 'TS-998-DEL',
        'number_code' => 'CODE-998-DEL',
        'fuel_type' => $fuelType->name,
        'seating_capacity' => 5,
    ]);

    $response = $this
        ->actingAs($user)
        ->deleteJson(route('vehicles.destroy', $vehicle));

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Vehicle deleted successfully.',
    ]);
    $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
});
