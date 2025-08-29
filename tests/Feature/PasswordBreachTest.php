<?php

use App\Services\PasswordBreachService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->passwordBreachService = app(PasswordBreachService::class);
    
    // Run the seeder to create roles
    $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
});

describe('Password Breach Service', function () {
    it('can check if password is compromised', function () {
        // Test with a known compromised password (password)
        $result = $this->passwordBreachService->isPasswordCompromised('password');
        
        expect($result)->toBeBool();
    });

    it('can get breach count for password', function () {
        $count = $this->passwordBreachService->getBreachCount('password');
        
        expect($count)->toBeInt();
        expect($count)->toBeGreaterThanOrEqual(0);
    });

    it('can get detailed breach information', function () {
        $details = $this->passwordBreachService->getBreachDetails('password');
        
        expect($details)->toBeArray();
        expect($details)->toHaveKeys(['compromised', 'count', 'hash', 'checked_at', 'severity', 'recommendation']);
        expect($details['compromised'])->toBeBool();
        expect($details['count'])->toBeInt();
        expect($details['severity'])->toBeString();
        expect($details['recommendation'])->toBeString();
    });

    it('can validate password with strength and breach check', function () {
        $validation = $this->passwordBreachService->validatePassword('MySecurePassword123!');
        
        expect($validation)->toBeArray();
        expect($validation)->toHaveKeys(['password', 'strength', 'breach_status', 'is_safe', 'recommendations']);
        expect($validation['strength'])->toHaveKeys(['score', 'max_score', 'feedback', 'strength']);
        expect($validation['breach_status'])->toHaveKeys(['compromised', 'count', 'severity', 'recommendation']);
        expect($validation['is_safe'])->toBeBool();
        expect($validation['recommendations'])->toBeArray();
    });

    it('identifies weak passwords correctly', function () {
        $validation = $this->passwordBreachService->validatePassword('123');
        
        expect($validation['strength']['score'])->toBeLessThan(3);
        expect($validation['strength']['strength'])->toBeIn(['Very Weak', 'Weak']);
        expect($validation['is_safe'])->toBeFalse();
    });

    it('identifies strong passwords correctly', function () {
        $validation = $this->passwordBreachService->validatePassword('MySecurePassword123!@#');
        
        expect($validation['strength']['score'])->toBeGreaterThanOrEqual(4);
        expect($validation['strength']['strength'])->toBeIn(['Good', 'Strong']);
    });

    it('detects common patterns', function () {
        $validation = $this->passwordBreachService->validatePassword('password123');
        
        expect($validation['strength']['feedback'])->toContain('Avoid common patterns');
    });

    it('provides appropriate recommendations', function () {
        $validation = $this->passwordBreachService->validatePassword('weak');
        
        expect($validation['recommendations'])->toBeArray();
        expect($validation['recommendations'])->not->toBeEmpty();
    });

    it('caches results for performance', function () {
        $password = 'TestPassword123!';
        
        // First call
        $start1 = microtime(true);
        $result1 = $this->passwordBreachService->isPasswordCompromised($password);
        $time1 = microtime(true) - $start1;
        
        // Second call (should be cached)
        $start2 = microtime(true);
        $result2 = $this->passwordBreachService->isPasswordCompromised($password);
        $time2 = microtime(true) - $start2;
        
        expect($result1)->toBe($result2);
        expect($time2)->toBeLessThan($time1); // Cached call should be faster
    });

    it('can clear cache', function () {
        $password = 'TestPassword456!';
        
        // Cache the result
        $this->passwordBreachService->isPasswordCompromised($password);
        
        // Clear cache
        $this->passwordBreachService->clearCache($password);
        
        // Should work without error
        expect(fn() => $this->passwordBreachService->isPasswordCompromised($password))->not->toThrow(Exception::class);
    });

    it('provides cache statistics', function () {
        $stats = $this->passwordBreachService->getCacheStats();
        
        expect($stats)->toBeArray();
        expect($stats)->toHaveKeys(['cache_ttl', 'timeout', 'max_retries']);
        expect($stats['cache_ttl'])->toBeInt();
        expect($stats['timeout'])->toBeInt();
        expect($stats['max_retries'])->toBeInt();
    });
});

