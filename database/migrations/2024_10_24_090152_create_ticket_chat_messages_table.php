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
        Schema::create('ticket_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_chat_id')->nullable()->constrained('ticket_chats')->onDelete('cascade');
            $table->foreignId('receiver_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->text('content')->nullable();
            $table->boolean('is_read')->default(false)->nullable();
            $table->boolean('is_deleted')->default(false)->nullable();
            $table->enum('sender_type', ['administrateur', 'utilisateur'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_chat_messages');
    }
};
