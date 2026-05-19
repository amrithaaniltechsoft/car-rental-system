<?php

use App\Models\Brand;
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
            'model' => 'Plaid',
            'brand' => $brand->name,
            'type' => $type->name,
            'registration_number' => 'TS-100-AB',
            'fuel_type' => $fuelType->name,
            'seating_capacity' => 5,
            'rc_book_details' => 'RC Details',
            'insurance_details' => 'Insurance Details',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('vehicles.index'));

    $this->assertDatabaseHas('vehicles', [
        'name' => 'Model S Plaid',
        'model' => 'Plaid',
        'brand' => $brand->name,
        'type' => $type->name,
        'registration_number' => 'TS-100-AB',
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
            'model' => 'Plaid',
            'brand' => 'NonExistentBrand',
            'type' => 'NonExistentType',
            'registration_number' => 'TS-100-AB',
            'fuel_type' => $fuelType->name,
            'seating_capacity' => 5,
        ]);

    $response->assertSessionHasErrors(['brand', 'type']);
});
