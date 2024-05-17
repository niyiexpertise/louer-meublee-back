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
        Schema::create('portfeuille_transactions', function (Blueprint $table) {
            $table->id();
            $table->boolean('debit');
            $table->boolean('credit');
            $table->double('amount');
            $table->string('motif');
            $table->foreignId('reservation_id')->nullable()->onDelete('cascade');
            $table->string('payment_method')->nullable();
            $table->foreignId('portfeuille_id')->constrained()->onDelete('cascade');
            $table->string('id_transaction')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfeuille_transactions');
    }
};
