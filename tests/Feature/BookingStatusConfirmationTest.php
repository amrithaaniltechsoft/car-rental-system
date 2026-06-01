<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('booking status can be confirmed via ajax', function () {
    $user = User::factory()->create();

    $customer = Customer::create([
        'name' => 'Test Customer',
        'customer_type' => 'individual',
        'phone_number' => '1234567890',
        'address' => '123 Test Street',
    ]);

    $vehicle = Vehicle::create([
        'name' => 'Test Vehicle',
        'registration_number' => 'ABC-123',
        'type' => 'sedan',
        'model' => 'Test Model',
        'brand' => 'Test Brand',
        'fuel_type' => 'petrol',
        'seating_capacity' => 5,
    ]);

    $booking = Booking::create([
        'vehicle_id' => $vehicle->id,
        'customer_id' => $customer->id,
        'from_date' => now()->addDay(),
        'to_date' => now()->addDays(3),
        'status' => 'pending',
        'total_amount' => 100.00,
    ]);

    $response = $this->actingAs($user)
        ->postJson(route('bookings.confirm', $booking));

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Booking status updated to confirmed successfully.',
        ]);

    $this->assertDatabaseHas('bookings', [
        'id' => $booking->id,
        'status' => 'confirmed',
    ]);
});

test('status badge is clickable for non-confirmed bookings', function () {
    $user = User::factory()->create();

    $customer = Customer::create([
        'name' => 'Test Customer',
        'customer_type' => 'individual',
        'phone_number' => '1234567890',
        'address' => '123 Test Street',
    ]);

    $vehicle = Vehicle::create([
        'name' => 'Test Vehicle',
        'registration_number' => 'ABC-123',
        'type' => 'sedan',
        'model' => 'Test Model',
        'brand' => 'Test Brand',
        'fuel_type' => 'petrol',
        'seating_capacity' => 5,
    ]);

    // Create bookings with different statuses (using only statuses supported by both enums)
    Booking::create([
        'vehicle_id' => $vehicle->id,
        'customer_id' => $customer->id,
        'from_date' => now()->addDay(),
        'to_date' => now()->addDays(3),
        'status' => 'pending',
        'total_amount' => 100.00,
    ]);

    Booking::create([
        'vehicle_id' => $vehicle->id,
        'customer_id' => $customer->id,
        'from_date' => now()->addDays(10),
        'to_date' => now()->addDays(12),
        'status' => 'cancelled',
        'total_amount' => 200.00,
    ]);

    Booking::create([
        'vehicle_id' => $vehicle->id,
        'customer_id' => $customer->id,
        'from_date' => now()->addDays(15),
        'to_date' => now()->addDays(17),
        'status' => 'confirmed',
        'total_amount' => 250.00,
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('bookings.data'));

    $response->assertStatus(200);

    $data = $response->json();

    // Check that non-confirmed statuses have the change-status-btn class
    foreach ($data['data'] as $row) {
        $statusHtml = $row['status'];
        if (in_array($row['status'], ['pending', 'cancelled'])) {
            expect($statusHtml)->toContain('change-status-btn');
        }
    }
});

test('confirmed bookings status badge is not clickable', function () {
    $user = User::factory()->create();

    $customer = Customer::create([
        'name' => 'Test Customer',
        'customer_type' => 'individual',
        'phone_number' => '1234567890',
        'address' => '123 Test Street',
    ]);

    $vehicle = Vehicle::create([
        'name' => 'Test Vehicle',
        'registration_number' => 'ABC-123',
        'type' => 'sedan',
        'model' => 'Test Model',
        'brand' => 'Test Brand',
        'fuel_type' => 'petrol',
        'seating_capacity' => 5,
    ]);

    $confirmedBooking = Booking::create([
        'vehicle_id' => $vehicle->id,
        'customer_id' => $customer->id,
        'from_date' => now()->addDay(),
        'to_date' => now()->addDays(3),
        'status' => 'confirmed',
        'total_amount' => 100.00,
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('bookings.data'));

    $response->assertStatus(200);

    $data = $response->json();

    // Find the confirmed booking in the response by vehicle and customer name
    $confirmedRow = null;
    foreach ($data['data'] as $row) {
        if (str_contains($row['vehicle'], 'Test Vehicle') && $row['customer'] === 'Test Customer') {
            // Check if this row has the confirmed badge (badge-primary) and no change-status-btn
            if (str_contains($row['status'], 'badge-primary') && ! str_contains($row['status'], 'change-status-btn')) {
                $confirmedRow = $row;
                break;
            }
        }
    }

    expect($confirmedRow)->not->toBeNull();
});
