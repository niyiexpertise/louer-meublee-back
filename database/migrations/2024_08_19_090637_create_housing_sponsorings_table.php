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
        Schema::create('housing_sponsorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_id')->nullable()->constrained('housings')->onDelete('cascade');
            $table->foreignId('sponsoring_id')->nullable()->constrained('sponsorings')->onDelete('cascade');
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->boolean('is_deleted')->default(false)->nullable();
            $table->boolean('is_actif')->default(false)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housing_sponsorings');
    }
};
