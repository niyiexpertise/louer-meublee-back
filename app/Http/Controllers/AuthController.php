<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationEmail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Exception;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Right;
use App\Models\User_right;
use validationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmationLoginEmail;
use App\Mail\NotificationEmail;
use App\Models\Notification;
use App\Mail\NotificationEmailwithoutfile;
use Dotenv\Exception\ValidationException as ExceptionValidationException;

class AuthController extends Controller
{

/**
 * @OA\Post(
 *     path="/api/users/assignPermsToRole/{role}",
 *     summary="Assign multiple permissions to a role",
 *     tags={"ManageAccess"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="role",
 *         in="path",
 *         description="ID of the role",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"permissions"},
 *             @OA\Property(
 *                 property="permissions",
 *                 type="array",
 *                 @OA\Items(type="integer", format="int64"),
 *                 description="List of permission IDs to assign to the role"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Permissions assigned to role successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Permissions assigned to role successfully"),
 *             @OA\Property(
 *                 property="assigned_permissions",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="permission", type="string", description="Name of the permission"),
 *                     @OA\Property(property="status", type="string", enum={"Assigned", "Already assigned"}, description="Status of the permission assignment")
 *                 )
 *             ),
 *             @OA\Property(property="role", type="object", description="Details of the role assigned"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Role or permission not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Role or permission not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation failed",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Validation failed"),
 *             @OA\Property(property="message", type="string", example="Validation error message")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="An error occurred"),
 *             @OA\Property(property="message", type="string", example="Internal server error message")
 *         )
 *     )
 * )
 */


    //ajouter une permission à un rôle
    public function assignPermsToRole(Request $request, $r){
    try {
        $role = Role::find($r);

        if (!$role) {
            return response()->json('Role not found');
        }
        $requestData = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'integer|exists:permissions,id'
        ]);

        $assignedPermissions = [];
        $permissions=$request->input('permissions');

        foreach ($permissions as $permissionId) {
            $permission = Permission::find($permissionId);

            if (!$permission) {
                return response()->json('Permission not found');
            }

            if ($role->hasPermissionTo($permission->name)) {
                $assignedPermissions[] = [
                    'permission' => $permission->name,
                    'status' => 'Already assigned'
                ];
            } else {
                $role->givePermissionTo($permission);
                $assignedPermissions[] = [
                    'permission' => $permission->name,
                    'status' => 'Assigned'
                ];
            }
        }

        return response()->json([
            'message' => 'Permissions assigned to role successfully',
            'assigned_permissions' => $assignedPermissions,
            'role' => $role
        ]);
    } catch(ValidationException $e) {
        return response()->json([
            'error' => 'Validation failed',
            'message' => $e->validator->errors()->first()
        ], 422);
    } catch(Exception $e) {
        return response()->json([
            'error' => 'An error occurred',
            'message' => $e->getMessage()
        ], 500);
    }
}


   /**
 * @OA\Post(
 *     path="/api/users/RevokePermsToRole/{role}",
 *     summary="Revoke multiple permissions from a role",
 *     tags={"ManageAccess"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="role",
 *         in="path",
 *         description="ID of the role",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"permissions"},
 *             @OA\Property(
 *                 property="permissions",
 *                 type="array",
 *                 @OA\Items(type="integer", format="int64"),
 *                 description="List of permission IDs to revoke from the role"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Permissions revoked from role successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Permissions revoked from role successfully"),
 *             @OA\Property(property="role", type="object", description="Details of the role"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Role or permission not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Role or permission not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation failed",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Validation failed"),
 *             @OA\Property(property="message", type="string", example="Validation error message")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="An error occurred"),
 *             @OA\Property(property="message", type="string", example="Internal server error message")
 *         )
 *     )
 * )
 */

