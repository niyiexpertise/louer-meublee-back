<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('criterias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icone')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
        });
        DB::table('criterias')->insert([
            ['name' => 'Communication', 'icone' => NULL, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Accueil', 'icone' => NULL, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Propreté', 'icone' => NULL, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Confort', 'icone' => NULL, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Localisation', 'icone' => NULL, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Equipements', 'icone' => NULL, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sécurité', 'icone' => NULL, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Flexibilité', 'icone' => NULL, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rapport qualité-prix', 'icone' => NULL, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Services', 'icone' => NULL, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criterias');
    }
};
