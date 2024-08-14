<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePortfeuilleTransactionHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('portfeuille_transaction_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id'); // Référence à la transaction modifiée
            $table->string('column_name'); // Nom de la colonne modifiée
            $table->text('old_value')->nullable(); // Ancienne valeur
            $table->text('new_value')->nullable(); // Nouvelle valeur
            $table->unsignedBigInteger('modified_by'); // ID de l'utilisateur qui a modifié
            $table->timestamp('modified_at'); // Date de la modification
            $table->string('motif')->nullable(); // Motif de la modification
            $table->timestamps();

            $table->foreign('transaction_id')->references('id')->on('portfeuille_transactions')->onDelete('cascade');
            $table->foreign('modified_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('portfeuille_transaction_history');
    }
}

