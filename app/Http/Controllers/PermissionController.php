<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Exception;

class PermissionController extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/permission/index",
     *     summary="Get all permissions",
     *     tags={"Permission"},
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
                return response()->json($e);
            }
        

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

  /**
      * @OA\Post(
      *     path="/api/permission/store",
      *     summary="Create a new permission ",
      *     tags={"Permission"},
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
            // $data = $request->validate([
            //     'name' =>'required|unique:permissions'
            // ]);
                $permission = new Permission();
                $permission->name = $request->name;
                $permission->save();
                return response()->json([
                    'message' =>'Successfully created',
                    'permission' => $permission
                ],200);
        }catch (Exception $e){
            return response()->json($e);
        }
        
    }

     /**
     * @OA\Get(
     *     path="/api/permission/show/{id}",
     *     summary="Get a specific permission by ID",
     *     tags={"Permission"},
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
            return response()->json($e);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

/**
     * @OA\Put(
     *     path="/api/permission/update/{id}",
     *     summary="Update a permission by ID",
     *     tags={"Permission"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the permission",
     *         @OA\Schema(type="integer")
     *     ),
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","permission_id"},
     *             @OA\Property(property="name", type="string", example="create"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Permission not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        try{
            $data = $request->validate([
                'name' => 'required'
            ]);
            Permission::whereId($id)->update($data);
            return response()->json([
                'message' => 'permission updated successfully',
                'data' => $data
            ],200);
        }catch (Exception $e){
            return response()->json($e);
        }
    }

 /**
     * @OA\Delete(
     *     path="/api/permission/destroy/{id}",
     *     summary="Delete a permission by ID",
     *     tags={"Permission"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the permission",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Permission deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Permission not found"
     *     )
     * )
     */
    public function destroy( $id)
    {
        try{
            $permission = Permission::find($id);
            if (!$permission) {
                return response()->json([
                    'message' => 'role not found',
                ]);
            }
            $n = User::permission($permission->name)->count();
            // dd($n);
            if (!($n == 0)) {
                return response()->json([
                    'message' => 'Cette permission   a  déjà été affecter à '.$n.' personne(s) veuillez lui ou leurs retiré(s) la permission  avant de la supprimé'
                ]);
            }
            $permission->delete();
            return response()->json([
                'message' => ' successfully deleted'
            ],200);
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
}
