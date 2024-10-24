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
        Schema::create('ticket_chats', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->boolean('is_read')->default(false)->nullable();
            $table->boolean('is_deleted')->default(false)->nullable();
            $table->boolean('is_open')->default(true)->nullable();
            $table->foreignId('is_closed_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('is_closed_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_chats');
    }
};
