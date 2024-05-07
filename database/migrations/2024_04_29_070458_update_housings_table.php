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
        Schema::table('housings', function (Blueprint $table) {
            $table->integer('delai_partiel_remboursement')->nullable();
            $table->integer('delai_integral_remboursement')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housings', function (Blueprint $table) {
            //
        });
    }
};
