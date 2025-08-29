<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin Users
        $adminUsers = [
            [
                'name' => 'System Administrator',
                'email' => 'system@example.com',
                'password' => 'SystemAdmin123!',
                'phone' => '+1234567891',
                'email_verified_at' => now(),
                'is_active' => true,
                'role' => 'Admin'
            ],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => 'SuperAdmin123!',
                'phone' => '+1234567892',
                'email_verified_at' => now(),
                'is_active' => true,
                'role' => 'Admin'
            ]
        ];

        // Create Customer Users
        $customerUsers = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'CustomerPassword123!',
                'phone' => '+1234567893',
                'email_verified_at' => now(),
                'is_active' => true,
                'role' => 'Customer'
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => 'CustomerPassword123!',
                'phone' => '+1234567894',
                'email_verified_at' => now(),
                'is_active' => true,
                'role' => 'Customer'
            ],
            [
                'name' => 'Mike Johnson',
                'email' => 'mike@example.com',
                'password' => 'CustomerPassword123!',
                'phone' => '+1234567895',
                'email_verified_at' => now(),
                'is_active' => true,
                'role' => 'Customer'
            ],
            [
                'name' => 'Sarah Wilson',
                'email' => 'sarah@example.com',
                'password' => 'CustomerPassword123!',
                'phone' => '+1234567896',
                'email_verified_at' => now(),
                'is_active' => true,
                'role' => 'Customer'
            ],
            [
                'name' => 'David Brown',
                'email' => 'david@example.com',
                'password' => 'CustomerPassword123!',
                'phone' => '+1234567897',
                'email_verified_at' => now(),
                'is_active' => true,
                'role' => 'Customer'
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily@example.com',
                'password' => 'CustomerPassword123!',
                'phone' => '+1234567898',
                'email_verified_at' => now(),
                'is_active' => true,
                'role' => 'Customer'
            ],
            [
                'name' => 'Robert Miller',
                'email' => 'robert@example.com',
                'password' => 'CustomerPassword123!',
                'phone' => '+1234567899',
                'email_verified_at' => now(),
                'is_active' => true,
                'role' => 'Customer'
            ],
            [
                'name' => 'Lisa Garcia',
                'email' => 'lisa@example.com',
                'password' => 'CustomerPassword123!',
                'phone' => '+1234567900',
                'email_verified_at' => now(),
                'is_active' => true,
                'role' => 'Customer'
            ],
            [
                'name' => 'James Rodriguez',
                'email' => 'james@example.com',
                'password' => 'CustomerPassword123!',
                'phone' => '+1234567901',
                'email_verified_at' => now(),
                'is_active' => true,
                'role' => 'Customer'
            ],
            [
                'name' => 'Maria Martinez',
                'email' => 'maria@example.com',
                'password' => 'CustomerPassword123!',
                'phone' => '+1234567902',
                'email_verified_at' => now(),
                'is_active' => true,
                'role' => 'Customer'
            ]
        ];

        // Create Inactive Users (for testing)
        $inactiveUsers = [
            [
                'name' => 'Inactive User',
                'email' => 'inactive@example.com',
                'password' => 'InactivePassword123!',
                'phone' => '+1234567903',
                'email_verified_at' => now(),
                'is_active' => false,
                'role' => 'Customer'
            ],
            [
                'name' => 'Suspended Admin',
                'email' => 'suspended@example.com',
                'password' => 'SuspendedPassword123!',
                'phone' => '+1234567904',
                'email_verified_at' => now(),
                'is_active' => false,
                'role' => 'Admin'
            ]
        ];

        // Combine all users
        $allUsers = array_merge($adminUsers, $customerUsers, $inactiveUsers);

        foreach ($allUsers as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            // Check if user already exists
            $existingUser = User::where('email', $userData['email'])->first();
            
            if ($existingUser) {
                $this->command->info("User already exists: {$userData['email']} - Skipping");
                continue;
            }

            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'phone' => $userData['phone'],
                'email_verified_at' => $userData['email_verified_at'],
                'is_active' => $userData['is_active'],
            ]);

            // Assign role
            $user->assignRole($role);

            $this->command->info("Created {$role} user: {$user->name} ({$user->email})");
        }

        // Create additional random users using factory
        User::factory(20)->create()->each(function ($user) {
            $user->assignRole('Customer');
        });

        $this->command->info('UserSeeder completed successfully!');
        $this->command->info('Created ' . count($allUsers) . ' specific users and 20 random users.');
        $this->command->info('');
        $this->command->info('Default Login Credentials:');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Customer: customer@example.com / password');
        $this->command->info('System Admin: system@example.com / SystemAdmin123!');
        $this->command->info('John Doe: john@example.com / CustomerPassword123!');
    }
}
