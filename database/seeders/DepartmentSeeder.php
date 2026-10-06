<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Quality Control',
            'Scanning',
            'Preparation',
        ];

        foreach ($departments as $department) {
            DB::table('departments')->updateOrInsert(
                ['department_name' => $department],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}

