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
        Schema::create('housing_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_id')->references('id')->on('housings')->onDelete('cascade');
            $table->foreignId('preference_id')->references('id')->on('preferences')->onDelete('cascade');
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->boolean('is_verified');
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housing_preferences');
    }
};
