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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icone')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->boolean('is_verified');
            $table->timestamps();

        });

        DB::table('equipment')->insert([
            // Salon
            ['name' => 'Canapé', 'is_verified' => true],
            ['name' => 'Fauteuil', 'is_verified' => true],
            ['name' => 'Table basse', 'is_verified' => true],
            ['name' => 'Télévision', 'is_verified' => true],
            ['name' => 'Lampe de salon', 'is_verified' => true],
            ['name' => 'Table d\'appoint', 'is_verified' => true],
            ['name' => 'Étagère', 'is_verified' => true],
            ['name' => 'Buffet', 'is_verified' => true],
            ['name' => 'Pouf', 'is_verified' => true],
            ['name' => 'Tapis', 'is_verified' => true],

            // Chambre
            ['name' => 'Lit double', 'is_verified' => true],
            ['name' => 'Lit simple', 'is_verified' => true],
            ['name' => 'Table de chevet', 'is_verified' => true],
            ['name' => 'Armoire', 'is_verified' => true],
            ['name' => 'Commode', 'is_verified' => true],
            ['name' => 'Bureau', 'is_verified' => true],
            ['name' => 'Chaise de bureau', 'is_verified' => true],
            ['name' => 'Lampe de chevet', 'is_verified' => true],
            ['name' => 'Miroir', 'is_verified' => true],
            ['name' => 'Cintres', 'is_verified' => true],

            // Cuisine
            ['name' => 'Table', 'is_verified' => true],
            ['name' => 'Chaise', 'is_verified' => true],
            ['name' => 'Réfrigérateur', 'is_verified' => true],
            ['name' => 'Four', 'is_verified' => true],
            ['name' => 'Micro-ondes', 'is_verified' => true],
            ['name' => 'Plaque de cuisson', 'is_verified' => true],
            ['name' => 'Hotte aspirante', 'is_verified' => true],
            ['name' => 'Évier', 'is_verified' => true],
            ['name' => 'Lave-vaisselle', 'is_verified' => true],
            ['name' => 'Vaisselle', 'is_verified' => true],

            // Salle de bain
            ['name' => 'Douche', 'is_verified' => true],
            ['name' => 'Baignoire', 'is_verified' => true],
            ['name' => 'Lavabo', 'is_verified' => true],
            ['name' => 'Miroir', 'is_verified' => true],
            ['name' => 'Toilettes', 'is_verified' => true],
            ['name' => 'Étagère de rangement', 'is_verified' => true],
            ['name' => 'Sèche-serviettes', 'is_verified' => true],
            ['name' => 'Porte-serviettes', 'is_verified' => true],
            ['name' => 'Poubelle', 'is_verified' => true],
            ['name' => 'Tapis de bain', 'is_verified' => true],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
