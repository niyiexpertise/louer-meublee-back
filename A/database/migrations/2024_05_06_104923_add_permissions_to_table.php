<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class AddPermissionsToTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissions = [];
        $guardName = 'web'; // Vous pouvez ajuster cela selon vos besoins

        // Obtenir toutes les routes définies dans votre application
        $allRoutes = Route::getRoutes();

        // Pour chaque route, créer une permission
        foreach ($allRoutes as $route) {
            $routeName = $route->getName();

            // Ignorer les routes sans nom
            if ($routeName) {
                $permissions[] = [
                    'name' =>'Manage'. $routeName,
                    'guard_name' => "web",
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insérer les permissions dans la table
        DB::table('permissions')->insert($permissions);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('permissions')->where('guard_name', 'web')->delete();
    }
}
