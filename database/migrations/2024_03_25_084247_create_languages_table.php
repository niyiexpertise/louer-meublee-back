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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icone')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
        });

        // Insertion de 30 langues
        $languages = [
            'English',
            'French',
            'Spanish',
            'German',
            'Italian',
            'Chinese',
            'Japanese',
            'Arabic',
            'Portuguese',
            'Russian',
            'Korean',
            'Dutch',
            'Swedish',
            'Danish',
            'Norwegian',
            'Finnish',
            'Greek',
            'Turkish',
            'Polish',
            'Hungarian',
            'Czech',
            'Romanian',
            'Thai',
            'Indonesian',
            'Malay',
            'Hindi',
            'Bengali',
            'Urdu',
            'Swahili',
            'Vietnamese',
        ];

        foreach ($languages as $language) {
            DB::table('languages')->insert([
                'name' => $language,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
