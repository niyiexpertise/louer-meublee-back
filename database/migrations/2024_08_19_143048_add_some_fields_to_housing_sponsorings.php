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
       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression des triggers
        DB::unprepared('DROP TRIGGER IF EXISTS check_status_before_update');
        DB::unprepared('DROP TRIGGER IF EXISTS check_status_before_insert');
    }
};