// Retirer des permissions à un rôle
public function RevokePermsToRole(Request $request, $r){
    try {
        $role = Role::find($r);

        if (!$role) {
            return response()->json('Role not found');
        }

        $requestData = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'integer|exists:permissions,id'
        ]);

        $revokedPermissions = [];

        foreach ($requestData['permissions'] as $permissionId) {
            $permission = Permission::find($permissionId);

            if ($role->hasPermissionTo($permission->name)) {
                $role->revokePermissionTo($permission);
                $revokedPermissions[] = $permission->name;
            }
        }

        return response()->json([
            'message' => 'Permissions revoked from role successfully',
            'revoked_permissions' => $revokedPermissions,
            'role' => $role
        ]);
    } catch(ValidationException $e) {
        return response()->json([
            'error' => 'Validation failed',
            'message' => $e->validator->errors()->first()
        ], 422);
    } catch(Exception $e) {
        return response()->json([
            'error' => 'An error occurred',
            'message' => $e->getMessage()
        ], 500);
    }
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
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json('User not found');
            }


            $rights = $user->user_right()->with('right')->get();

            if($rights->isEmpty()) {
                return response()->json(['message' => 'Roles not found']);
            }


            $roles = $rights->pluck('right.name')->unique()->toArray();

            return response()->json(['data' => $roles]);
        } catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
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

                $right = Right::find($r);

                $user = User::find($id);
                if (!$role) {
                    return response()->json([
                        'message' => 'role not found',
                    ]);
                }

                if($role->name == "superAdmin"){
                    return (new ServiceController())->apiResponse(404, [], "Repentez vous.");
                }

                if (!$right) {
                    return response()->json([
                        'message' => 'role not found',
                    ]);
                }

                if (!$user) {
                    return response()->json('user not found');
                }
                if(User_right::where('user_id',$id)->where('right_id',$r)->exists()){
                    return response()->json([
                        'message' => 'Ce rôle a déjà été assigné à cet utilisateur'
                    ]);
                }
                $u = User_right::where('user_id',$id)->get();
                foreach($u as $utilisateur){
                    $roles = Role::where('id',$utilisateur->right_id)->first();
                    $user->removeRole($roles);
                }

                $user->assignRole($role);
                $user_right = new User_right();
                $user_right->user_id = $id;
                $user_right->right_id = $r;
                $user_right->save();
                $permission_role = $role->permissions;
                $permission_name="";
                foreach($permission_role as $pr){
                    $permission_name .= " " . $pr['name'] . ",";
                }
                $message_notification ="Vous avez maintenant le rôle de ". $role->name .".";

                    $mail = [
                        'title' => "Attribution du role de ".$role->name,
                        'body' => $message_notification
                    ];
                    try {
                        dispatch( new SendRegistrationEmail($user->email, $mail['body'], $mail['title'], 2));
                    } catch (\Exception $e) {

                    }

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
                        'role' => User::find($id)->getRoleNames(),
                    ]
                ]);
        }catch(ExceptionValidationException $e) {
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

            $right = Right::find($r);

            $user = User::find($id);
            if (!$role) {
                return response()->json([
                    'message' => 'role not found',
                ]);
            }
            if (!$right) {
                return response()->json([
                    'message' => 'role not found',
                ]);
            }

            if (!$user) {
                return response()->json('user not found');
            }

                if(!(User_right::where('user_id',$id)->where('right_id',$r)->exists())){
                    return response()->json([
                        'message' => 'Cet utilisateur n\'a pas le rôle que vous voulez lui retirer.'
                    ]);
                }
            $count = User_right::where('user_id', $id)->count();

                if ($count == 1) {
                    return response()->json([
                        'error' => "L'utilisateur a un seul role dans le système actuellement qui est le role traveler;vous ne pouvez donc pas lui retirer ",
                        'message' => 'Merci bien de lui bloquer donc au lieu de le retirer le seul role restant'
                    ], 403);
                }
                if ($user->hasRole($role->name)) {
                    $user->removeRole($role);
                }

                User_right::where('user_id',$id)->where('right_id',$r)->delete();

                $u = User_right::where('user_id',$id)->get();
                foreach($u as $utilisateur){
                    $roles = Role::where('id',$utilisateur->right_id)->first();

                }

                if($user->roles->count()!= 1){
                    $user->assignRole($roles);
                }
                $message_notification ="Vous n'avez plus maintenant le rôle de  ". $role->name .". Ce role vient de vous être retiré par l'administrateur.";

                    $mail = [
                        'title' => "Retrait du role de ".$role->name,
                        'body' => $message_notification
                    ];
                    try {
                        dispatch( new SendRegistrationEmail($user->email, $mail['body'], $mail['title'], 2));

                    } catch (\Exception $e) {

                    }
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
 *     path="/api/users/assignPermsToUser/{id}",
 *     summary="Assign multiple permissions to a user",
 *     tags={"ManageAccess"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the user",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"permissions"},
 *             @OA\Property(
 *                 property="permissions",
 *                 type="array",
 *                 @OA\Items(type="integer", format="int64"),
 *                 description="List of permission IDs to assign to the role"
 *             )
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
    public function assignPermsToUser(Request $request,$id,){
        try{
            $requestData = $request->validate([
                'permissions' => 'required|array',
                'permissions.*' => 'integer|exists:permissions,id'
            ]);

            $user = User::find($id);

            $permissions=$request->input('permissions');

            if (!$user) {
                return response()->json('user not found');
            }

        $assignedPermissions =[];
        $permission_name= "";
        $permission_add=[];
        $permissions=$request->input('permissions');

        foreach ($permissions as $permissionId) {
            $permission = Permission::find($permissionId);

            if (!$permission) {
                return response()->json('Permission not found');
            }

            if ($user->hasPermissionTo($permission->name)) {
                $assignedPermissions[] = [
                    'permission' => $permission->name,
                    'status' => 'Already assigned'
                ];
            } else {
                $user->givePermissionTo($permission);
                $assignedPermissions[] = [
                    'permission' => $permission->name,
                    'status' => 'Assigned'
                ];
                $permission_name .= " " .  $permission->name . ",";
            }
        }

            $permissionsDirect = $user->getDirectPermissions();
            //$permissionsRole = $user->getPermissionsViaRoles();
            $userRights = User_right::where('user_id', $id)->get();

            $permissions = [];

            foreach ($userRights as $userRight) {
                $role = Role::find($userRight->right_id);
                if ($role) {
                    $permissions = array_merge($permissions, $role->permissions()->pluck('name')->toArray());
                }
            }

            $uniquePermissions = array_unique($permissions);
            $message_notification= "Vous avez maintenant les permissions suivantes: ". $permission_name . ".";
                    $mail = [
                        'title' => "Notification sur les nouvelle permissions attribuées",
                        'body' => $message_notification
                    ];
                    try {

                        dispatch( new SendRegistrationEmail($user->email, $mail['body'], $mail['title'], 2));
                    } catch (\Exception $e) {

                    }
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
                        'bilan_permission_add' => $assignedPermissions,
                        'permission_user' => [
                            'permissionDirect' => $permissionsDirect,
                            'permissionRole' => $uniquePermissions
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
 *     path="/api/users/revokePermsToUser/{id}",
*     summary="retirer multiple permissions to a user",
 *     tags={"ManageAccess"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the user",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"permissions"},
 *             @OA\Property(
 *                 property="permissions",
 *                 type="array",
 *                 @OA\Items(type="integer", format="int64"),
 *                 description="List of permission IDs to assign to the role"
 *             )
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
    public function revokePermsToUser(Request $request,$id){
        try{
            $requestData = $request->validate([
                'permissions' => 'required|array',
                'permissions.*' => 'integer|exists:permissions,id'
            ]);

            $permissions=$request->input('permissions');
            $assignedPermissions =[];
            $permission_name= "";
            $user = User::find($id);
            if (!$user) {
                return response()->json('user not found');
            }
            foreach ($permissions as $permissionId) {
                $permission = Permission::find($permissionId);

                if (!$permission) {
                    return response()->json('Permission not found');
                }

                if (!$user->hasDirectPermission($permission->name)) {
                    $retiredPermissions[] = [
                        'permission' => $permission->name,
                        'status' => 'Not retired because he has no this permission'
                    ];
                } else {
                    $user->revokePermissionTo($permission);
                    $retiredPermissions[] = [
                        'permission' => $permission->name,
                        'status' => 'retired'
                    ];
                    $permission_name .= " " .  $permission->name . ",";

                }
            }


            $permissionsDirect = $user->getDirectPermissions();

            //permissionsRole = $user->getPermissionsViaRoles();
            $userRights = User_right::where('user_id', $id)->get();

            $permissions = [];

            foreach ($userRights as $userRight) {
                $role = Role::find($userRight->right_id);
                if ($role) {
                    $permissions = array_merge($permissions, $role->permissions()->pluck('name')->toArray());
                }
            }

            $uniquePermissions = array_unique($permissions);
            $message_notification= "Vous n'avez plus les permissions suivantes: ". $permission_name . ".Elles vous ont été retiré par l'admin.";
                    $mail = [
                        'title' => "Notification sur le retrait des permissions ",
                        'body' => $message_notification
                    ];
                    try {


                        dispatch( new SendRegistrationEmail($user->email, $mail['body'], $mail['title'], 2));
                    } catch (\Exception $e) {

                    }
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
                    'bilan_permission_retired' => $retiredPermissions,
                    'permission' => [
                        'permissionDirect' => $permissionsDirect,
                        'permissionRole' => $uniquePermissions
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
             $userRights = User_right::where('user_id', $id)->get();

            $permissions = [];

            foreach ($userRights as $userRight) {
                $role = Role::find($userRight->right_id);
                if ($role) {
                    $permissions = array_merge($permissions, $role->permissions()->pluck('name')->toArray());
                }
            }

            // $uniquePermissions = array_unique($permissions);
            $uniquePermissions = $permissions;
            // $permissionNames = $users->getPermissionNames();
            $permissionsDirectes = $users->getDirectPermissions();
            $permissionsDirect = [];

            foreach($permissionsDirectes as $permissionsDirecte){
                $permissionsDirect[] = $permissionsDirecte->name;
            }
            //$permissionsRole = $users->getPermissionsViaRoles();

            return response()->json([
                'data' => [
                    'directPermissions' => $permissionsDirect,
                    'indirectPermissions' => $uniquePermissions
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
        try {
            // Recherche du rôle par son nom
            $role = Right::where('id', $r)->first();

            if (!$role) {
                return response()->json('Role not found');
            }

            // Obtenez tous les utilisateurs associés à ce rôle
            $users = User_right::where('right_id', $role->id)->with('user')->get()->pluck('user');

            return response()->json(['data' => $users]);
        } catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }



        /**
 * @OA\Get(
 *     path="/api/users/usersWithPerm/{permission}",
 *     summary="Liste des utilisateurs ayant une permission donné ",
 *     tags={"ManageAccess"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="permission",
 *         in="path",
 *         description="Id of the permission ",
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
        //       return response()->json(['error' => $e->getMessage()], 500);
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

        //liste des utilisateurs et leur rôlespublic function usersRoles()
        public function usersRoles()
{
    try {
        // Récupérer tous les utilisateurs avec leurs rôles, en filtrant les utilisateurs non supprimés (is_deleted = false)
        $userRights = User_right::with(['user' => function ($query) {
            $query->where('is_deleted', false);
        }, 'right'])->get();

        // Créer un tableau associatif pour stocker les rôles de chaque utilisateur
        $data = [];

        foreach ($userRights as $userRight) {
            // Assurez-vous que l'utilisateur n'est pas null
            if (!$userRight->user) {
                continue;
            }

            $userId = $userRight->user_id;
            $roleName = $userRight->right->name;

            // Vérifier si l'utilisateur existe déjà dans le tableau
            if (!isset($data[$userId])) {
                $data[$userId] = [
                    'user_id' => $userId,
                    'user_info' => $userRight->user->toArray(), // Convertir l'utilisateur en tableau
                ];
            }

            // Ajouter le rôle à l'utilisateur dans la clé 'roles' de 'user_info'
            if (!isset($data[$userId]['user_info']['roles'])) {
                $data[$userId]['user_info']['roles'] = [];
            }
            $data[$userId]['user_info']['roles'][] = $roleName;
        }

        // Convertir le tableau associatif en un tableau numérique
        $formattedData = array_values($data);

        return response()->json([
            'data' => $formattedData
        ]);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
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
                  return response()->json(['error' => $e->getMessage()], 500);
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
                 return response()->json(['error' => $e->getMessage()], 500);
           }
       }


/**
 * @OA\Get(
 *     path="/api/users/usersCountByRole",
 *     tags={"Administration"},
* security={{"bearerAuth": {}}},
 *     summary="Recupère chaque role avec le nombre d'utilisateur associé",
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error",
 *     )
 * )
 */
           public function usersCountByRole(){
            try {

                $rolesCount = Right::withCount('user_right')->get()->pluck('user_right_count', 'name');

                return response()->json(['data' => $rolesCount]);
            } catch(Exception $e) {
                return response()->json([
                    'error' => 'An error occurred',
                    'message' => $e->getMessage()
                ], 500);
            }
        }


   /**
 * @OA\Post(
 *     path="/api/users/switchToAnotherRole/{roleId}",
 *     tags={"ManageAccess"},
 *     summary="Switch to another role",
 *     operationId="switchToAnotherRole",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="roleId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successfully switched to another role",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="role",
 *                 type="array",
 *                 @OA\Items()
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User has no roles or role does not exist",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="You have no roles or the role you want to switch to does not exist")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="An error occurred")
 *         )
 *     )
 * )
 */



        public function switchToAnotherRole($roleId){
            try{
             $id = auth()->id();
             $roleName = Role::whereId($roleId)->first()->name;


                $role =  User::find($id)->getRoleNames();
                if (count($role) == 0) {
                    return (new ServiceController())->apiResponse(404,[], "Vous n'avez aucun rôle");
                }
                $role_actif = $role[0];
                $r = Right::where('name',$roleName)->first();
            $exist = User_right::where('user_id',$id)->where('right_id',$r->id)->exists();
                    if(!$exist){
                        return (new ServiceController())->apiResponse(404,[], "Vous n'avez pas le rôle auquel vous voulez switcher!");
                    }
                $user = User::find($id)->removeRole($role_actif);
                $user = User::find($id)->assignRole($roleName);
                return (new ServiceController())->apiResponse(200,
                [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => User::find($id)->getRoleNames()
                ],
                 "Switch vers le rôle $roleName effectué avec succès");
            }catch (Exception $e){
                  return response()->json(['error' => $e->getMessage()], 500);
            }
       }

}
