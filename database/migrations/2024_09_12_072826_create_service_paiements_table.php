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
        Schema::create('service_paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('method_payement_id')
                  ->nullable()
                  ->constrained('method_payements')
                  ->onDelete('cascade');
            $table->string('type')->nullable();
            $table->string('public_key')->nullable();
            $table->string('private_key')->nullable();
            $table->string('secret_key')->nullable();
            $table->text('description_service')->nullable();
            $table->text('description_type')->nullable();
            $table->decimal('fees', 10, 2)->nullable();
            $table->timestamp('date_activation')->nullable();
            $table->boolean('is_actif')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_paiements');
    }
};
