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
            $table->float('valeur_commission')->nullable()->change();
            $table->double('montant_commission')->nullable()->change();
            $table->double('montant_restant')->nullable()->change();
            $table->double('solde_total')->nullable()->change();
            $table->double('solde_commission')->nullable()->change();
            $table->double('solde_restant')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfeuille_transactions', function (Blueprint $table) {
            $table->float('valeur_commission')->nullable(false)->change();
            $table->double('montant_commission')->nullable(false)->change();
            $table->double('montant_restant')->nullable(false)->change();
            $table->double('solde_total')->nullable(false)->change();
            $table->double('solde_commission')->nullable(false)->change();
            $table->double('solde_restant')->nullable(false)->change();
        });
    }
};
