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
        Schema::table('reservations', function (Blueprint $table) {
            $table->float('valeur_reduction_hote');
            $table->float('valeur_promotion_hote');
            $table->float('valeur_reduction_code_promo');
            $table->float('valeur_reduction_staturp');
            $table->float('montant_charge');
            $table->float('montant_housing');
            $table->float('montant_a_paye');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            //
        });
    }
};
