<?php

use App\Models\Customer;
use App\Models\User;

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
