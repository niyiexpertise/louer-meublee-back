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
            //--
            $table->decimal('montant_maximum_recharge', 15, 2)->nullable();
            $table->decimal('montant_minimum_recharge', 15, 2)->nullable();
            $table->decimal('montant_minimum_retrait', 15, 2)->nullable();
            $table->decimal('montant_maximum_retrait', 15, 2)->nullable();
            $table->decimal('montant_minimum_solde_retrait', 15, 2)->nullable();
            //--
            $table->float('commission_partenaire_defaut')->nullable();
            $table->float('reduction_partenaire_defaut')->nullable();
            $table->integer('number_of_reservation_partenaire_defaut')->nullable();
            $table->float('commission_hote_defaut')->nullable();
            //--
            $table->integer('max_night_number');
            $table->float('max_value_reduction');
            $table->integer('max_number_of_reservation');
            $table->float('max_value_promotion');
            //--
            $table->float('commission_seuil_hote_partenaire')->nullable();
            //--
            $table->integer('min_housing_file')->nullable();

            $table->timestamps();
        });

        // Insert a default row
        DB::table('settings')->insert([
            'pagination_logement_acceuil' => 10,
            'condition_tranche_paiement' => 'Conditions de tranche de paiement par défaut',
            'condition_prix_logement' => 'Conditions de prix de logement par défaut',
            'condition_sponsoring_logement' => 'Conditions de sponsoring par défaut',
            'contact_email' => 'example@domain.com',
            'contact_telephone' => '0123456789',
            'facebook_url' => 'https://facebook.com/example',
            'twitter_url' => 'https://twitter.com/example',
            'instagram_url' => 'https://instagram.com/example',
            'linkedin_url' => 'https://linkedin.com/in/example',
            'logo' => 'https://example.com/logo.png',
            'adresse_serveur_fichier' => 'https://example.com/files',
            'app_mode' => 'production',
            'montant_maximum_recharge' => 10000.00,
            'montant_minimum_recharge' => 10.00,
            'montant_minimum_retrait' => 5.00,
            'montant_maximum_retrait' => 1000.00,
            'montant_minimum_solde_retrait' => 10.00,
            'commission_partenaire_defaut' => 5.0,
            'reduction_partenaire_defaut' => 2.0,
            'number_of_reservation_partenaire_defaut' => 3,
            'commission_hote_defaut' => 7.0,
            'max_night_number' => 30,
            'max_value_reduction' => 50.0,
            'max_number_of_reservation' => 10,
            'max_value_promotion' => 100.0,
            'commission_seuil_hote_partenaire' => 3.0,
            'min_housing_file' => 5,
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
