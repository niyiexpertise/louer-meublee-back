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
        Schema::create('equipment_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreignId('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
            $table->timestamps();
        });

        DB::table('equipment_categories')->insert([
            // Catégorie Salon
            ['category_id' => 1, 'equipment_id' => 1],
            ['category_id' => 1, 'equipment_id' => 2],
            ['category_id' => 1, 'equipment_id' => 3],
            ['category_id' => 1, 'equipment_id' => 4],
            ['category_id' => 1, 'equipment_id' => 5],
            ['category_id' => 1, 'equipment_id' => 6],
            ['category_id' => 1, 'equipment_id' => 7],
            ['category_id' => 1, 'equipment_id' => 8],
            ['category_id' => 1, 'equipment_id' => 9],
            ['category_id' => 1, 'equipment_id' => 10],
        
            // Catégorie Chambre
            ['category_id' => 2, 'equipment_id' => 11],
            ['category_id' => 2, 'equipment_id' => 12],
            ['category_id' => 2, 'equipment_id' => 13],
            ['category_id' => 2, 'equipment_id' => 14],
            ['category_id' => 2, 'equipment_id' => 15],
            ['category_id' => 2, 'equipment_id' => 16],
            ['category_id' => 2, 'equipment_id' => 17],
            ['category_id' => 2, 'equipment_id' => 18],
            ['category_id' => 2, 'equipment_id' => 19],
            ['category_id' => 2, 'equipment_id' => 20],
        
            // Catégorie Cuisine
            ['category_id' => 3, 'equipment_id' => 21],
            ['category_id' => 3, 'equipment_id' => 22],
            ['category_id' => 3, 'equipment_id' => 23],
            ['category_id' => 3, 'equipment_id' => 24],
            ['category_id' => 3, 'equipment_id' => 25],
            ['category_id' => 3, 'equipment_id' => 26],
            ['category_id' => 3, 'equipment_id' => 27],
            ['category_id' => 3, 'equipment_id' => 28],
            ['category_id' => 3, 'equipment_id' => 29],
            ['category_id' => 3, 'equipment_id' => 30],
        
            // Catégorie Salle de bain
            ['category_id' => 4, 'equipment_id' => 31],
            ['category_id' => 4, 'equipment_id' => 32],
            ['category_id' => 4, 'equipment_id' => 33],
            ['category_id' => 4, 'equipment_id' => 34],
            ['category_id' => 4, 'equipment_id' => 35],
            ['category_id' => 4, 'equipment_id' => 36],
            ['category_id' => 4, 'equipment_id' => 37],
            ['category_id' => 4, 'equipment_id' => 38],
            ['category_id' => 4, 'equipment_id' => 39],
            ['category_id' => 4, 'equipment_id' => 40],
        ]);
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_categories');
    }
};
