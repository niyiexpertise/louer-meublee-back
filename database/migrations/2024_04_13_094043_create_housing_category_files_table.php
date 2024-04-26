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
        Schema::create('housing_category_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('housing_id')->references('id')->on('housings')->onDelete('cascade');
                $table->foreignId('category_id')->references('id')->on('categories')->onDelete('cascade');
                $table->foreignId('file_id')->references('id')->on('files')->onDelete('cascade');
                $table->integer('number');
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housing_category_files');
    }
};
