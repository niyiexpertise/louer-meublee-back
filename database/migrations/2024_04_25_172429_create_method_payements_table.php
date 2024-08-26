<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('method_payements', function (Blueprint $table) {
            $table->id();
            $table->string('icone')->nullable();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('method_payements')->insert([
            ['name' => 'Momo', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Flooz', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Portefeuille', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Visa', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MasterCard', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PayPal', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Stripe', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bitcoin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Apple Pay', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Google Pay', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Western Union', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bank Transfer', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cash', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_payements');
    }
};
