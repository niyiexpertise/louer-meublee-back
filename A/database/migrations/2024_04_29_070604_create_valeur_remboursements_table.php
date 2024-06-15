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
        Schema::create('valeur_remboursements', function (Blueprint $table) {
            $table->id();
            $table->float('valeur_integral_remboursement');
            $table->float('valeur_partiel_remboursement');
            $table->float('valeur_partiel_remboursement_hote');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valeur_remboursements');
    }
};
