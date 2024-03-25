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
        Schema::create('preferences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icone')->nullable();
            $table->timestamps();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preferences');
    }
};
