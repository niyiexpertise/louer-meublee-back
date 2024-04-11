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
        Schema::create('housing_reductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_id')->references('id')->on('housings')->onDelete('cascade');
            $table->foreignId('reduction_id')->references('id')->on('reductions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housing_reductions');
    }
};
