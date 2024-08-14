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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->text('content')->nullable();
            $table->foreignId('receiver_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('done_by_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('chat_id')->nullable()->constrained('chats')->onDelete('cascade');
            $table->boolean('is_read')->default(false)->nullable();
            $table->string('filecode')->nullable()->unique();
            $table->boolean('is_valid')->nullable();
            $table->boolean('is_deleted_by_receiver')->default(false)->nullable();
            $table->boolean('is_deleted_by_sender')->default(false)->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
