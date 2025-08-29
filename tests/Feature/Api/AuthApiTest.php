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

it('can register user via API', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'SecurePass456!@#',
        'password_confirmation' => 'SecurePass456!@#',
        'phone' => '1234567890',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
                'phone',
                'roles',
                'permissions',
            ],
            'token',
            'token_type',
        ]);

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('Customer'))->toBeTrue();
});

it('can login user via API', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
    $user->assignRole('Customer');

    $response = $this->postJson('/api/v1/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
                'roles',
                'permissions',
            ],
            'token',
            'token_type',
        ]);
});

it('can send OTP via API', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);
    $user->assignRole('Customer');

    $response = $this->postJson('/api/v1/send-otp', [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'OTP sent successfully',
        ]);

    $user->refresh();
    expect($user->otp)->not->toBeNull();
    expect($user->otp_expires_at)->not->toBeNull();
});

it('can verify OTP via API', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);
    $user->assignRole('Customer');
    
    $otp = $user->generateOtp();

    $response = $this->postJson('/api/v1/verify-otp', [
        'email' => 'test@example.com',
        'otp' => $otp,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
                'roles',
                'permissions',
            ],
            'token',
            'token_type',
        ]);

    $user->refresh();
    expect($user->otp)->toBeNull();
    expect($user->otp_expires_at)->toBeNull();
});

it('can get current user via API', function () {
    $user = User::factory()->create();
    $user->assignRole('Customer');

    $response = $this->actingAs($user)
        ->withHeaders(['Authorization' => 'Bearer ' . $user->createToken('test')->plainTextToken])
        ->getJson('/api/v1/me');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
                'roles',
                'permissions',
            ],
        ]);
});

it('can logout user via API', function () {
    $user = User::factory()->create();
    $user->assignRole('Customer');
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->postJson('/api/v1/logout');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Logged out successfully',
        ]);

    // Token should be revoked
    expect($user->tokens)->toHaveCount(0);
});

it('can refresh token via API', function () {
    $user = User::factory()->create();
    $user->assignRole('Customer');
    $oldToken = $user->createToken('test')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => 'Bearer ' . $oldToken])
        ->postJson('/api/v1/refresh');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'token',
            'token_type',
        ]);

    // Old token should be revoked
    expect($user->tokens)->toHaveCount(1);
    expect($user->tokens->first()->name)->toBe('auth-token');
});

it('validates registration data via API', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => '',
        'email' => 'invalid-email',
        'password' => 'weak',
        'password_confirmation' => 'different',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('validates login data via API', function () {
    $response = $this->postJson('/api/v1/login', [
        'email' => 'invalid-email',
        'password' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

it('validates OTP data via API', function () {
    $response = $this->postJson('/api/v1/verify-otp', [
        'email' => 'invalid-email',
        'otp' => '123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'otp']);
});

it('rejects invalid credentials via API', function () {
    $response = $this->postJson('/api/v1/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Login failed',
        ]);
});

it('rejects invalid OTP via API', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);
    $user->assignRole('Customer');

    $response = $this->postJson('/api/v1/verify-otp', [
        'email' => 'test@example.com',
        'otp' => '123456',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'OTP verification failed',
        ]);
});

it('requires authentication for protected endpoints', function () {
    $response = $this->getJson('/api/v1/me');

    $response->assertStatus(401);
});

it('validates email uniqueness during registration via API', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->postJson('/api/v1/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'SecurePass456!@#',
        'password_confirmation' => 'SecurePass456!@#',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});
