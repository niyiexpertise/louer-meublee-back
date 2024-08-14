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
        Schema::table('user_partenaires', function (Blueprint $table) {
            $table->float('reduction_traveler');
            $table->integer('number_of_reservation');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_partenaires', function (Blueprint $table) {
            //
        });
    }
};
