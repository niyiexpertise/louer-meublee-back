<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Right;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        
        $role = Role::firstOrCreate(['name' => 'superAdmin']);

        
        $user = new User();
        $user->lastname = 'ADMIN'; 
        $user->firstname = 'Super'; 
        $user->password = bcrypt('Pasword2000!');
        $user->telephone = '123456789';
        $user->code_pays = 'FR'; 
        $user->email = 'superadmin@example.com'; 
        $user->country = 'France';
        $user->city = 'Paris'; 
        $user->address = '123 SuperAdmin Street';
        $user->sexe = 'Masculin';


        $user->save();

        $user->assignRole($role);

        $right = Right::where('name', 'superAdmin')->first();
        if ($right) {
            DB::table('user_rights')->insert([
                'user_id' => $user->id,
                'right_id' => $right->id,
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
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
