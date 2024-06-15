<?php

namespace App\Http\Controllers;

use App\Models\type_demande;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class TypeDemandeController extends Controller
{
    /**
   * @OA\Get(
   *     path="/api/type_demande/index",
   *     summary="Get all type_demandes",
   *     tags={"type_demande"},
   * security={{"bearerAuth": {}}},
   *     @OA\Response(
   *         response=200,
   *         description="List of type_demandes"
   * 
   *     )
   * )
   */
  public function index()
  {
    try{
            $type_demandes = type_demande::all();
            return response()->json(['data' => $type_demandes], 200);
      } catch(Exception $e) {    
        return response()->json(['error' => $e->getMessage()], 200);
     }

  }  


/**
         * @OA\Post(
         *     path="/api/type_demande/store",
         *     summary="Create a new type_demande ",
         *     tags={"type_demande"},
         * security={{"bearerAuth": {}}},
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(property="name", type="string", example="hote , partenaire"),
 *       )
 *     )
 *   ),
         *     @OA\Response(
         *         response=200,
         *         description="type_demande  created successfully"
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
                    'name' => 'required|unique:type_demandes|max:255',
                ]);
                $type_demande = new type_demande();
                    $type_demande->name = $request->name;
                    $type_demande->save();
                    return response()->json(['message' => 'Type de demande créé avec succès.'
                ], 201);
            } catch(Exception $e) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 200);
            }
       
    }





/**
   * @OA\Get(
   *     path="/api/type_demande/show/{id}",
   *     summary="Get a specific type_demande by ID",
   *     tags={"type_demande"},
   * security={{"bearerAuth": {}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the type_demande",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="type_demande details"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="type_demande not found"
   *     )
   * )
   */
  public function show(string $id)
  {
    try{
        $type_demande = type_demande::find($id);

        if (!$type_demande) {
            return response()->json(['error' => 'Type de demande non trouvé.'], 200);
        }

        return response()->json(['data' => $type_demande], 200);
    } catch(Exception $e) {    
          return response()->json(['error' => $e->getMessage()], 200);
    }

  }

  /**
   * @OA\Put(
   *     path="/api/type_demande/updateName/{id}",
   *     summary="Update a type_demande name by ID",
   *     tags={"type_demande"},
   * security={{"bearerAuth": {}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the type_demande",
   *         @OA\Schema(type="integer")
   *     ),
   *   @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"name"},
   *             @OA\Property(property="name", type="string", example="hote,partenaire,etc")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="type_demande updated successfully"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="type_demande not found"
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
                Rule::unique('type_demandes')->ignore($id),
            ],
        ]);
        $type_demande = type_demande::whereId($id)->update($data);
        return response()->json(['message' => 'Nom du Type de demande mise à jour avec succès.'], 200);
    } catch(Exception $e) {    
        return response()->json($e->getMessage());
    }

  }



   /**
   * @OA\Delete(
   *     path="/api/type_demande/destroy/{id}",
   *     summary="Delete a type_demande by ID",
   *     tags={"type_demande"},
   * security={{"bearerAuth": {}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the type_demande",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=204,
   *         description="type_demande deleted successfully"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="type_demande not found"
   *     )
   * )
   */
  public function destroy(string $id)
  {
    try{

        $type_demande = type_demande::find($id);

        if (!$type_demande) {
            return response()->json(['error' => 'Type de demande non trouvé.'], 204);
        }

        type_demande::whereId($id)->delete();

        return response()->json(['message' => 'Type de demande supprimé avec succès.'], 200);

    } catch(Exception $e) {
          return response()->json(['error' => $e->getMessage()], 200);
    }

  }
}
