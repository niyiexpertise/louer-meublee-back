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
        Schema::table('payements', function (Blueprint $table) {
            $table->foreignId('reservation_id')->nullable()->change();
            $table->foreignId('housing_sponsoring_id')
                  ->nullable()
                  ->constrained('housing_sponsorings')
                  ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payements', function (Blueprint $table) {
            // Retirer la contrainte de clé étrangère et supprimer la colonne housing_sponsoring_id
            $table->dropForeign(['housing_sponsoring_id']);
            $table->dropColumn('housing_sponsoring_id');

            // Rendre la colonne reservation_id non-nullable (revenir à l'état initial)
            $table->foreignId('reservation_id')->nullable(false)->change();
        });
    }
};
