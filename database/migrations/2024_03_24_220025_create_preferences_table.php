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
        Schema::create('preferences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icone')->nullable();
            $table->timestamps();
            $table->boolean('is_verified');
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
        });

        DB::table('preferences')->insert([
            ['name' => 'Au bord de la plage', 'icone' => null, 'is_deleted' => false,'is_verified' => true, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Vers la forêt', 'icone' => null, 'is_deleted' => false,'is_verified' => true, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Au bord du goudron', 'icone' => null, 'is_deleted' => false,'is_verified' => true, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'En centre-ville', 'icone' => null, 'is_deleted' => false,'is_verified' => true, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dans un quartier calme', 'icone' => null, 'is_deleted' => false,'is_verified' => true, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Proche des transports en commun', 'icone' => null, 'is_deleted' => false,'is_verified' => true, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Près des commerces', 'icone' => null, 'is_deleted' => false,'is_verified' => true, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'À proximité des restaurants', 'icone' => null, 'is_deleted' => false,'is_verified' => true, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Avec vue sur la mer', 'icone' => null, 'is_deleted' => false,'is_verified' => true, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'En montagne', 'icone' => null, 'is_deleted' => false,'is_verified' => true, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preferences');
    }
};
