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
            $table->string('code_pays');
            $table->integer('telephone_traveler');
            $table->string('photo');
            $table->time('heure_arrivee_max');
            $table->time('heure_arrivee_min');
            $table->boolean('is_tranche_paiement');
            $table->double('montant_total');
            $table->double('valeur_payee');
            $table->boolean('is_confirmed_hote');
            $table->boolean('is_integration');
            $table->boolean('is_rejected_traveler');
            $table->boolean('is_rejected_hote');
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
