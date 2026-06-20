<?php

use App\Models\Supplier;
use App\Models\User;

test('supplier index screen can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('suppliers.index'));

    $response->assertOk();
    $response->assertSee('Add New Supplier');
});

test('supplier create screen can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('suppliers.create'));

    $response->assertOk();
    $response->assertSee('Add New Supplier');
});

test('supplier can be stored with valid input', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('suppliers.store'), [
            'name' => 'Acme Supplier',
            'phone' => '1234567890',
            'email' => 'acme@example.com',
            'address' => '123 Acme St',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('suppliers.index'));

    $this->assertDatabaseHas('suppliers', [
        'name' => 'Acme Supplier',
        'phone' => '1234567890',
        'email' => 'acme@example.com',
        'address' => '123 Acme St',
    ]);
});

test('supplier store via AJAX returns JSON success response', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->postJson(route('suppliers.store'), [
            'name' => 'Beta Supplier',
            'phone' => '0987654321',
            'email' => 'beta@example.com',
            'address' => '456 Beta Ave',
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Supplier created successfully.',
    ]);
});

test('supplier update via AJAX returns JSON success response', function () {
    $user = User::factory()->create();

    $supplier = Supplier::factory()->create([
        'name' => 'Original Supplier',
    ]);

    $response = $this
        ->actingAs($user)
        ->putJson(route('suppliers.update', $supplier), [
            'name' => 'Updated Supplier',
            'phone' => $supplier->phone,
            'email' => 'updated@example.com',
            'address' => $supplier->address,
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Supplier updated successfully.',
    ]);

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'name' => 'Updated Supplier',
        'email' => 'updated@example.com',
    ]);
});

test('supplier can be deleted', function () {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('suppliers.destroy', $supplier));

    $response->assertRedirect(route('suppliers.index'));
    $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
});
