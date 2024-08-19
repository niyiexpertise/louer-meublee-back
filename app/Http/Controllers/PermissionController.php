<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Exception;
use Illuminate\Validation\Rule;
class PermissionController extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/permission/index",
     *     summary="Get all permissions",
     *     tags={"Permission"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of permissions"
     *
     *     )
     * )
     */
    public function index()
    {
        try{
            $permissions = Permission::all();
            return response()->json([
                'permissions' => $permissions
            ],200);
            }catch (Exception $e){
                  return response()->json(['error' => $e->getMessage()], 500);
            }
        

    }


  /**
      * @OA\Post(
      *     path="/api/permission/store",
      *     summary="Create a new permission ",
      *     tags={"Permission"},
      *security={{"bearerAuth": {}}},
      *     @OA\RequestBody(
      *         required=true,
      *         @OA\JsonContent(
      *             required={"name"},
      *             @OA\Property(property="name", type="string", example="create"),
      *         )
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="Permission  created successfully"
      *     ),
      *     @OA\Response(
      *         response=401,
      *         description="Invalid credentials"
      *     )
      * )
      */
    public function store(Request $request)
    {
        try{
            $data = $request->validate([
                'name' => 'required|unique:permissions|max:255',
            ]);
            $exist = Permission::where('name',$request->name)->exists();
            if($exist){
                return response()->json([
                    "message"=>"This name has already taken"
                ]);
            }
                $permission = new Permission();
                $permission->name = $request->name;
                $permission->guard_name= "web";
                $permission->save();
                return response()->json([
                    'message' =>'Successfully created',
                    'permission' => $permission
                ],200);
        }catch (Exception $e){
              return response()->json(['error' => $e->getMessage()], 500);
        }
        
    }

     /**
     * @OA\Get(
     *     path="/api/permission/show/{id}",
     *     summary="Get a specific permission by ID",
     *     tags={"Permission"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the permission",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Permission not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        try{
            $permission = Permission::find($id);
            return response()->json([
                'data' => $permission
            ],200);
        }catch (Exception $e){
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }
/**
     * @OA\Get(
     *     path="/api/permission/indexbycategorie",
     *     summary="Get all permissions groupe by categorie",
     *     tags={"Permission"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of permissions groupe by category"
     * 
     *     )
     * )
     */
    public function indexbycategorie()
    {
        try {
            // Récupérer toutes les permissions
            $permissions = Permission::all();
    
            // Grouper les permissions par groupe
            $groupedPermissions = $permissions->filter(function ($permission) {
                return !is_null($permission->groupe); // Filtrer les permissions où groupe n'est pas nul
            })->groupBy('groupe')->map(function ($group) {
                return [
                    'permissions' => $group->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'description' => $permission->description,
                            // Ajoutez d'autres champs pertinents ici
                        ];
                    }),
                    'count' => $group->count() // Nombre de permissions dans ce groupe
                ];
            });
    
            // Calculer le nombre total de permissions en faisant la somme des sous-totaux
            $totalPermissionsCount = $groupedPermissions->sum('count');
    
            // Préparer la structure de la réponse
            $response = [
                'groups' => $groupedPermissions->mapWithKeys(function ($data, $group) {
                    return [$group => [
                        'permissions' => $data['permissions'],
                        'count' => $data['count']
                    ]];
                }),
                'total_permissions_count' => $totalPermissionsCount
            ];
    
            // Retourner la réponse JSON
            return response()->json($response, 200);
    
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    


}