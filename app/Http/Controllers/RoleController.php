<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\Right;

use Exception;


class RoleController extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/role/index",
     *     summary="Get all roles",
     *     tags={"Role"},
     * security={{"bearerAuth": {}}},
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
      *     path="/api/role/store",
      *     summary="Create a new role ",
      *     tags={"Role"},
      *security={{"bearerAuth": {}}},
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

            $right = new Right();
            $right->name = $request->name;
            $right->save();
            return response()->json([
                'message' =>'Successfully created',
                'role' => $role
            ],200);
        }catch (Exception $e){
            return response()->json($e);
        }
        
    }

     /**
     * @OA\Get(
     *     path="/api/role/show/{id}",
     *     summary="Get a specific role by ID",
     *     tags={"Role"},
     * security={{"bearerAuth": {}}},
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
     * @OA\Delete(
     *     path="/api/role/destroy/{id}",
     *     summary="Delete a role by ID",
     *     tags={"Role"},
     * security={{"bearerAuth": {}}},
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
            if (!(Right::find($id) && Role::find($id))) {
                return response()->json([
                    'message' => 'role not found'
                ]);
            }
                Role::find($id)->delete();
                Right::find($id)->delete();
                return response()->json([
                    'message' => ' role  deleted successfully ',
                ]);
        }catch (Exception $e){
            return response()->json($e);
        }
    }
}
