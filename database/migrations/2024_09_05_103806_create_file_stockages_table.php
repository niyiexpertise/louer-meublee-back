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
        Schema::create('file_stockages', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique()->nullable();
            $table->text('access_key_id')->nullable();
            $table->text('secret_access_key')->nullable();
            $table->text('default_region')->nullable();
            $table->text('bucket')->nullable();
            $table->text('url')->nullable();
            $table->boolean('is_actif')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_stockages');
    }
};
