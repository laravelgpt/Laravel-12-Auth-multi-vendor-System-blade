<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    $this->adminRole = Role::create(['name' => 'Admin']);
    $this->customerRole = Role::create(['name' => 'Customer']);
});

it('can register a new user with customer role', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'SecurePass456!@#',
        'password_confirmation' => 'SecurePass456!@#',
        'phone' => '1234567890',
    ]);

    $response->assertRedirect('/customer/dashboard');
    
    $this->assertAuthenticated();
    
    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('Customer'))->toBeTrue();
    expect($user->hasRole('Admin'))->toBeFalse();
});

it('can login admin user and redirect to admin dashboard', function () {
    $admin = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);
    $admin->assignRole('Admin');

    $response = $this->post('/login', [
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/admin/dashboard');
    $this->assertAuthenticated();
});

it('can login customer user and redirect to customer dashboard', function () {
    $customer = User::factory()->create([
        'email' => 'customer@example.com',
        'password' => bcrypt('password'),
    ]);
    $customer->assignRole('Customer');

    $response = $this->post('/login', [
        'email' => 'customer@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/customer/dashboard');
    $this->assertAuthenticated();
});

it('can send OTP for email verification', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);
    $user->assignRole('Customer');

    $response = $this->post('/send-otp', [
        'email' => 'test@example.com',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    $user->refresh();
    expect($user->otp)->not->toBeNull();
    expect($user->otp_expires_at)->not->toBeNull();
});

it('can verify OTP and login user', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);
    $user->assignRole('Customer');
    
    // Generate OTP
    $otp = $user->generateOtp();

    $response = $this->post('/verify-otp', [
        'email' => 'test@example.com',
        'otp' => $otp,
    ]);

    $response->assertRedirect('/customer/dashboard');
    $this->assertAuthenticated();
    
    $user->refresh();
    expect($user->otp)->toBeNull();
    expect($user->otp_expires_at)->toBeNull();
});

it('rejects invalid OTP', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);
    $user->assignRole('Customer');

    $response = $this->post('/verify-otp', [
        'email' => 'test@example.com',
        'otp' => '123456',
    ]);

    $response->assertSessionHasErrors('otp');
    $this->assertGuest();
});

it('prevents access to admin dashboard for non-admin users', function () {
    $customer = User::factory()->create();
    $customer->assignRole('Customer');

    $response = $this->actingAs($customer)->get('/admin/dashboard');

    $response->assertForbidden();
});

it('allows access to admin dashboard for admin users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this->actingAs($admin)->get('/admin/dashboard');

    $response->assertSuccessful();
    $response->assertSee('Admin Dashboard');
});

it('prevents access to customer dashboard for non-customer users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this->actingAs($admin)->get('/customer/dashboard');

    $response->assertForbidden();
});

it('allows access to customer dashboard for customer users', function () {
    $customer = User::factory()->create();
    $customer->assignRole('Customer');

    $response = $this->actingAs($customer)->get('/customer/dashboard');

    $response->assertSuccessful();
    $response->assertSee('Customer Dashboard');
});

it('can logout user', function () {
    $user = User::factory()->create();
    $user->assignRole('Customer');

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/login');
    $this->assertGuest();
});

it('validates password strength during registration', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

it('validates email uniqueness during registration', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'SecurePass456!@#',
        'password_confirmation' => 'SecurePass456!@#',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});
