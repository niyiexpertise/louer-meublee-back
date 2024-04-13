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
        Schema::create('property_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icone')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
        });

       DB::table('property_types')->insert([
    ['name' => 'Appartement', 'icone' => null, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'Maison', 'icone' => null, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'Studio', 'icone' => null, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'Villa', 'icone' => null, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'Chalet', 'icone' => null, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'Bungalow', 'icone' => null, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'Maison d\'hôtes', 'icone' => null, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'Cabane dans les arbres', 'icone' => null, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'Château', 'icone' => null, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'Yourte', 'icone' => null, 'is_deleted' => 0, 'is_blocked' => 0, 'created_at' => now(), 'updated_at' => now()],
       ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_types');
    }
};
