<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SupportTeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        $members = [
            'Edward Ngugi',
            'Eston Mbugua',
            'Franklin Okoth',
            'Bianca Wachira',
            'George Okello',
            'Samuel Kutosi',
            'Wycliff Ofuyo',
            'Ivene Kamau',
        ];

        $passwords = [
            "123456",
        ];

        $specialties = [
            1,
            2
        ];

        foreach ($members as $member) {
            [$firstName, $lastName] = explode(' ', $member, 2);

            DB::table('support_teams')->insert([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'password' => Hash::make($passwords[0]),
                'phone_number' => '+2547' . rand(10000000, 99999999),
                'email' => strtolower($firstName . '.' . $lastName . '@gmail.com'),
                'profile_picture' => 'profile_pictures/' . Str::slug($member) . '.jpg',
                'ticket_category_id' => $specialties[array_rand($specialties)],
                'available' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
