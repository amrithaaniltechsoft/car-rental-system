<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;

test('invoice index screen can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('invoices.index'));

    $response->assertOk();
    $response->assertViewHas(['customers', 'vehicles']);
});

test('invoice can be stored with valid data', function () {
    $user = User::factory()->create();
    $customer = Customer::create([
        'customer_type' => 'individual',
        'name' => 'Invoice Customer',
        'phone_number' => '1234567890',
        'id_card_number' => 'ID-INV-001',
        'address' => 'Invoice Street',
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('invoices.store'), [
            'customer_id' => $customer->id,
            'amount' => 150.50,
            'rate' => 10.00,
            'invoice_date' => now()->toDateString(),
            'status' => 'pending',
            'description' => 'Rental invoice',
        ]);

    $response->assertRedirect(route('invoices.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('invoices', [
        'customer_id' => $customer->id,
        'amount' => 150.50,
        'status' => 'pending',
    ]);

    expect(Invoice::first()->invoice_number)->toStartWith('INV-');
});

test('invoice store via AJAX returns JSON success response', function () {
    $user = User::factory()->create();
    $customer = Customer::create([
        'customer_type' => 'individual',
        'name' => 'AJAX Invoice Customer',
        'phone_number' => '9876543210',
        'id_card_number' => 'ID-INV-002',
        'address' => 'Ajax Street',
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('invoices.store'), [
            'customer_id' => $customer->id,
            'amount' => 200.00,
            'invoice_date' => now()->toDateString(),
            'status' => 'paid',
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Invoice created successfully.',
    ]);
});

test('invoices data endpoint returns datatables json', function () {
    $user = User::factory()->create();
    $customer = Customer::create([
        'customer_type' => 'individual',
        'name' => 'List Customer',
        'phone_number' => '5555555',
        'id_card_number' => 'ID-INV-003',
        'address' => 'List Street',
    ]);

    Invoice::create([
        'invoice_number' => 'INV-TEST-0001',
        'customer_id' => $customer->id,
        'amount' => 99.99,
        'invoice_date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $response = $this
        ->actingAs($user)
        ->getJson(route('invoices.data', ['draw' => 1, 'start' => 0, 'length' => 10]));

    $response->assertOk();
    $response->assertJsonStructure([
        'draw',
        'recordsTotal',
        'recordsFiltered',
        'data',
    ]);
});

test('booking invoice can be created with rate and rate type', function () {
    $user = User::factory()->create();
    $customer = Customer::create([
        'customer_type' => 'individual',
        'name' => 'Booking Invoice Customer',
        'phone_number' => '1234567890',
        'id_card_number' => 'ID-INV-BK',
        'address' => 'Booking Street',
    ]);
    $vehicle = App\Models\Vehicle::create([
        'name' => 'Test Car',
        'registration_number' => 'REG-INV-BK',
    ]);
    $booking = App\Models\Booking::create([
        'booking_id' => 'BK20260612001',
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'booking_date' => now()->toDateString(),
        'pickup_datetime' => now()->format('Y-m-d 10:00:00'),
        'return_datetime' => now()->addDays(5)->format('Y-m-d 10:00:00'),
        'from_date' => now()->toDateString(),
        'to_date' => now()->addDays(5)->toDateString(),
        'status' => 'confirmed',
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('bookings.create-invoice', $booking), [
            'invoice_date' => now()->toDateString(),
            'rate' => 100.00,
            'rate_type' => 'daily',
            'extra_kms_charges' => 50.00,
            'vat' => 5,
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Invoice created successfully.',
    ]);

    $invoice = Invoice::where('booking_id', $booking->id)->first();
    expect($invoice)->not->toBeNull();
    expect((float)$invoice->rate)->toBe(100.00);
    expect($invoice->rate_type)->toBe('daily');
    expect((float)$invoice->total)->toBe(550.00);
    expect((float)$invoice->subtotal)->toBe(522.50);
});

test('invoice can be updated with rate and rate type', function () {
    $user = User::factory()->create();
    $customer = Customer::create([
        'customer_type' => 'individual',
        'name' => 'Booking Invoice Customer 2',
        'phone_number' => '1234567890',
        'id_card_number' => 'ID-INV-BK2',
        'address' => 'Booking Street',
    ]);
    $vehicle = App\Models\Vehicle::create([
        'name' => 'Test Car 2',
        'registration_number' => 'REG-INV-BK2',
    ]);
    $booking = App\Models\Booking::create([
        'booking_id' => 'BK20260612002',
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'booking_date' => now()->toDateString(),
        'pickup_datetime' => now()->format('Y-m-d 10:00:00'),
        'return_datetime' => now()->addDays(2)->format('Y-m-d 10:00:00'),
        'from_date' => now()->toDateString(),
        'to_date' => now()->addDays(2)->toDateString(),
        'status' => 'confirmed',
    ]);
    $invoice = Invoice::create([
        'invoice_number' => 'INV-TEST-0002',
        'customer_id' => $customer->id,
        'booking_id' => $booking->id,
        'amount' => 100.00,
        'rate' => 50.00,
        'rate_type' => 'daily',
        'invoice_date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $response = $this
        ->actingAs($user)
        ->putJson(route('invoices.update', $invoice), [
            'invoice_date' => now()->toDateString(),
            'rate' => 100.00,
            'rate_type' => 'daily',
            'extra_kms_charges' => 20.00,
            'vat' => 10,
            'total' => 220.00,
        ]);

    $response->assertOk();
    
    $invoice->refresh();
    expect((float)$invoice->rate)->toBe(100.00);
    expect($invoice->rate_type)->toBe('daily');
    expect((float)$invoice->total)->toBe(220.00);
    expect((float)$invoice->subtotal)->toBe(198.00);
    expect((float)$invoice->vat_amount)->toBe(22.00);
});

