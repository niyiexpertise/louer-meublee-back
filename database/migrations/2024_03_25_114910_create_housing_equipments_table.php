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
        Schema::create('housing_equipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_id')->references('id')->on('housings')->onDelete('cascade');
            $table->foreignId('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housing_equipments');
    }
};
