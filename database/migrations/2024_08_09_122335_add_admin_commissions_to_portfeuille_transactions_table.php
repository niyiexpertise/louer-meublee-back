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
            // Ajout des colonnes pour l'administrateur
            $table->double('valeur_commission_admin')->nullable();
            $table->double('montant_commission_admin')->nullable();
            $table->double('new_solde_admin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfeuille_transactions', function (Blueprint $table) {
            // Suppression des colonnes pour l'administrateur
            $table->dropColumn(['valeur_commission_admin', 'montant_commission_admin', 'new_solde_admin']);
        });
    }
};

