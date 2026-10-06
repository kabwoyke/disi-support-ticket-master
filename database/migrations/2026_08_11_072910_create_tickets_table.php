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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->text("subject");
            $table->foreignId("categoryId")->references('id')->on("ticket_categories");
            $table->enum("priority" , ["LOW" , "HIGH" , "MODERATE"])->default("LOW");
            $table->foreignId("equipmentId")->references('id')->on("equipments");
            $table->foreignId("departmentId")->references('id')->on("departments");
            $table->foreignId("deskId")->references("id")->on('desks');
             $table->foreignId("userId")->references("id")->on('users');
            $table->text("description");
            $table->json("attachment_url");
            $table->enum("status" , ["OPEN" , "CLOSED" , "IN-PROGRESS" , "RESOLVED"])->default("OPEN");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
