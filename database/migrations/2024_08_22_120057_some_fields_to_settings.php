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
        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('montant_maximum_recharge', 15, 2)->default(1000000000)->nullable();
            $table->decimal('montant_minimum_recharge', 15, 2)->default(10000)->nullable();
            $table->decimal('montant_minimum_retrait', 15, 2)->default(5000)->nullable();
            $table->decimal('montant_maximum_retrait', 15, 2)->default(1000000)->nullable();
            $table->decimal('montant_minimum_solde_retrait', 15, 2)->default(10000)->nullable();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            //
        });
    }
};
