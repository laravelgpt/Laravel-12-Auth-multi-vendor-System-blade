<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class PasswordBreachService
{
    private string $apiUrl = 'https://api.pwnedpasswords.com/range/';
    private int $cacheTtl = 3600; // 1 hour cache
    private int $timeout = 10;
    private int $maxRetries = 3;

    /**
     * Check if password has been compromised in data breaches (real-time)
     */
    public function isPasswordCompromised(string $password): bool
    {
        $hash = $this->generateHash($password);
        $cacheKey = "password_breach_{$hash}";

        // Check cache first
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            Log::info("Password breach check from cache", [
                'hash' => $hash,
                'compromised' => $cached['compromised'],
                'count' => $cached['count'] ?? 0
            ]);
            return $cached['compromised'];
        }

        // Real-time check
        $result = $this->performRealTimeCheck($password);
        
        // Cache the result
        Cache::put($cacheKey, $result, $this->cacheTtl);

        return $result['compromised'];
    }

    /**
     * Get breach count for a password (real-time)
     */
    public function getBreachCount(string $password): int
    {
        $hash = $this->generateHash($password);
        $cacheKey = "password_breach_{$hash}";

        // Check cache first
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            return $cached['count'] ?? 0;
        }

        // Real-time check
        $result = $this->performRealTimeCheck($password);
        
        // Cache the result
        Cache::put($cacheKey, $result, $this->cacheTtl);

        return $result['count'];
    }

    /**
     * Get detailed breach information
     */
    public function getBreachDetails(string $password): array
    {
        $hash = $this->generateHash($password);
        $cacheKey = "password_breach_details_{$hash}";

        // Check cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->performRealTimeCheck($password);
        
        $details = [
            'compromised' => $result['compromised'],
            'count' => $result['count'],
            'hash' => $hash,
            'checked_at' => now()->toISOString(),
            'severity' => $this->getSeverityLevel($result['count']),
            'recommendation' => $this->getRecommendation($result['count'])
        ];

        // Cache the result
        Cache::put($cacheKey, $details, $this->cacheTtl);

        return $details;
    }

    /**
     * Check password strength and breach status
     */
    public function validatePassword(string $password): array
    {
        $strength = $this->checkPasswordStrength($password);
        $breachDetails = $this->getBreachDetails($password);

        return [
            'password' => $password,
            'strength' => $strength,
            'breach_status' => $breachDetails,
            'is_safe' => $strength['score'] >= 3 && !$breachDetails['compromised'],
            'recommendations' => $this->getPasswordRecommendations($strength, $breachDetails)
        ];
    }

    /**
     * Perform real-time breach check
     */
    private function performRealTimeCheck(string $password): array
    {
        $sha1Hash = strtoupper(sha1($password));
        $prefix = substr($sha1Hash, 0, 5);
        $suffix = substr($sha1Hash, 5);

        $attempts = 0;
        while ($attempts < $this->maxRetries) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'User-Agent' => 'Laravel-Password-Breach-Checker/1.0',
                        'Accept' => 'text/plain'
                    ])
                    ->get($this->apiUrl . $prefix);

                if ($response->successful()) {
                    $hashes = explode("\n", $response->body());
                    
                    foreach ($hashes as $hash) {
                        $parts = explode(':', trim($hash));
                        if (count($parts) === 2 && $parts[0] === $suffix) {
                            $count = (int) $parts[1];
                            
                            Log::info("Password found in breaches (real-time)", [
                                'count' => $count,
                                'hash_prefix' => $prefix,
                                'severity' => $this->getSeverityLevel($count)
                            ]);

                            return [
                                'compromised' => true,
                                'count' => $count
                            ];
                        }
                    }

                    return [
                        'compromised' => false,
                        'count' => 0
                    ];
                }

                Log::warning("Password breach API returned non-successful status", [
                    'status' => $response->status(),
                    'attempt' => $attempts + 1
                ]);

            } catch (\Exception $e) {
                Log::error('Password breach check failed', [
                    'error' => $e->getMessage(),
                    'attempt' => $attempts + 1,
                    'hash_prefix' => $prefix
                ]);
            }

            $attempts++;
            if ($attempts < $this->maxRetries) {
                sleep(1); // Wait before retry
            }
        }

        // If all attempts failed, return safe (don't block registration)
        Log::error('Password breach check failed after all retries', [
            'hash_prefix' => $prefix
        ]);

        return [
            'compromised' => false,
            'count' => 0
        ];
    }

    /**
     * Check password strength
     */
    private function checkPasswordStrength(string $password): array
    {
        $score = 0;
        $feedback = [];

        // Length check
        if (strlen($password) >= 12) {
            $score += 2;
        } elseif (strlen($password) >= 8) {
            $score += 1;
        } else {
            $feedback[] = 'Password should be at least 8 characters long';
        }

        // Uppercase letters
        if (preg_match('/[A-Z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Include uppercase letters';
        }

        // Lowercase letters
        if (preg_match('/[a-z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Include lowercase letters';
        }

        // Numbers
        if (preg_match('/[0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Include numbers';
        }

        // Special characters
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Include special characters';
        }

        // Common patterns check
        if ($this->hasCommonPatterns($password)) {
            $score = max(0, $score - 1);
            $feedback[] = 'Avoid common patterns';
        }

        return [
            'score' => min(5, $score),
            'max_score' => 5,
            'feedback' => $feedback,
            'strength' => $this->getStrengthLabel($score)
        ];
    }

    /**
     * Check for common patterns
     */
    private function hasCommonPatterns(string $password): bool
    {
        $commonPatterns = [
            '123456',
            'password',
            'qwerty',
            'abc123',
            'letmein',
            'admin',
            'welcome',
            'monkey',
            'dragon',
            'master'
        ];

        $passwordLower = strtolower($password);
        
        foreach ($commonPatterns as $pattern) {
            if (str_contains($passwordLower, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get strength label
     */
    private function getStrengthLabel(int $score): string
    {
        return match($score) {
            0, 1 => 'Very Weak',
            2 => 'Weak',
            3 => 'Fair',
            4 => 'Good',
            5 => 'Strong',
            default => 'Unknown'
        };
    }

    /**
     * Get severity level based on breach count
     */
    private function getSeverityLevel(int $count): string
    {
        return match(true) {
            $count === 0 => 'Safe',
            $count <= 10 => 'Low',
            $count <= 100 => 'Medium',
            $count <= 1000 => 'High',
            default => 'Critical'
        };
    }

    /**
     * Get recommendation based on breach count
     */
    private function getRecommendation(int $count): string
    {
        return match(true) {
            $count === 0 => 'This password appears to be safe.',
            $count <= 10 => 'This password has been found in a few breaches. Consider changing it.',
            $count <= 100 => 'This password has been found in multiple breaches. Strongly recommend changing it.',
            $count <= 1000 => 'This password has been found in many breaches. You must change it immediately.',
            default => 'This password has been compromised extensively. Change it immediately and use a password manager.'
        };
    }

    /**
     * Get password recommendations
     */
    private function getPasswordRecommendations(array $strength, array $breachDetails): array
    {
        $recommendations = [];

        // Strength recommendations
        if ($strength['score'] < 3) {
            $recommendations[] = 'Password strength is too low. ' . implode(', ', $strength['feedback']);
        }

        // Breach recommendations
        if ($breachDetails['compromised']) {
            $recommendations[] = $breachDetails['recommendation'];
        }

        // General recommendations
        if (empty($recommendations)) {
            $recommendations[] = 'Password meets security requirements.';
        }

        return $recommendations;
    }

    /**
     * Generate hash for caching
     */
    private function generateHash(string $password): string
    {
        return hash('sha256', $password . config('app.key'));
    }

    /**
     * Clear cache for a password
     */
    public function clearCache(string $password): void
    {
        $hash = $this->generateHash($password);
        Cache::forget("password_breach_{$hash}");
        Cache::forget("password_breach_details_{$hash}");
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        return [
            'cache_ttl' => $this->cacheTtl,
            'timeout' => $this->timeout,
            'max_retries' => $this->maxRetries
        ];
    }
}
