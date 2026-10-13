<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Moves the category taxonomy from config/solves.php into editable tables, and backfills missing question dates. */
return new class extends Migration
{
    protected $connection = 'mysql_solves';

    public function up(): void
    {
        Schema::connection($this->connection)->create('solve_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('label', 100);
            $table->timestamps();
        });

        Schema::connection($this->connection)->create('solve_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('solve_categories')->cascadeOnDelete();
            $table->string('slug', 50);
            $table->string('label', 100);
            $table->timestamps();
            $table->unique(['category_id', 'slug']);
        });

        $db = DB::connection($this->connection);
        foreach (config('solves.categories', []) as $slug => $cat) {
            $id = $db->table('solve_categories')->insertGetId(['slug' => $slug, 'label' => $cat['label'], 'created_at' => now(), 'updated_at' => now()]);
            foreach ($cat['subcategories'] as $subSlug => $label) {
                $db->table('solve_subcategories')->insert(['category_id' => $id, 'slug' => $subSlug, 'label' => $label, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        // Admin-posted questions were inserted without timestamps, which rendered as "Invalid date".
        $db->table('questions')->whereNull('created_at')->update(['created_at' => now()]);
        $db->table('questions')->whereNull('updated_at')->update(['updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('solve_subcategories');
        Schema::connection($this->connection)->dropIfExists('solve_categories');
    }
};
