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
        Schema::table('housing_sponsorings', function (Blueprint $table) {
            $table->boolean('is_rejected')->default(false)->nullable();
            $table->text('motif')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housing_sponsorings', function (Blueprint $table) {
            //
        });
    }
};
