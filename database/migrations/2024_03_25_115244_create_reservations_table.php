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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('housing_id')->references('id')->on('housings')->onDelete('cascade');
            $table->date('date_of_reservation');
            $table->date('date_of_starting');
            $table->date('date_of_end');
            $table->integer('number_of_adult');
            $table->integer('number_of_child');
            $table->integer('number_of_domestical_animal');
            $table->integer('number_of_baby');
            $table->string('icone')->nullable();
            $table->timestamps();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
            

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
