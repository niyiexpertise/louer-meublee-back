<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }
        if ($teams && empty($columnNames['team_foreign_key'] ?? null)) {
            throw new \Exception('Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id'); // permission id
            $table->string('name');       // For MySQL 8.0 use string('name', 125);
            $table->string('guard_name'); // For MySQL 8.0 use string('guard_name', 125);
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });
        DB::table($tableNames['permissions'])->insert([
            ['name' => 'manageHousingType', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manageType', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manageRole', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manageCriteria', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manageEquipment', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manageHousing', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manageUser', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'managePermission', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manageReview', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manageLanguage', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manageCategory', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'managePreference', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'managePropertyType', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manageUsers', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manageDocument', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manageVerificationDocument', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manageCommission', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manageLogement', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
        ]);
        

        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames) {
            $table->bigIncrements('id'); // role id
            if ($teams || config('permission.testing')) { // permission.testing is a fix for sqlite testing
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name');       // For MySQL 8.0 use string('name', 125);
            $table->string('guard_name'); // For MySQL 8.0 use string('guard_name', 125);
            $table->timestamps();
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });
        // Insertion des rôles
   DB::table($tableNames['roles'])->insert([
    ['name' => 'traveler', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'hote', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'admin', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'superAdmin', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
   ]);


        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $table->unsignedBigInteger($pivotPermission);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            }

        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $table->unsignedBigInteger($pivotRole);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            } else {
                $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            }
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }



};
