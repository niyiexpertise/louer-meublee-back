<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Exception;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use validationException;
class AuthController extends Controller
{

/**
 * @OA\Post(
 *     path="/api/users/assignPermToRole/{role}/{permission}",
 *     summary="ajouter une permission à un rôle",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="role",
 *         in="path",
 *         description="ID of the role ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 * @OA\Parameter(
 *         name="permission",
 *         in="path",
 *         description="ID of the permission ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="permission assigned to role sucessfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="permission assigned to role sucessfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="permission not assigned to role",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="permission not assigned to role")
 *         )
 *     )
 * )
 */

    //ajouter une permission à un rôle
    public function assignPermToRole(Request $request, $r, $p){
        // try{

        // }catch (Exception $e){
        //     return response()->json($e);
        // }
        try{
                $role = Role::find($r);
                $permission = Permission::find($p);

                if (!$role) {
                    return response()->json('role not found');
                }

                if (!$permission) {
                    return response()->json('permission not found');
                }

                if($role->hasPermissionTo($permission->name)){
                    return response()->json([
                        'message' => 'Cette permission a déjà été assigné à ce role'
                    ]);
                }

                
                // $role->givePermissionTo($permission);

                

                if($permission->assignRole($role)){
                    return response()->json([
                        'message' => 'la permission '.$permission->name.' a ete accorde a '.$role->name,
                        'permission' => $permission,
                        'role' => $role
                    ]);
                }
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

    /**
 * @OA\Post(
 *     path="/api/users/RevokePermToRole/{role}/{permission}",
 *     summary="retirer une permission à un rôle",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="role",
 *         in="path",
 *         description="ID of the role ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 * @OA\Parameter(
 *         name="permission",
 *         in="path",
 *         description="ID of the permission",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="permission revoke to role successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="permission revoke to role successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="permission don't revoke to role ",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="permission don't revoke to role ")
 *         )
 *     )
 * )
 */

    //retirer la permission à un rôle
    public function RevokePermToRole(Request $request, $r, $p){
            try{
                $role = Role::find($r);
                $permission = Permission::find($p);

                if (!$role) {
                    return response()->json('role not found');
                }

                if (!$permission) {
                    return response()->json('permission not found');
                }

                if(!$role->hasPermissionTo($permission->name)){
                    return response()->json([
                        'message' => 'Ce role n\'a pas la permission qu\'on veut lui retirer'
                    ]);
                }
            // $role->revokePermissionTo($permission);
            // $permission->removeRole($role)
            if($role->revokePermissionTo($permission)){
                return response()->json([
                    'message' => 'la permission '.$permission->firstname.' a ete retire a '.$role->firstname,
                    'permission' => $permission,
                    'role' => $role
                ]);
            }
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

/**
 * @OA\Get(
 *     path="/api/users/getUserRoles/{id}",
 *     summary="Récupérer la liste des rôle assigner a un utilisateur",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the user ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="list taked succesfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="list taked succesfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="list don't taked ",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="list don't taked ")
 *         )
 *     )
 * )
 */

    //Récupérer la liste des rôle assigner a un utilisateur
    public function getUserRoles($id){
        try{

            $users = User::find($id);
            if (!$users) {
                return response()->json('user not found');
            }

            // $permissionNames = $users->getPermissionNames();
            $roles = $users->getRoleNames();
            if(!$roles){
                    return response()->json([
                        'message' => 'Role not found'
                    ]);
            }
            return response()->json([
                'data' => $roles
            ]);
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

       /**
 * @OA\Post(
 *     path="/api/users/assignRoleToUser/{id}/{role}",
 *     summary="assigner un rôle à un utilisateur",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the user ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 * @OA\Parameter(
 *         name="role",
 *         in="path",
 *         description="ID of the role ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="role assigned successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="role assigned successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="role not assigned",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="role not assigned")
 *         )
 *     )
 * )
 */

    //assigner un rôle à un utilisateur
    public function assignRoleToUser(Request $request,$id,$r){
        try{
                $role = Role::find($r);
               
                $user = User::find($id);
                if (!$role) {
                    return response()->json([
                        'message' => 'role not found',
                    ]);
                }

                if (!$user) {
                    return response()->json('user not found');
                }
                
                if($user->hasRole($role->name)){
                    return response()->json([
                        'message' => 'Ce rôle a déjà été assigné à cet utilisateur'
                    ]);
                }
                $user->assignRole($role);

                return response()->json([
                    'message' => 'role assigné avec success',
                    'data' => [
                        'id' => $user->id,
                        'lastname' => $user->lastname,
                        'firstname' => $user->firstname,
                        'password' => $user->password,
                        'telephone' => $user->telephone,
                        'email' => $user->email,
                        'country' => $user->country,
                        'city' => $user->city,
                        'address' => $user->address,
                        'sex' => $user->sexe,
                        'postal_code' => $user->postal_code,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                        'is_deleted' => $user->is_deleted,
                        'is_blocked' => $user->is_blocked,
                        'file_profil' => $user->file_profil,
                        
                        'role' => User::find($id)->getRoleNames()
                    ]
                ]);
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
        
    }

           /**
 * @OA\Post(
 *     path="/api/users/RevokeRoleToUser/{id}/{role}",
 *     summary="retirer un rôle à un utilisateur",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the user ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 * @OA\Parameter(
 *         name="role",
 *         in="path",
 *         description="ID of the role ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="revoke role to user",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="revoke role to user")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="role not revoked",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="role not revoked")
 *         )
 *     )
 * )
 */

    //retirer un rôle à un utilisateur
    public function RevokeRoleToUser(Request $request,$id,$r){
        try{
                $role = Role::find($r);
                $user = User::find($id);
               
                if (!$role) {
                    return response()->json([
                        'message' => 'role not found',
                    ]);
                }

                if (!$user) {
                    return response()->json('user not found');
                }
                
                if(!$user->hasRole($role->name)){
                    return response()->json([
                        'message' => 'Cet utilisateur n\'a pas le rôle que vous voulez lui retirer'
                    ]);
                }

                $user->removeRole($role);

                return response()->json([
                    'message' => 'role retire avec success',
                    'data' => [
                        'id' => $user->id,
                        'lastname' => $user->lastname,
                        'firstname' => $user->firstname,
                        'password' => $user->password,
                        'telephone' => $user->telephone,
                        'email' => $user->email,
                        'country' => $user->country,
                        'city' => $user->city,
                        'address' => $user->address,
                        'sex' => $user->sexe,
                        'postal_code' => $user->postal_code,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                        'is_deleted' => $user->is_deleted,
                        'is_blocked' => $user->is_blocked,
                        'file_profil' => $user->file_profil,
                        
                        'role' => User::find($id)->getRoleNames()
                    ]
                ]);
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

           /**
 * @OA\Post(
 *     path="/api/users/assignPermToUser/{id}/{permission}",
 *     summary="ajouter une permission à un utilisateur",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the user ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 * @OA\Parameter(
 *         name="permission",
 *         in="path",
 *         description="ID of the permission ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="permission grant to user successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="permission grant to user successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="permission not granted",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="permission not granted")
 *         )
 *     )
 * )
 */

    //ajouter une permission à un utilisateur
    public function assignPermToUser(Request $request,$id,$p){
        try{
            $permission = Permission::find($p);
            $user = User::find($id);
            
            if (!$user) {
                return response()->json('user not found');
            }

            if (!$permission) {
                return response()->json('permission not found');
            }

            if($user->hasPermissionTo($permission->name)){
                return response()->json([
                    'message' => 'Cet utilisateur a déjà la permission  que vous voulez lui assigner'
                ]);
            }

            // if($user->hasDirectPermission($permission->name)){
            //     return response()->json([
            //         'message' => 'Cet utilisateur a déjà la permission que vous voulez lui assigner'
            //     ]);
            // }

            
            
            // hasDirectPermission('edit articles')
            $permissionsDirect = $user->getDirectPermissions();
            $permissionsRole = $user->getPermissionsViaRoles();
            $user->givePermissionTo($permission);
            return response()->json([
                'message'=>'permission add successfully',
                'data' =>[
                        'id' => $user->id,
                        'lastname' => $user->lastname,
                        'firstname' => $user->firstname,
                        'telephone' => $user->telephone,
                        'email' => $user->email,
                        'country' => $user->country,
                        'city' => $user->city,
                        'address' => $user->address,
                        'sex' => $user->sexe,
                        'postal_code' => $user->postal_code,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                        'is_deleted' => $user->is_deleted,
                        'is_blocked' => $user->is_blocked,
                        'file_profil' => $user->file_profil,
                        
                        'permission' => [
                            'permissionDirect' => $permissionsDirect,
                            'permissionRole' => $permissionsRole
                        ]
                ]
            ]);
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

               /**
 * @OA\Post(
 *     path="/api/users/revokePermToUser/{id}/{permission}",
 *     summary="retirer une permission à un rôle",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the user ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 * @OA\Parameter(
 *         name="permission",
 *         in="path",
 *         description="ID of the permission ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="permission revoked to user successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="permission revoked to user successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="permission not revoked",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="permission not revoked")
 *         )
 *     )
 * )
 */

    //retirer une permission à un utilisateur
    public function revokePermToUser(Request $request,$id,$p){
        try{
            // $users = User::find($id);
            $permission = Permission::find($p);
            $user = User::find($id);
            if (!$user) {
                return response()->json('user not found');
            }

            if (!$permission) {
                return response()->json('permission not found');
            }

            if(!$user->hasDirectPermission($permission->name)){
                return response()->json([
                    'message' => 'Cet utilisateur n\'a pas la permission  que vous voulez lui retirer'
                ]);
            }

            $permissionsDirect = $user->getDirectPermissions();
            $permissionsRole = $user->getPermissionsViaRoles();
            $user->revokePermissionTo($permission);
            return response()->json([
                'message'=>'permission deny successfully',
                'data' =>[
                    'id' => $user->id,
                    'lastname' => $user->lastname,
                    'firstname' => $user->firstname,
                    'telephone' => $user->telephone,
                    'email' => $user->email,
                    'country' => $user->country,
                    'city' => $user->city,
                    'address' => $user->address,
                    'sex' => $user->sexe,
                    'postal_code' => $user->postal_code,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'is_deleted' => $user->is_deleted,
                    'is_blocked' => $user->is_blocked,
                    'file_profil' => $user->file_profil,
                    
                    'permission' => [
                        'permissionDirect' => $permissionsDirect,
                        'permissionRole' => $permissionsRole
                    ]
            ]
            ]);
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

    /**
 * @OA\Get(
 *     path="/api/users/getUserPerms/{id}",
 *     summary="Récupérer la liste des permissions assigner a un utilisateur",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the user ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="list of user permissions granted to the user given",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="list taked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="list not taked",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="list not taked")
 *         )
 *     )
 * )
 */
       //Récupérer la liste des permissions assigner a un utilisateur
       public function getUserPerms($id){
        try{
            $users = User::find($id);
            if (!$users) {
                return response()->json([
                    'message' => 'user not found'
                ]);
            }
            // $permissionNames = $users->getPermissionNames();
            $permissionsDirect = $users->getDirectPermissions();
            $permissionsRole = $users->getPermissionsViaRoles();

            return response()->json([
                'data' => [
                    'directPermissions' => $permissionsDirect,
                    'indirectPermissions' => $permissionsRole
                ]
            ]);
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

    /**
 * @OA\Get(
 *     path="/api/users/usersWithRole/{role}",
 *     summary="Liste des utilisateurs ayant un rôle donné",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="role",
 *         in="path",
 *         description="ID of the role ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="list of users with role given",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="list taked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="list not taked",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="list not taked")
 *         )
 *     )
 * )
 */
    //Liste des utilisateurs ayant un rôle donné
    public function usersWithRole($r){
        // try{

        // }catch (Exception $e){
        //     return response()->json($e);
        // }
        try{
            $role = Role::find($r);
            $users = User::role($role->name)->get();
            if (!$role) {
                return response()->json('role not found');
            }
            return response()->json([
                'data' => $users
            ]);
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }


        /**
 * @OA\Get(
 *     path="/api/users/usersWithRoleCount/{role}",
 *     summary="Nombre des utilisateurs ayant un rôle donné",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="role",
 *         in="path",
 *         description="ID of the role ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="number of users with role given",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="count done successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="count not done",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="count not done")
 *         )
 *     )
 * )
 */

    //Nombre des utilisateurs ayant un rôle donné
    public function usersWithRoleCount($r){
        // try{

        // }catch (Exception $e){
        //     return response()->json($e);
        // }
        try{
            $role = Role::find($r);
            $n = User::role($role->name)->count();

            if (!$role) {
                return response()->json('role not found');
            }
            return response()->json([
                'data' => $n
            ]);
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

        /**
 * @OA\Get(
 *     path="/api/users/usersWithPerm/{permission}",
 *     summary="Liste des utilisateurs ayant une permission donné",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="permission",
 *         in="path",
 *         description="ID of the permission ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="list of users with permissions given",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="list taked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="list not taked",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="list not taked")
 *         )
 *     )
 * )
 */

    //Liste des utilisateurs ayant une permission donné
    public function usersWithPerm($p){
        // try{

        // }catch (Exception $e){
        //     return response()->json($e);
        // }
        try{
            $permission = permission::find($p);
            $users = User::permission($permission->name)->get();
            if (!$permission) {
                return response()->json('permission not found');
            }
            return response()->json([
                'data' => $users
            ]);
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

            /**
 * @OA\Get(
 *     path="/api/users/usersWithPermCount/{permission}",
 *     summary="nombre des utilisateurs ayant une permission donné",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="permission",
 *         in="path",
 *         description="ID of the permission ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="number of users without permissions given",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="count done successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="count not done ",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="count not done ")
 *         )
 *     )
 * )
 */
    //nombre des utilisateurs ayant une permission donné
    public function usersWithPermCount($p){
        // try{

        // }catch (Exception $e){
        //     return response()->json($e);
        // }
        try{
            $permission = permission::find($p);
            if (!$permission) {
                return response()->json('permission not found');
            }
            $n = User::permission($permission->name)->count();
            return response()->json([
                'data' => $n
            ]);
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

            /**
 * @OA\Get(
 *     path="/api/users/usersWithoutRole/{role}",
 *     summary="Liste des utilisateurs n'ayant pas un rôle donné",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="role",
 *         in="path",
 *         description="ID of the role ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="list of users without role given",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="list taked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="list not taked",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="list not taked")
 *         )
 *     )
 * )
 */
    //Liste des utilisateurs n'ayant pas un rôle donné
    public function usersWithoutRole($r){
        // try{

        // }catch (Exception $e){
        //     return response()->json($e);
        // }
        try{
            $role = Role::find($r);
            $users = User::withoutRole($role->name)->get();
            if (!$role) {
                return response()->json('role not found');
            }
            return response()->json([
                'data' => $users
            ]);
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

            /**
 * @OA\Get(
 *     path="/api/users/usersWithoutRoleCount/{role}",
 *     summary="Nombre des utilisateurs n'ayant pas un rôle donné",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="role",
 *         in="path",
 *         description="ID of the role ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="number of users without role given",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="list of permissions'roles")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description=" not count",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example=" not count")
 *         )
 *     )
 * )
 */
    //Nombre des utilisateurs n'ayant pas un rôle donné
    public function usersWithoutRoleCount($r){
        // try{

        // }catch (Exception $e){
        //     return response()->json($e);
        // }
        try{
            $role = Role::find($r);
            if (!$role) {
                return response()->json('role not found');
            }
            $n = User::withoutRole($role->name)->count();
            return response()->json([
                'data' => $n
            ]);
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

                /**
 * @OA\Get(
 *     path="/api/users/usersWithoutPerm/{permission}",
 *     summary="Liste des utilisateurs n'ayant une permission donné",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="permission",
 *         in="path",
 *         description="ID of the permission ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="list of users without permissions given",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="list taked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="list not taked ",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="list not taked ")
 *         )
 *     )
 * )
 */
        //Liste des utilisateurs n'ayant une permission donné
        public function usersWithoutPerm($p){
            // try{
    
            // }catch (Exception $e){
            //     return response()->json($e);
            // }
            try{
                $permission = permission::find($p);
                if (!$permission) {
                    return response()->json('permission not found');
                }
                $users = User::withoutPermission($permission->name)->get();
                return response()->json([
                    'data' => $users
                ]);
            }catch (Exception $e){
                return response()->json($e);
            }
        }

                        /**
 * @OA\Get(
 *     path="/api/users/usersWithoutPermCount/{permission}",
 *     summary="Nombre des utilisateurs n'ayant une permission donné",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="permission",
 *         in="path",
 *         description="ID of the permission ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="count of users without permission given",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="count done successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="count done successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="count done successfully")
 *         )
 *     )
 * )
 */
        //Nombre des utilisateurs n'ayant une permission donné
        public function usersWithoutPermCount($p){
            // try{
    
            // }catch (Exception $e){
            //     return response()->json($e);
            // }
            try{
                $permission = permission::find($p);
                if (!$permission) {
                    return response()->json('permission not found');
                }
                $users = User::withoutPermission($permission->name)->count();
                return response()->json([
                    'data' => $users
                ]);
            }catch (Exception $e){
                return response()->json($e);
            }
        }

              /**
     * @OA\Get(
     *     path="/api/users/usersRoles",
     *     summary="liste des utilisateurs et leur rôles",
     *     tags={"ManageAccess"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="list of users and their roles"
     * 
     *     )
     * )
     */

        //liste des utilisateurs et leur rôles
        public function usersRoles(){
             try{
                    $data = User::with('roles')->get();
                    return response()->json([
                        'data' => $data
                    ]);
            }catch (Exception $e){
                return response()->json($e);
            }
           
        }

              /**
     * @OA\Get(
     *     path="/api/users/usersPerms",
     *     summary="liste des utilisateurs et leur permissions",
     *     tags={"ManageAccess"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of users and their permissions"
     * 
     *     )
     * )
     */

        //liste des utilisateurs et leur permissions
        public function usersPerms(){
             try{
                // $data = User::with('permissions')->get();
                $users = User::where('is_deleted',false)->get();
                $data = [];
                foreach($users as $user){
                    $data[] = [
                        'id' => $user->id,
                        'lastname' => $user->lastname,
                        'firstname' => $user->firstname,
                        'telephone' => $user->telephone,
                        'email' => $user->email,
                        'country' => $user->country,
                        'city' => $user->city,
                        'address' => $user->address,
                        'sex' => $user->sexe,
                        'postal_code' => $user->postal_code,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                        'is_deleted' => $user->is_deleted,
                        'is_blocked' => $user->is_blocked,
                        'file_profil' => $user->file_profil,
                        'permission' => [
                            'permissionDirect' => $user->getDirectPermissions(),
                            'permissionRole' => $user->getPermissionsViaRoles()
                        ]
                    ];
                }
                
                return response()->json([
                    'data' => $data
                ]);
            }catch (Exception $e){
                return response()->json($e);
            }
           
        }

                                /**
 * @OA\Get(
 *     path="/api/users/rolesPerms/{role}",
 *     summary="liste des permissions d'un rôle",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="role",
 *         in="path",
 *         description="ID of the role ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="list of permissions'roles",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="list of permissions'roles")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Users not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="list not found")
 *         )
 *     )
 * )
 */

        //liste des permissions d'un rôle
        public function rolesPerms($r){
             try{
                $role = Role::find($r);
                if (!$role) {
                    return response()->json('role not found');
                }
                $data = $role->permissions;
                return response()->json([
                    'data' => $data
                ]);
            }catch (Exception $e){
                return response()->json($e);
            }
        }

                                /**
 * @OA\Get(
 *     path="/api/users/rolesPermsCount/{role}",
 *     summary="nombre des permissions d'un rôle",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="role",
 *         in="path",
 *         description="ID of the role ",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="count of permissions of role",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="count done successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="count not done ",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="count not done ")
 *         )
 *     )
 * )
 */

        //nombre des permissions d'un rôle
        public function rolesPermsCount($r){
            try{
               $role = Role::find($r);
               if (!$role) {
                return response()->json('role not found');
            }
               $data = $role->permissions;
               return response()->json([
                   'data' => count($data)
               ]);
           }catch (Exception $e){
               return response()->json($e);
           }
       }

       /**
       * @OA\Post(
       *     path="/api/users/switchToHote",
       *     summary="quitter le role voyageur au role hote",
       *     tags={"ManageAccess"},
       * security={{"bearerAuth": {}}},
       *     @OA\Response(
       *         response=200,
       *         description="move to hote"
       * 
       *     )
       * )
       */

        //quitter le role voyageur au role hote
       public function switchToHote(){
            try{
                // $id = auth()->id();
                $id = 4;
                $user = User::find($id)->assignRole("hote");
                $user = User::find($id)->removeRole("traveler");
                return response()->json([
                    'message' => 'role retire avec success',
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => User::find($id)->getRoleNames()
                    ]
                ]);
            }catch (Exception $e){
                return response()->json($e);
            }
       }

              /**
       * @OA\Post(
       *     path="/api/users/switchToTraveler",
       *     summary="quitter le role hote au role voyageur",
       *     tags={"ManageAccess"},
       * security={{"bearerAuth": {}}},
       *     @OA\Response(
       *         response=200,
       *         description="move to traveler"
       * 
       *     )
       * )
       */

               //quitter le role hote au role voyageur
               public function switchToTraveler(){
                try{
                    // $id = auth()->id();
                    $id = 4;
                    $user = User::find($id)->assignRole("traveler");
                    $user = User::find($id)->removeRole("hote");
                    return response()->json([
                        'message' => 'role retire avec success',
                        'data' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => User::find($id)->getRoleNames()
                        ]
                    ]);
                }catch (Exception $e){
                    return response()->json($e);
                }
           }

}
