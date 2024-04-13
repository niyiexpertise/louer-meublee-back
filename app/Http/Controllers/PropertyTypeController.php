<?php

namespace App\Http\Controllers;

use App\Models\PropertyType;
use Illuminate\Http\Request;
use Exception;


class PropertyTypeController extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/propertyType/index",
     *     summary="Get all property types",
     *     tags={"PropertyType"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of property types"
     *
     *     )
     * )
     */



    public function index()
    {
        try{
                $propertyTypes = PropertyType::where('is_deleted', false)->get();
                return response()->json(['data' => $propertyTypes], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

/**
     * @OA\Post(
     *     path="/api/propertyType/store",
     *     summary="create new property type",
     *     tags={"PropertyType"},
     * security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="appartement,etc")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Property type created successfuly",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Property type created successfuly")
     *         )
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
                    'name' => 'required|unique:property_types|max:255',
                ]);
                $propertyType = new PropertyType();
                $propertyType->name = $request->name;
                $propertyType->save();
                return response()->json(['data' => 'Type de propriété created successfuly.', 'propertyType' => $propertyType], 201);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

  /**
     * @OA\Get(
     *     path="/api/propertyType/show/{id}",
     *     summary="Get a specific property type by ID",
     *     tags={"PropertyType"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the property type",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Property type details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Property type not found"
     *     )
     * )
     */

    public function show(string $id)
    {
        try{
                $propertyType = PropertyType::find($id);

                if (!$propertyType) {
                    return response()->json(['error' => 'Type de propriété non trouvé.'], 404);
                }

                return response()->json(['data' => $propertyType], 200);
        } catch(Exception $e) {    
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
     *     path="/api/propertyType/update/{id}",
     *     summary="Update a property type by ID",
     *     tags={"PropertyType"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the property type",
     *         @OA\Schema(type="integer")
     *     ),
     *   @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="beach,etc")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Property type updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Property type not found"
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
                    'name' =>'required | string'
                ]);
                $propertyType = PropertyType::whereId($id)->update($data);
                return response()->json(['data' => 'Type de propriété  mise à jour avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

   /**
     * @OA\Delete(
     *     path="/api/propertyType/destroy/{id}",
     *     summary="Delete a property type by ID",
     *     tags={"PropertyType"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the property type",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Property type deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Property type not found"
     *     )
     * )
     */

    public function destroy(string $id)
    {
        try{
                $propertyType = PropertyType::whereId($id)->update(['is_deleted' => true]);

                if (!$propertyType) {
                    return response()->json(['error' => 'Type de propriété  non trouvé.'], 404);
                }

                return response()->json(['data' => 'Type de propriété  supprimé avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }


    }
    /**
 * @OA\Put(
 *     path="/api/propertyType/block/{id}",
 *     summary="Block a property type",
 *     tags={"PropertyType"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the property type to block",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="PropertyType successfully blocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="PropertyType successfully blocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="PropertyType not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="PropertyType not found")
 *         )
 *     )
 * )
 */

 public function block(string $id)
 {
    try{
            $propertyType = PropertyType::whereId($id)->update(['is_blocked' => true]);

            if (!$propertyType) {
                return response()->json(['error' => 'Type de propriété  non trouvé.'], 404);
            }

            return response()->json(['data' => 'This type of propriety is block successfuly.'], 200);
    } catch(Exception $e) {    
        return response()->json($e);
    }


 }

 /**
 * @OA\Put(
 *     path="/api/propertyType/unblock/{id}",
 *     summary="Unblock a property type",
 *     tags={"PropertyType"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the property type to unblock",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="PropertyType successfully unblocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="PropertyType successfully unblocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="PropertyType not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="PropertyType not found")
 *         )
 *     )
 * )
 */
public function unblock(string $id)
{
    try{
            $propertyType = PropertyType::whereId($id)->update(['is_blocked' => false]);

            if (!$propertyType) {
                return response()->json(['error' => 'Type de propriété  non trouvé.'], 404);
            }

            return response()->json(['data' => 'his type of propriety is unblock successfuly.'], 200);
    } catch(Exception $e) {    
        return response()->json($e);
    }


}

}
