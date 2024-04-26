<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('housing_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icone')->nullable();
            $table->text('description');
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
        });

        // Inserting data
        DB::table('housing_types')->insert([
            ['name' => 'Chambre partagée', 'description' => 'Description de la chambre partagée'],
            ['name' => 'Logement entier', 'description' => 'Description du logement entier'],
            ['name' => 'type logement1', 'description' => 'Description du type logement1'],
            ['name' => 'type logement2', 'description' => 'Description du type logement2'],
            ['name' => 'type logement3', 'description' => 'Description du type logement3'],
            ['name' => 'type logement4', 'description' => 'Description du type logement4'],
            ['name' => 'type logement5', 'description' => 'Description du type logement5'],
            ['name' => 'type logement6', 'description' => 'Description du type logement6'],
            ['name' => 'type logement7', 'description' => 'Description du type logement7'],
            ['name' => 'type logement8', 'description' => 'Description du type logement8'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housing_types');
    }
};

