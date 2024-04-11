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
        Schema::create('equipment_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcategory_id')->references('id')->on('subcategories')->onDelete('cascade');
            $table->foreignId('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_subcategories');
    }
};
