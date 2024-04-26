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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icone')->nullable();
            $table->boolean('is_verified');
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
        });

        // Insertion des données
        DB::table('categories')->insert([
            ['name' => 'Salon','is_verified' => 1],
            ['name' => 'Chambre','is_verified' => 1],
            ['name' => 'Cuisine','is_verified' => 1],
            ['name' => 'Salle de bain','is_verified' => 1],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
