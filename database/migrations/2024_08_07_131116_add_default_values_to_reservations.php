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
            $table->float('valeur_reduction_hote')->default(0)->change();
            $table->float('valeur_promotion_hote')->default(0)->change();
            $table->float('valeur_reduction_code_promo')->default(0)->change();
            $table->float('valeur_reduction_staturp')->default(0)->change();
            $table->float('montant_charge')->default(0)->change();
            $table->float('montant_housing')->default(0)->change();
            $table->float('montant_a_paye')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->float('valeur_reduction_hote')->default(null)->change();
            $table->float('valeur_promotion_hote')->default(null)->change();
            $table->float('valeur_reduction_code_promo')->default(null)->change();
            $table->float('valeur_reduction_staturp')->default(null)->change();
            $table->float('montant_charge')->default(null)->change();
            $table->float('montant_housing')->default(null)->change();
            $table->float('montant_a_paye')->default(null)->change();
        });
    }
};

