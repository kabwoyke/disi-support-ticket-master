<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Questions move from a product-only enum to category (hardware/software) + subcategory. */
return new class extends Migration
{
    public function up(): void
    {
        $db = DB::connection('mysql_solves');

        // category was an enum of products (ibml, softtrac, omniscan); products become subcategories.
        $db->statement('ALTER TABLE questions MODIFY category VARCHAR(50) NOT NULL');

        Schema::connection('mysql_solves')->table('questions', function (Blueprint $table) {
            $table->string('subcategory', 50)->nullable()->after('category');
            $table->index(['category', 'subcategory']);
        });

        $db->table('questions')->update(['subcategory' => $db->raw('category')]);
        $db->table('questions')->where('subcategory', 'ibml')->update(['category' => 'hardware']);
        $db->table('questions')->whereIn('subcategory', ['softtrac', 'omniscan'])->update(['category' => 'software']);
    }

    public function down(): void
    {
        $db = DB::connection('mysql_solves');

        $db->table('questions')->whereNotNull('subcategory')->update(['category' => $db->raw('subcategory')]);
        $db->table('questions')->whereNotIn('category', ['ibml', 'softtrac', 'omniscan'])->update(['category' => 'ibml']);

        Schema::connection('mysql_solves')->table('questions', function (Blueprint $table) {
            $table->dropIndex(['category', 'subcategory']);
            $table->dropColumn('subcategory');
        });

        $db->statement("ALTER TABLE questions MODIFY category ENUM('ibml','softtrac','omniscan') NOT NULL");
    }
};
