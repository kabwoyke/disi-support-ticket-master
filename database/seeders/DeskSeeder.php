<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $desks = [
            // Quality Control Desks (3)
            ['desk_name' => 'DESK-QC-01', 'created_at' => now(), 'updated_at' => now()],
            ['desk_name' => 'DESK-QC-02', 'created_at' => now(), 'updated_at' => now()],
            ['desk_name' => 'DESK-QC-03', 'created_at' => now(), 'updated_at' => now()],

            // Preparation Desks (3)
            ['desk_name' => 'DESK-PREP-01', 'created_at' => now(), 'updated_at' => now()],
            ['desk_name' => 'DESK-PREP-02', 'created_at' => now(), 'updated_at' => now()],
            ['desk_name' => 'DESK-PREP-03', 'created_at' => now(), 'updated_at' => now()],

            // Scanning Desks (3)
            ['desk_name' => 'DESK-SCN-01', 'created_at' => now(), 'updated_at' => now()],
            ['desk_name' => 'DESK-SCN-02', 'created_at' => now(), 'updated_at' => now()],
            ['desk_name' => 'DESK-SCN-03', 'created_at' => now(), 'updated_at' => now()],

            // Restoration Desks (3)
            ['desk_name' => 'DESK-REST-01', 'created_at' => now(), 'updated_at' => now()],
            ['desk_name' => 'DESK-REST-02', 'created_at' => now(), 'updated_at' => now()],
            ['desk_name' => 'DESK-REST-03', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('desks')->insert($desks);
    }
}
