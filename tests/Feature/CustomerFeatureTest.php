<?php

use App\Models\Booking;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\FuelType;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;

test('customer index screen can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('customers.index'));

    $response->assertOk();
});

test('individual customer can be stored with valid data', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('customers.store'), [
            'customer_type' => 'individual',
            'name' => 'John Doe',
            'phone_number' => '1234567890',
            'id_card_number' => 'ID-12345',
            'address' => '123 Test Street',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('customers.index'));

    $this->assertDatabaseHas('customers', [
        'customer_type' => 'individual',
        'name' => 'John Doe',
        'phone_number' => '1234567890',
        'id_card_number' => 'ID-12345',
        'address' => '123 Test Street',
        'company_name' => null,
        'company_registration_id' => null,
    ]);
});

test('company customer can be stored with valid data', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('customers.store'), [
            'customer_type' => 'company',
            'company_name' => 'Acme Corp',
            'phone_number' => '0987654321',
            'company_registration_id' => 'REG-999',
            'address' => '456 Business Blvd',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('customers.index'));

    $this->assertDatabaseHas('customers', [
        'customer_type' => 'company',
        'name' => 'Acme Corp',
        'company_name' => 'Acme Corp',
        'phone_number' => '0987654321',
        'company_registration_id' => 'REG-999',
        'address' => '456 Business Blvd',
        'id_card_number' => null,
    ]);
});

test('customer update changes values successfully', function () {
    $user = User::factory()->create();
    $customer = Customer::create([
        'customer_type' => 'individual',
        'name' => 'Jane Smith',
        'phone_number' => '5555555',
        'id_card_number' => 'ID-SMITH',
        'address' => 'Suburban Lane',
    ]);

    $response = $this
        ->actingAs($user)
        ->put(route('customers.update', $customer), [
            'customer_type' => 'company',
            'company_name' => 'Smith Enterprises',
            'phone_number' => '7777777',
            'company_registration_id' => 'REG-SMITH',
            'address' => 'Enterprise Plaza',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('customers.index'));

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'customer_type' => 'company',
        'name' => 'Smith Enterprises',
        'company_name' => 'Smith Enterprises',
        'phone_number' => '7777777',
        'company_registration_id' => 'REG-SMITH',
        'address' => 'Enterprise Plaza',
        'id_card_number' => null,
    ]);
});

test('customer can be deleted when they have no bookings', function () {
    $user = User::factory()->create();
    $customer = Customer::create([
        'customer_type' => 'individual',
        'name' => 'To Be Deleted',
        'phone_number' => '1111111',
        'id_card_number' => 'ID-DEL',
        'address' => 'Delete Street',
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('customers.destroy', $customer));

    $response->assertRedirect(route('customers.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
});

test('customer cannot be deleted when they have bookings', function () {
    $user = User::factory()->create();
    $brand = Brand::first();
    $type = VehicleType::first();
    $fuelType = FuelType::first();

    $customer = Customer::create([
        'customer_type' => 'individual',
        'name' => 'Booked Customer',
        'phone_number' => '2222222',
        'id_card_number' => 'ID-BOOKED',
        'address' => 'Booking Street',
    ]);

    $vehicle = Vehicle::create([
        'name' => 'Customer Delete Test Vehicle',
        'model' => 2024,
        'brand' => $brand->name,
        'type' => $type->name,
        'registration_number' => 'TS-CUST-DEL',
        'fuel_type' => $fuelType->name,
        'seating_capacity' => 5,
    ]);

    Booking::create([
        'vehicle_id' => $vehicle->id,
        'customer_id' => $customer->id,
        'from_date' => now()->toDateString(),
        'to_date' => now()->addDays(2)->toDateString(),
        'total_amount' => 300.00,
        'status' => 'pending',
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('customers.destroy', $customer));

    $response->assertRedirect(route('customers.index'));
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('customers', ['id' => $customer->id]);
});

test('customer cannot be deleted via AJAX when they have bookings', function () {
    $user = User::factory()->create();
    $brand = Brand::first();
    $type = VehicleType::first();
    $fuelType = FuelType::first();

    $customer = Customer::create([
        'customer_type' => 'individual',
        'name' => 'Booked Customer AJAX',
        'phone_number' => '3333333',
        'id_card_number' => 'ID-AJAX',
        'address' => 'Ajax Street',
    ]);

    $vehicle = Vehicle::create([
        'name' => 'Customer Delete AJAX Vehicle',
        'model' => 2024,
        'brand' => $brand->name,
        'type' => $type->name,
        'registration_number' => 'TS-CUST-AJX',
        'fuel_type' => $fuelType->name,
        'seating_capacity' => 5,
    ]);

    Booking::create([
        'vehicle_id' => $vehicle->id,
        'customer_id' => $customer->id,
        'from_date' => now()->toDateString(),
        'to_date' => now()->addDays(2)->toDateString(),
        'total_amount' => 300.00,
        'status' => 'confirmed',
    ]);

    $response = $this
        ->actingAs($user)
        ->deleteJson(route('customers.destroy', $customer));

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'message' => 'Cannot delete this customer because they are in use by existing bookings.',
    ]);
    $this->assertDatabaseHas('customers', ['id' => $customer->id]);
});
