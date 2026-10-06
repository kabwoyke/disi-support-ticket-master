<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'mysql_solves';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::connection($this->connection)->create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->references('id')->on('questions');
            $table->text('answer_text');
            $table->foreignId('created_by')->references('id')->on('solve_users');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('attachment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
       Schema::connection($this->connection)->dropIfExists('answers');
    }
};
