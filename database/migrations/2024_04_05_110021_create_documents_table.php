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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_actif');
            $table->string('icone')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
        });
        DB::table('documents')->insert([
            ['name' => 'CIP', 'is_actif' => true, 'icone' => NULL, 'is_deleted' => false, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Carte d\'identité', 'is_actif' => true, 'icone' => NULL, 'is_deleted' => false, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Acte de naissance', 'is_actif' => true, 'icone' => NULL, 'is_deleted' => false, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Passeport', 'is_actif' => true, 'icone' => NULL, 'is_deleted' => false, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Carte bibliométrique', 'is_actif' => true, 'icone' => NULL, 'is_deleted' => false, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'RIB', 'is_actif' => true, 'icone' => NULL, 'is_deleted' => false, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Document 1', 'is_actif' => true, 'icone' => NULL, 'is_deleted' => false, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Document 2', 'is_actif' => true, 'icone' => NULL, 'is_deleted' => false, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Document 3', 'is_actif' => true, 'icone' => NULL, 'is_deleted' => false, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Document 4', 'is_actif' => true, 'icone' => NULL, 'is_deleted' => false, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
