<?php

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;

test('bill index screen can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('bills.index'));

    $response->assertOk();
    $response->assertViewHas('invoices');
});

test('bill can be stored with valid data', function () {
    $user = User::factory()->create();
    $customer = Customer::create([
        'customer_type' => 'individual',
        'name' => 'Bill Customer',
        'phone_number' => '1111111111',
        'id_card_number' => 'ID-BILL-001',
        'address' => 'Bill Street',
    ]);

    $invoice = Invoice::create([
        'invoice_number' => 'INV-BILL-0001',
        'customer_id' => $customer->id,
        'amount' => 100.00,
        'invoice_date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('bills.store'), [
            'invoice_id' => $invoice->id,
            'amount' => 100.00,
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'status' => 'unpaid',
            'notes' => 'Test bill',
        ]);

    $response->assertRedirect(route('bills.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('bills', [
        'invoice_id' => $invoice->id,
        'amount' => 100.00,
        'status' => 'unpaid',
    ]);
});

test('bill store via AJAX returns JSON success response', function () {
    $user = User::factory()->create();
    $customer = Customer::create([
        'customer_type' => 'individual',
        'name' => 'Bill Ajax Customer',
        'phone_number' => '2222222222',
        'id_card_number' => 'ID-BILL-002',
        'address' => 'Ajax Street',
    ]);

    $invoice = Invoice::create([
        'invoice_number' => 'INV-BILL-0002',
        'customer_id' => $customer->id,
        'amount' => 220.00,
        'invoice_date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('bills.store'), [
            'invoice_id' => $invoice->id,
            'amount' => 220.00,
            'bill_date' => now()->toDateString(),
            'status' => 'paid',
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Bill created successfully.',
    ]);
});

test('bills data endpoint returns datatables json', function () {
    $user = User::factory()->create();
    $customer = Customer::create([
        'customer_type' => 'individual',
        'name' => 'Bill List Customer',
        'phone_number' => '3333333333',
        'id_card_number' => 'ID-BILL-003',
        'address' => 'List Street',
    ]);

    $invoice = Invoice::create([
        'invoice_number' => 'INV-BILL-0003',
        'customer_id' => $customer->id,
        'amount' => 330.00,
        'invoice_date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    Bill::create([
        'bill_number' => 'BILL-TEST-0001',
        'invoice_id' => $invoice->id,
        'amount' => 330.00,
        'bill_date' => now()->toDateString(),
        'status' => 'unpaid',
    ]);

    $response = $this
        ->actingAs($user)
        ->getJson(route('bills.data', ['draw' => 1, 'start' => 0, 'length' => 10]));

    $response->assertOk();
    $response->assertJsonStructure([
        'draw',
        'recordsTotal',
        'recordsFiltered',
        'data',
    ]);
});
