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
        Schema::table('housings', function (Blueprint $table) {
            // Rendre toutes les colonnes non-nullables, nullable sauf les clés étrangères et 'id'
            $table->text('description')->nullable()->change();
            $table->integer('number_of_bed')->nullable()->change();
            $table->integer('number_of_traveller')->nullable()->change();
            $table->double('sit_geo_lat')->nullable()->change();
            $table->double('sit_geo_lng')->nullable()->change();
            $table->string('country')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('department')->nullable()->change();
            $table->boolean('is_animal_exist')->nullable()->change();
            $table->string('telephone')->nullable()->change();
            $table->string('code_pays')->nullable()->change();
            $table->string('status')->nullable()->change();
            $table->string('arrived_independently')->nullable()->change();
            $table->boolean('is_instant_reservation')->nullable()->change();
            $table->integer('minimum_duration')->nullable()->change();
            $table->boolean('is_accept_arm')->nullable()->change();
            $table->boolean('is_accept_smoking')->nullable()->change();
            $table->boolean('is_accept_chill')->nullable()->change();
            $table->boolean('is_accept_noise')->nullable()->change();
            $table->boolean('is_updated')->nullable()->change();
            $table->boolean('is_finished')->nullable()->change();
            $table->double('price')->nullable()->change();
            $table->float('surface')->nullable()->change();
            $table->boolean('is_actif')->nullable()->change();
            $table->boolean('is_destroy')->nullable()->change();
            $table->boolean('is_accept_alccol')->nullable()->change();
            $table->integer('delai_partiel_remboursement')->nullable()->change();
            $table->integer('delai_integral_remboursement')->nullable()->change();
            $table->float('valeur_integral_remboursement')->nullable()->change();
            $table->float('valeur_partiel_remboursement')->nullable()->change();
            $table->boolean('is_accept_anulation')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housings', function (Blueprint $table) {
            // Revenir aux colonnes non-nullables
            $table->text('description')->nullable(false)->change();
            $table->integer('number_of_bed')->nullable(false)->change();
            $table->integer('number_of_traveller')->nullable(false)->change();
            $table->double('sit_geo_lat')->nullable(false)->change();
            $table->double('sit_geo_lng')->nullable(false)->change();
            $table->string('country')->nullable(false)->change();
            $table->string('address')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->string('department')->nullable(false)->change();
            $table->boolean('is_animal_exist')->nullable(false)->change();
            $table->string('telephone')->nullable(false)->change();
            $table->string('code_pays')->nullable(false)->change();
            $table->string('status')->nullable(false)->change();
            $table->string('arrived_independently')->nullable(false)->change();
            $table->boolean('is_instant_reservation')->nullable(false)->change();
            $table->integer('minimum_duration')->nullable(false)->change();
            $table->boolean('is_accept_arm')->nullable(false)->change();
            $table->boolean('is_accept_smoking')->nullable(false)->change();
            $table->boolean('is_accept_chill')->nullable(false)->change();
            $table->boolean('is_accept_noise')->nullable(false)->change();
            $table->boolean('is_updated')->nullable(false)->change();
            $table->boolean('is_finished')->nullable(false)->change();
            $table->double('price')->nullable(false)->change();
            $table->float('surface')->nullable(false)->change();
            $table->boolean('is_actif')->nullable(false)->change();
            $table->boolean('is_destroy')->nullable(false)->change();
            $table->boolean('is_accept_alccol')->nullable(false)->change();
            $table->integer('delai_partiel_remboursement')->nullable(false)->change();
            $table->integer('delai_integral_remboursement')->nullable(false)->change();
            $table->float('valeur_integral_remboursement')->nullable(false)->change();
            $table->float('valeur_partiel_remboursement')->nullable(false)->change();
            $table->boolean('is_accept_anulation')->nullable(false)->change();
        });
    }
};
