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
        Schema::table('settings', function (Blueprint $table) {
            $table->float('commission_partenaire_defaut')->nullable();
            $table->float('reduction_partenaire_defaut')->nullable();
            $table->integer('number_of_reservation_partenaire_defaut')->nullable();
            $table->float('commission_hote_defaut')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            
        });
    }
};
