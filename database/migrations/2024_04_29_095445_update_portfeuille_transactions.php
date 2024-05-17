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
        Schema::table('portfeuille_transactions', function (Blueprint $table) {
            $table->float('valeur_commission');
            $table->double('montant_commission');
            $table->double('montant_restant');
            $table->double('solde_total');
            $table->double('solde_commission');
            $table->double('solde_restant');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfeuille_transactions', function (Blueprint $table) {
            //
        });
    }
};
