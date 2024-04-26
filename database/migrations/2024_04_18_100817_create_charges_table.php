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
        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icone')->nullable();
            $table->timestamps();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
        });
        DB::table('charges')->insert([
            ['name' => 'Électricité', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Eau', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gaz', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Internet', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Assainissement', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ménage', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Taxe de séjour', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Loyer', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Chauffage', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Electricité de la cuisine', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Volet roulant', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Volet fixe', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cuisine équipée', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Salle de bain avec douche', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Salle de bain avec baignoire', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Parking', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jardin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Climatisation', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Alarme', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Volet roulant électrique', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Volet fixe électrique', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cuisine équipée électrique', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Salle de bain avec douche électrique', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Salle de bain avec baignoire électrique', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Parking électrique', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jardin électrique', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Climatisation électrique', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Alarme électrique', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charges');
    }
};

