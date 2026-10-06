<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipments = [
            // Category ID 1: Hardware
            ['name' => 'Scanners', 'categoryId' => 1],
            ['name' => 'Server', 'categoryId' => 1],
            ['name' => 'Printers', 'categoryId' => 1],
            ['name' => 'Desktops', 'categoryId' => 1],
            ['name' => 'Network', 'categoryId' => 1],

            // Category ID 2: Software
            ['name' => 'FTS (File Tracking System)', 'categoryId' => 2],
            ['name' => 'OCR Engine', 'categoryId' => 2],
            ['name' => 'Data Auto Cleanup', 'categoryId' => 2],
            ['name' => 'QCC Software (Cropping, Rotating)', 'categoryId' => 2],
            ['name' => 'Payroll System', 'categoryId' => 2],
            ['name' => 'Clocking Attendance Software', 'categoryId' => 2],
        ];

        foreach ($equipments as $item) {
            DB::table('equipments')->updateOrInsert(
                [
                    'name' => $item['name'],
                    'categoryId' => $item['categoryId'],
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
