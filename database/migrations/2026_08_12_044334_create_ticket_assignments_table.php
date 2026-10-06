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
        Schema::create('ticket_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticketId')->references('id')->on('tickets');
            $table->foreignId('teamId')->references('id')->on('support_teams');
            $table->enum("status" , ["OPEN" , "CLOSED" , "IN-PROGRESS" , "RESOLVED"])->default("OPEN");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_assignments');
    }
};
