<?php

namespace App\Http\Controllers;

use App\Models\PropertyType;
use Illuminate\Http\Request;
use Exception;
use App\Models\Housing;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File as F ;
use Illuminate\Validation\ValidationException ;
use Illuminate\Validation\Rule;

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
                $propertyTypes = PropertyType::where('is_blocked', false)->where('is_deleted', false)->get();
                return response()->json(['data' => $propertyTypes], 200);
        } catch(Exception $e) {
            return response()->json($e->getMessage());
        }

    }

          /**
     * @OA\Get(
     *     path="/api/propertyType/indexBlock",
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



     public function indexBlock()
     {
         try{
                 $propertyTypes = PropertyType::where('is_blocked', true)->where('is_deleted', false)->get();
                 return response()->json(['data' => $propertyTypes], 200);
         } catch(Exception $e) {
             return response()->json($e->getMessage());
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
         *     summary="Create a new propertyType ",
         *     tags={"PropertyType"},
         * security={{"bearerAuth": {}}},
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(property="name", type="string", example="Appartement"),
 *         @OA\Property(
 *           property="icone",
 *           type="string",
 *           format="binary",
 *           description="Image de profil d'identité (JPEG, PNG, JPG, GIF, taille max : 2048)"
 *         ),
 *       )
 *     )
 *   ),
         *     @OA\Response(
         *         response=200,
         *         description="PropertyType  created successfully"
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
                if ($request->hasFile('icone')) {
                    $icone_name = uniqid() . '.' . $request->file('icone')->getClientOriginalExtension();
                    $identity_profil_path = $request->file('icone')->move(public_path('image/iconeTypePropriete'), $icone_name);
                    $base_url = url('/');
                    $icone_url = $base_url . '/image/iconeTypePropriete/' . $icone_name;
                    $propertyType->icone = $icone_url;
                    }
                $propertyType->name = $request->name;
                $propertyType->save();
                return response()->json(['data' => 'Type de propriété created successfuly.', 'propertyType' => $propertyType], 201);
        } catch(Exception $e) {
            return response()->json($e->getMessage());
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
            return response()->json($e->getMessage());
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
     *     path="/api/propertyType/updateName/{id}",
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
     *             @OA\Property(property="name", type="string", example="Appartement,etc")
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

    public function updateName(Request $request, string $id)
    {
        try{
            $data = $request->validate([
                'name' => [
                    'required',
                    'string',
                    Rule::unique('property_types')->ignore($id),
                ],
            ]);
                $propertyType = PropertyType::whereId($id)->update($data);
                return response()->json(['data' => 'Type de propriété  mise à jour avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e->getMessage());
        }

    }

     /**
     * @OA\Post(
     *     path="/api/propertyType/updateIcone/{id}",
     *     summary="Update an propertyType icone by ID",
     *     tags={"PropertyType"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the propertyType to update",
     *         @OA\Schema(type="integer")
     *     ),
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(
 *           property="icone",
 *           type="string",
 *           format="binary",
 *           description="Image de profil d'identité (JPEG, PNG, JPG, GIF, taille max : 2048)"
 *         ),
 *       )
 *     )
 *   ),
     *     @OA\Response(
     *         response=200,
     *         description="PropertyType updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="PropertyType updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="PropertyType not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="PropertyType not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     */
    public function updateIcone(Request $request, string $id)
    {
        
        try {
            $propertyType = PropertyType::find($id);
            
            if (!$propertyType) {
                return response()->json(['error' => 'type de propriété non trouvé.'], 404);
            }
            
            // $request->validate([
            //         'icone' => 'image|mimes:jpeg,jpg,png,gif'
            //     ]);

            $oldProfilePhotoUrl = $propertyType->icone;
            if ($oldProfilePhotoUrl) {
                $parsedUrl = parse_url($oldProfilePhotoUrl);
                $oldProfilePhotoPath = public_path($parsedUrl['path']);
                if (F::exists($oldProfilePhotoPath)) {
                    F::delete($oldProfilePhotoPath);
                }
            }
                
                if ($request->hasFile('icone')) {
                    $icone_name = uniqid() . '.' . $request->file('icone')->getClientOriginalExtension();
                    $icone_path = $request->file('icone')->move(public_path('image/iconeTypePropriete'), $icone_name);
                    $base_url = url('/');
                    $icone_url = $base_url . '/image/iconeTypePropriete/' . $icone_name;
                    
                    PropertyType::whereId($id)->update(['icone' => $icone_url]);
                    
                    return response()->json(['data' => 'icône de l\'équipement mis à jour avec succès.'], 200);
                } else {
                dd("h");
                return response()->json(['error' => 'Aucun fichier d\'icône trouvé dans la requête.'], 400);
            }
        } catch (QueryException $e) {
            return response()->json(['error' => 'Erreur de requête SQL: ' . $e->getMessage()], 500);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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
                    return response()->json(['error' => 'Type de propriété  non trouvé.'], 200);
                }
                $nbexist= Housing::where('property_type_id', $id)->count(); 
        
                if ($nbexist > 0) {
                    return response()->json(['error' => "Suppression impossible car ce type de propriété est déjà associé à un logement."], 200);
        
                }
                

                return response()->json(['data' => 'Type de propriété supprimé avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e->getMessage());
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
        return response()->json($e->getMessage());
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
        return response()->json($e->getMessage());
    }


}

}