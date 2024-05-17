<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('housings', function (Blueprint $table) {
            $table->boolean('is_actif');
            $table->boolean('is_destroy');
        });
    }


    public function down(): void
    {
        Schema::table('housings', function (Blueprint $table) {

        });
    }
};
