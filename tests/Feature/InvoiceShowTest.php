<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Test Case: Invoice Show View
|--------------------------------------------------------------------------
|
| This test verifies that the invoice show page displays correctly
| with all the relevant invoice details.
|
*/

test('invoice show page displays correctly', function () {
    $user = User::factory()->create();

    // Create test data
    $customer = Customer::create([
        'name' => 'Test Customer',
        'customer_type' => 'individual',
        'phone_number' => '1234567890',
        'address' => '123 Test Street',
    ]);

    $vehicle = Vehicle::create([
        'name' => 'Test Vehicle',
        'number_plate' => 'ABC-123',
        'number_code' => 'CODE-123',
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
        'status' => 'confirmed',
        'total_amount' => 100.00,
    ]);

    $invoice = Invoice::create([
        'invoice_number' => 'INV'.now()->format('Ymd').'0001',
        'customer_id' => $customer->id,
        'booking_id' => $booking->id,
        'amount' => 100.00,
        'vat' => 10.00,
        'subtotal' => 100.00,
        'total' => 110.00,
        'invoice_date' => now(),
        'due_date' => now()->addDays(7),
        'status' => 'pending',
    ]);

    // Act as authenticated user and visit invoice show page
    $response = $this->actingAs($user)
        ->get(route('invoices.show', $invoice));

    // Assert response
    $response->assertStatus(200);

    // Assert invoice details are displayed
    $response->assertSee($invoice->invoice_number);
    $response->assertSee($invoice->invoice_date->format('d M Y'));
    $response->assertSee(number_format((float) $invoice->total, 2));

    // Assert customer information is displayed
    $response->assertSee($customer->name);

    // Assert booking details are displayed (if exists)
    $response->assertSee('#'.$booking->id);
    $response->assertSee($vehicle->name);
});

test('invoice show page displays status badge correctly', function () {
    $user = User::factory()->create();

    $customer = Customer::create([
        'name' => 'Test Customer',
        'customer_type' => 'individual',
        'phone_number' => '1234567890',
        'address' => '123 Test Street',
    ]);

    $invoice = Invoice::create([
        'invoice_number' => 'INV'.now()->format('Ymd').'0002',
        'customer_id' => $customer->id,
        'amount' => 100.00,
        'vat' => 10.00,
        'subtotal' => 100.00,
        'total' => 110.00,
        'invoice_date' => now(),
        'due_date' => now()->addDays(7),
        'status' => 'paid',
    ]);

    $response = $this->actingAs($user)
        ->get(route('invoices.show', $invoice));

    $response->assertStatus(200);
    $response->assertSee('Paid');
    $response->assertSee('badge-success');
});

test('invoice show page displays description when available', function () {
    $user = User::factory()->create();

    $customer = Customer::create([
        'name' => 'Test Customer',
        'customer_type' => 'individual',
        'phone_number' => '1234567890',
        'address' => '123 Test Street',
    ]);

    $invoice = Invoice::create([
        'invoice_number' => 'INV'.now()->format('Ymd').'0003',
        'customer_id' => $customer->id,
        'amount' => 100.00,
        'vat' => 10.00,
        'subtotal' => 100.00,
        'total' => 110.00,
        'invoice_date' => now(),
        'due_date' => now()->addDays(7),
        'status' => 'pending',
        'description' => 'This is a test invoice description.',
    ]);

    $response = $this->actingAs($user)
        ->get(route('invoices.show', $invoice));

    $response->assertStatus(200);
    $response->assertSee('This is a test invoice description.');
});

test('invoice show page displays company customer name correctly', function () {
    $user = User::factory()->create();

    $customer = Customer::create([
        'customer_type' => 'company',
        'name' => 'Test Company Ltd.',
        'company_name' => 'Test Company Ltd.',
        'phone_number' => '1234567890',
        'address' => '123 Business Ave',
    ]);

    $invoice = Invoice::create([
        'invoice_number' => 'INV'.now()->format('Ymd').'0004',
        'customer_id' => $customer->id,
        'amount' => 100.00,
        'vat' => 10.00,
        'subtotal' => 100.00,
        'total' => 110.00,
        'invoice_date' => now(),
        'due_date' => now()->addDays(7),
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user)
        ->get(route('invoices.show', $invoice));

    $response->assertStatus(200);
    $response->assertSee('Test Company Ltd.');
});
