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
    $response->assertViewHas(['customers', 'bookings']);
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
