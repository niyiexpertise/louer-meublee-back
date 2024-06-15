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
        Schema::create('verification_statut_partenaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vpdocument_id')->references('id')->on('verification_document_partenaires')->onDelete('cascade');
            $table->boolean('status')->default(false);
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
        Schema::dropIfExists('verification_statut_partenaires');
    }
};
