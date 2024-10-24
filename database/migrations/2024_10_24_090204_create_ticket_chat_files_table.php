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
        Schema::create('ticket_chat_files', function (Blueprint $table) {
            $table->id();
            $table->string('location')->nullable();
            $table->string('extension')->nullable();
            $table->foreignId('ticket_chat_message_id')->nullable()->constrained('ticket_chat_messages')->onDelete('cascade');
            $table->boolean('is_deleted')->default(false)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_chat_files');
    }
};
