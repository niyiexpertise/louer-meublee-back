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
        Schema::table('payements', function (Blueprint $table) {
            $table->string('country')->nullable();
            $table->boolean('is_confirmed');
            $table->boolean('is_canceled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payements', function (Blueprint $table) {
            //
        });
    }
};
