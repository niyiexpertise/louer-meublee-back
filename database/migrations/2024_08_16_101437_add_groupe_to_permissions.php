<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddGroupeToPermissions extends Migration
{
    public function up()
    {
        // Ajouter la colonne 'groupe' à la table 'permissions'
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('groupe')->nullable();
        });

    }

    public function down()
    {

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('groupe');
        });
    }
}

