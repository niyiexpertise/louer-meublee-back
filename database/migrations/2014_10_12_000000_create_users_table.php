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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('lastname');
            $table->string('firstname');
            $table->string('email')->unique();
            $table->string('telephone');
            $table->string('country');
            $table->string('file_profil')->nullable(); // Chemin vers l'image de profil
            $table->string('piece_of_identity')->nullable(); // Chemin vers la pièce d'identité
            $table->string('city');
            $table->text('address');
            $table->string('sexe');
            $table->string('postal_code')->nullable();
            $table->string('icone')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
