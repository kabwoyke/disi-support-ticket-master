<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::connection('mysql_solves')->create('questions', function (Blueprint $table) {
            $table->id('id');
            $table->text('title');
            $table->text('description');
            $table->enum('category', ['ibml', 'softtrac', 'omniscan']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->integer('created_by');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('views')->default(0);
            $table->integer('is_final')->default(0);
            $table->text('attachment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_solves')->dropIfExists('questions');
    }
};
