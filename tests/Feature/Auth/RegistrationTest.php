<?php

use Spatie\Permission\Models\Role;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    // Create Customer role if it doesn't exist
    $customerRole = Role::firstOrCreate(['name' => 'Customer']);
    
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'SecurePass456!@#',
        'password_confirmation' => 'SecurePass456!@#',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('customer.dashboard', absolute: false));
});
