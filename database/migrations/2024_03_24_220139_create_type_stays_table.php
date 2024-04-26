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
        Schema::create('type_stays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->string('icone')->nullable();
            $table->timestamps();
            
        });

        DB::table('type_stays')->insert([
            ['name' => 'Nuit', 'is_deleted' => false, 'is_blocked' => false, 'icone' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Matin', 'is_deleted' => false, 'is_blocked' => false, 'icone' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Soir', 'is_deleted' => false, 'is_blocked' => false, 'icone' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jour', 'is_deleted' => false, 'is_blocked' => false, 'icone' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Semaine', 'is_deleted' => false, 'is_blocked' => false, 'icone' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mois', 'is_deleted' => false, 'is_blocked' => false, 'icone' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Année', 'is_deleted' => false, 'is_blocked' => false, 'icone' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Week-end', 'is_deleted' => false, 'is_blocked' => false, 'icone' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Long séjour', 'is_deleted' => false, 'is_blocked' => false, 'icone' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Court séjour', 'is_deleted' => false, 'is_blocked' => false, 'icone' => NULL, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_stays');
    }
};
