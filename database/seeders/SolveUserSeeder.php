<?php

namespace Database\Seeders;

use App\Models\SolveUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SolveUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'username' => 'admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'supervisor_type' => null,
                'first_name' => 'Edward',
                'last_name' => 'Ngugi',
            ],
            [
                'username' => 'supervisor',
                'password' => Hash::make('password123'),
                'role' => 'supervisor',
                'supervisor_type' => 'technical',
                'first_name' => 'Jane',
                'last_name' => 'Atieno',
            ],
            [
                'username' => 'user',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'supervisor_type' => null,
                'first_name' => 'James',
                'last_name' => 'Mwangi',
            ],

            [
                'username' => 'user1',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'supervisor_type' => null,
                'first_name' => 'Justus',
                'last_name' => 'Ovumba',
            ],
        ];

        foreach ($users as $userData) {
            SolveUser::updateOrCreate(
                ['username' => $userData['username']],
                $userData
            );
        }
    }
}
