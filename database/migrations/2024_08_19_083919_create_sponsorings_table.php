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
        Schema::create('sponsorings', function (Blueprint $table) {
            $table->id();
            $table->integer('duree')->nullable();
            $table->decimal('prix', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_deleted')->default(false)->nullable();
            $table->boolean('is_actif')->default(true)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsorings');
    }
};