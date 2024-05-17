<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
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
        $guardName = 'web';
    
        $allRoutes = Route::getRoutes();
    
        foreach ($allRoutes as $route) {
            $routeName = $route->getName();
            if ($routeName) {
                DB::table('permissions')->insert([
                    'name' => 'Manage' . $routeName,
                    'guard_name' => $guardName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $middlewares = $route->gatherMiddleware();
                foreach ($middlewares as $middleware) {
                    if (strpos($middleware, 'role_or_permission') !== false && strpos($middleware, 'admin') !== false) {
                        
    
                        $role = Role::where('name', 'admin')->first();
                        if ($role) {
                            $permission = Permission::where('name', 'Manage' . $routeName)->first();
                            if ($permission) {
                                $role->givePermissionTo($permission);
                            }
                        }
                    }
                }
            }
        }
    
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
