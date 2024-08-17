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
            $table->double('solde_credit')->nullable();
            $table->double('solde_debit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfeuille_transactions', function (Blueprint $table) {
            $table->dropColumn('solde_credit');
            $table->dropColumn('solde_debit');
        });
    }
};
