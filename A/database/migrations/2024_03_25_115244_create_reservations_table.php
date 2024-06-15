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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('housing_id')->references('id')->on('housings')->onDelete('cascade');
            $table->date('date_of_reservation');
            $table->date('date_of_starting');
            $table->date('date_of_end');
            $table->integer('number_of_adult');
            $table->integer('number_of_child');
            $table->integer('number_of_domestical_animal');
            $table->integer('number_of_baby');
            $table->string('icone')->nullable();
            $table->text('motif_rejet_hote')->nullable();
            $table->text('motif_rejet_traveler')->nullable();
            $table->text('message_to_hote')->nullable();
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
        Schema::dropIfExists('reservations');
    }
};
