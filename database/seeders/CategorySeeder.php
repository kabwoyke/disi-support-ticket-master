<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        $categories = [
            'Hardware',
            'Software',
        ];

        foreach ($categories as $cat) {
            DB::table('ticket_categories')->updateOrInsert(
                ['category_name' => $cat],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
