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
            $table->double('valeur_commission_partenaire')->nullable();
            $table->double('montant_commission_partenaire')->nullable();
            $table->double('solde_commission_partenaire')->nullable();
            $table->foreignId('partenaire_id')->nullable()->references('id')->on('user_partenaires');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfeuille_transactions', function (Blueprint $table) {
            $table->dropColumn(['valeur_commission_partenaire', 'montant_commission_partenaire', 'solde_commission_partenaire','partenaire_id']);
        });
    }
};
