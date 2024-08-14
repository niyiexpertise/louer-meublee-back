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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_to')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('sent_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->boolean('is_read')->default(false);
            $table->text('last_message')->nullable();
            $table->string('model_type_concerned')->nullable();
            $table->bigInteger('model_id')->nullable();
            $table->boolean('is_deleted')->default(false)->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
