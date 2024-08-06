<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insérer le droit 'partenaire' dans la table 'rights'
        DB::table('rights')->insert([
            'name' => 'partenaire',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer le droit 'partenaire' de la table 'rights'
        DB::table('rights')->where('name', 'partenaire')->delete();
    }
};
