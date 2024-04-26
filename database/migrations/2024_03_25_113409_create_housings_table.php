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
        Schema::create('housings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_type_id')->references('id')->on('housing_types')->onDelete('cascade');
            $table->foreignId('property_type_id')->references('id')->on('property_types')->onDelete('cascade');
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->text('description');
            $table->integer('number_of_bed');
            $table->integer('number_of_traveller');
            $table->double('sit_geo_lat');
            $table->double('sit_geo_lng');
            $table->string('country');
            $table->string('address');
            $table->string('city');
            $table->string('department');
            $table->boolean('is_camera')->nullable();
            $table->boolean('is_accepted_animal')->nullable();
            $table->boolean('is_animal_exist');
            $table->boolean('is_disponible');
            $table->text('interior_regulation')->nullable();
            $table->string('telephone');
            $table->string('code_pays');
            $table->string('status');
            $table->string('arrived_independently');
            $table->string('icone')->nullable();
            $table->boolean('is_instant_reservation');
            $table->integer('maximum_duration');
            $table->integer('minimum_duration');
            $table->integer('time_before_reservation')->nullable();
            $table->text('cancelation_condition')->nullable();
            $table->text('departure_instruction')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housings');
    }
};
