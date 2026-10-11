<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed requester accounts (the people who raise tickets).
     * Safe to re-run: users are matched by email. Password for all: password123
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Test User',     'email' => 'test@example.com'],
            ['name' => 'Grace Wanjiku', 'email' => 'grace.wanjiku@example.com'],
            ['name' => 'Peter Otieno',  'email' => 'peter.otieno@example.com'],
            ['name' => 'Mary Achieng',  'email' => 'mary.achieng@example.com'],
            ['name' => 'John Kamau',    'email' => 'john.kamau@example.com'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    // The model's "hashed" cast hashes this on save.
                    'password' => 'password123',
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
