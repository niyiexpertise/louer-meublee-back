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

            $table->integer('pagination_logement_acceuil')->nullable();
            $table->text('condition_tranche_paiement')->nullable();
            $table->text('condition_prix_logement')->nullable();
            $table->text('condition_sponsoring_logement')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_telephone')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('logo')->nullable();
            $table->string('adresse_serveur_fichier')->nullable();
            $table->string('app_mode')->nullable();

            $table->timestamps();
        });

        // Insert a default row
        DB::table('settings')->insert([
            'pagination_logement_acceuil' => 10,
            'condition_tranche_paiement' => '',
            'condition_prix_logement' => '',
            'condition_sponsoring_logement' => '',
            'contact_email' => 'example@domain.com',
            'contact_telephone' => '',
            'facebook_url' => '',
            'twitter_url' => '',
            'instagram_url' => '',
            'linkedin_url' => '',
            'logo' => '',
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