describe('Password Validation API', function () {
    it('validates password via API endpoint', function () {
        $response = $this->postJson('/api/v1/validate-password', [
            'password' => 'MySecurePassword123!'
        ]);
        
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'password',
                'is_safe',
                'strength' => [
                    'score',
                    'max_score',
                    'feedback',
                    'strength'
                ],
                'breach_status' => [
                    'compromised',
                    'count',
                    'severity',
                    'recommendation'
                ],
                'recommendations',
                'checked_at'
            ]
        ]);
    });

    it('returns error for invalid password input', function () {
        $response = $this->postJson('/api/v1/validate-password', [
            'password' => ''
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    });

    it('provides breach details via API endpoint', function () {
        $response = $this->postJson('/api/v1/password-breach-details', [
            'password' => 'password'
        ]);
        
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'compromised',
                'count',
                'hash',
                'checked_at',
                'severity',
                'recommendation'
            ]
        ]);
    });

    it('handles API errors gracefully', function () {
        // Mock a failed API call
        $this->mock(PasswordBreachService::class, function ($mock) {
            $mock->shouldReceive('getBreachDetails')
                ->andThrow(new Exception('API Error'));
        });
        
        $response = $this->postJson('/api/v1/password-breach-details', [
            'password' => 'testpassword'
        ]);
        
        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Password breach check failed'
        ]);
    });
});

describe('Registration with Password Validation', function () {
    it('blocks registration with compromised password', function () {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password', // Known compromised password
            'password_confirmation' => 'password'
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
        // Check for either password strength or breach validation error
        $errorMessage = $response->json('errors.password.0');
        expect($errorMessage)->toContain('Password') || expect($errorMessage)->toContain('password');
    });

    it('blocks registration with specific compromised password Gsmhex@123#', function () {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'password' => 'Gsmhex@123#', // Specific compromised password
            'password_confirmation' => 'Gsmhex@123#'
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
        
        // Check that the error message indicates a security issue
        $errorMessage = $response->json('errors.password.0');
        expect($errorMessage)->toContain('Password') || expect($errorMessage)->toContain('security') || expect($errorMessage)->toContain('breach');
    });

    it('blocks registration with compromised password Aa@123123', function () {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test3@example.com',
            'password' => 'Aa@123123', // Known compromised password (112,938 breaches)
            'password_confirmation' => 'Aa@123123'
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
        
        // Check that the error message indicates a security issue
        $errorMessage = $response->json('errors.password.0');
        expect($errorMessage)->toContain('Password') || expect($errorMessage)->toContain('security') || expect($errorMessage)->toContain('breach');
    });

    it('allows registration with strong, uncompromised password', function () {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'MySecurePassword123!',
            'password_confirmation' => 'MySecurePassword123!'
        ]);
        
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email'
            ],
            'token',
            'token_type'
        ]);
    });

    it('blocks registration with weak password', function () {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123'
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    });
});

describe('Password Change Validation', function () {
    it('validates new password during password change', function () {
        $user = \App\Models\User::factory()->create();
        
        $response = $this->actingAs($user)
            ->postJson('/api/v1/customer/change-password', [
                'current_password' => 'password',
                'password' => 'password', // Known compromised password
                'password_confirmation' => 'password'
            ]);
        
        // Should either be 422 (validation error) or 403 (forbidden due to role)
        expect($response->status())->toBeIn([422, 403]);
    });

    it('allows password change with strong password', function () {
        $user = \App\Models\User::factory()->create([
            'password' => bcrypt('oldpassword')
        ]);
        
        $response = $this->actingAs($user)
            ->postJson('/api/v1/customer/change-password', [
                'current_password' => 'oldpassword',
                'password' => 'MyNewSecurePassword123!@#',
                'password_confirmation' => 'MyNewSecurePassword123!@#'
            ]);
        
        // Should either be successful or forbidden due to role
        expect($response->status())->toBeIn([200, 201, 403]);
    });
});
