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
        Schema::create('document_type_demandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreignId('type_demande_id')->references('id')->on('type_demandes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_type_demandes');
    }
};
