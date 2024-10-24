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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            $table->integer('pagination_logement_acceuil')->default(10)->nullable();
            $table->text('condition_tranche_paiement')->default('condition')->nullable();
            $table->text('condition_prix_logement')->default('condition')->nullable();
            $table->text('condition_sponsoring_logement')->default('condition')->nullable();
            $table->string('contact_email')->default('zakiyoubababodi@gmail.com')->nullable();
            $table->string('contact_telephone')->default('97546933')->nullable();
            $table->string('facebook_url')->default('url')->nullable();
            $table->string('twitter_url')->default('url')->nullable();
            $table->string('instagram_url')->default('url')->nullable();
            $table->string('linkedin_url')->default('url')->nullable();
            $table->string('logo')->default('logo')->nullable();
            $table->string('adresse_serveur_fichier')->default('https://example.com/files')->nullable();
            $table->string('app_mode')->default('developpement')->nullable();

            $table->timestamps();
        });

        // Insert a default row
        DB::table('settings')->insert([
            'pagination_logement_acceuil' => 10,
            'adresse_serveur_fichier' => 'https://example.com/files',
            'app_mode' => 'production',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
