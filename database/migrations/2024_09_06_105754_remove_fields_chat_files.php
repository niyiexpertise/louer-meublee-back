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
        Schema::table('chat_files', function (Blueprint $table) {
            // Suppression des colonnes 'type' et 'filename'
            $table->dropColumn(['type', 'filename']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_files', function (Blueprint $table) {
            // Ajout des colonnes 'type' et 'filename' si la migration est annulée
            $table->string('type')->nullable();
            $table->string('filename')->nullable();
        });
    }
};
