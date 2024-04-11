<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

use Exception;


class RoleController extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/role/index",
     *     summary="Get all roles",
     *     tags={"Role"},
     *     @OA\Response(
     *         response=200,
     *         description="List of roles"
     * 
     *     )
     * )
     */
    public function index()
    {
        try{
            $roles = Role::all();
            return response()->json([
                'roles' => $roles
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

  /**
      * @OA\Post(
      *     path="/api/role/store",
      *     summary="Create a new role ",
      *     tags={"Role"},
      *     @OA\RequestBody(
      *         required=true,
      *         @OA\JsonContent(
      *             required={"name"},
      *             @OA\Property(property="name", type="string", example="traveler"),
      *         )
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="Role  created successfully"
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
            $role = new Role();
            $role->name = $request->name;
            $role->save();
            return response()->json([
                'message' =>'Successfully created',
                'role' => $role
            ],200);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
        
    }

     /**
     * @OA\Get(
     *     path="/api/role/show/{id}",
     *     summary="Get a specific role by ID",
     *     tags={"Role"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the role",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        try{
                $role = Role::find($id);
                return response()->json([
                    'data' => $role
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

/**
     * @OA\Put(
     *     path="/api/role/update/{id}",
     *     summary="Update a role by ID",
     *     tags={"Role"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the role",
     *         @OA\Schema(type="integer")
     *     ),
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","role_id"},
     *             @OA\Property(property="name", type="string", example="traveler"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
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
            Role::whereId($id)->update($data);
            return response()->json([
                'message' => 'Role updated successfully',
                'data' => $data
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
     * @OA\Delete(
     *     path="/api/role/destroy/{id}",
     *     summary="Delete a role by ID",
     *     tags={"Role"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the role",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Role deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try{
                $role = Role::find($id);
                $a = new AuthController();
                if (!$role) {
                    return response()->json([
                        'message' => 'role not found',
                    ]);
                }
                $n = User::role($role->name)->count();
                if (!($n == 0)) {
                    return response()->json([
                        'message' => 'Ce rôle   a  déjà été affecter à '.$n.' personne(s) veuillez lui ou leurs retiré(s) la rôle  avant de la supprimé'
                    ]);
                }
                $role->delete();
                return response()->json([
                    'message' => ' role  deleted successfully ',
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
}
