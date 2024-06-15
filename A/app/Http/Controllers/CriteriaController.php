<?php

namespace App\Http\Controllers;

use App\Models\Criteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as F ;

use Exception;
use Illuminate\Validation\Rule;
use App\Models\Note;

class CriteriaController extends Controller
{
    /**
   * @OA\Get(
   *     path="/api/criteria/index",
   *     summary="Get all criterias",
   *     tags={"Criteria"},
   * security={{"bearerAuth": {}}},
   *     @OA\Response(
   *         response=200,
   *         description="List of criterias"
   * 
   *     )
   * )
   */
  public function index()
  {
    try{
            $criterias = Criteria::all()->where('is_deleted', false);
                return response()->json(['data' => $criterias], 200);
  } catch(Exception $e) {    
        return response()->json(['error' => $e->getMessage()], 500);
  }

  }

/**
         * @OA\Post(
         *     path="/api/criteria/store",
         *     summary="Create a new criteria ",
         *     tags={"Criteria"},
         * security={{"bearerAuth": {}}},
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(property="name", type="string", example="communication , sociabilité"),
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
         *         description="Criteria  created successfully"
         *     ),
         *     @OA\Response(
         *         response=401,
         *         description="Invalid credentials"
         *     )
         * )
         */
        public function store(Request $request)
        {
          // return response()->json($request);
          try{
                  $data = $request->validate([
                      'name' => 'required|unique:criterias|max:255',
                  ]);
                  $criteria = new Criteria();
                  if ($request->hasFile('icone')) {
                      $icone_name = uniqid() . '.' . $request->file('icone')->getClientOriginalExtension();
                      $identity_profil_path = $request->file('icone')->move(public_path('image/iconeCriteria'), $icone_name);
                      $base_url = url('/');
                      $icone_url = $base_url . '/image/iconeCriteria/' . $icone_name;
                      $criteria->icone = $icone_url;
                      }
                  $criteria->name = $request->name;
                  $criteria->save();
                  return response()->json(['data' => 'Critère créé avec succès.'
                  , 'critère' => $criteria
              ], 201);
          } catch(Exception $e) {
              return response()->json($e->getMessage(), 500);
          }
      
        }

/**
   * @OA\Get(
   *     path="/api/criteria/show/{id}",
   *     summary="Get a specific criteria by ID",
   *     tags={"Criteria"},
   * security={{"bearerAuth": {}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the criteria",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Criteria details"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Criteria not found"
   *     )
   * )
   */
  public function show(string $id)
  {
    try{
        $criteria = Criteria::find($id);

        if (!$criteria) {
            return response()->json(['error' => 'Critèrenon trouvé.'], 404);
        }

        return response()->json(['data' => $criteria], 200);
    } catch(Exception $e) {    
          return response()->json(['error' => $e->getMessage()], 500);
    }

  }

/**
   * @OA\Put(
   *     path="/api/criteria/updateName/{id}",
   *     summary="Update a criteria name by ID",
   *     tags={"Criteria"},
   * security={{"bearerAuth": {}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the criteria",
   *         @OA\Schema(type="integer")
   *     ),
   *   @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"name"},
   *             @OA\Property(property="name", type="string", example="français,anglais,etc")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Criteria updated successfully"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Criteria not found"
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
                Rule::unique('criterias')->ignore($id),
            ],
        ]);
        $criteria = Criteria::whereId($id)->update($data);
        return response()->json(['data' => 'Nom du Critère mise à jour avec succès.'], 200);
    } catch(Exception $e) {    
          return response()->json(['error' => $e->getMessage()], 500);
    }

  }

  /**
     * @OA\Post(
     *     path="/api/criteria/updateIcone/{id}",
     *     summary="Update a criteria icone by ID",
     *     tags={"Criteria"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the criteria to update",
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
     *         description="Criteria updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Criteria updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Criteria not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Criteria not found")
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
            $criteria = Criteria::find($id);
            
            if (!$criteria) {
                return response()->json(['error' => 'Criteria non trouvé.'], 404);
            }
            
            // $request->validate([
            //         'icone' => 'image|mimes:jpeg,jpg,png,gif'
            //     ]);

            $oldProfilePhotoUrl = $criteria->icone;
            if ($oldProfilePhotoUrl) {
                $parsedUrl = parse_url($oldProfilePhotoUrl);
                $oldProfilePhotoPath = public_path($parsedUrl['path']);
                if (F::exists($oldProfilePhotoPath)) {
                    F::delete($oldProfilePhotoPath);
                }
            }
                
                if ($request->hasFile('icone')) {
                    $icone_name = uniqid() . '.' . $request->file('icone')->getClientOriginalExtension();
                    $icone_path = $request->file('icone')->move(public_path('image/iconeCriteria'), $icone_name);
                    $base_url = url('/');
                    $icone_url = $base_url . '/image/iconeCriteria/' . $icone_name;
                    
                    Criteria::whereId($id)->update(['icone' => $icone_url]);
                    
                    return response()->json(['data' => 'icône du critère mis à jour avec succès.'], 200);
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
   *     path="/api/criteria/destroy/{id}",
   *     summary="Delete a criteria by ID",
   *     tags={"Criteria"},
   * security={{"bearerAuth": {}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the criteria",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=204,
   *         description="Criteria deleted successfully"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Criteria not found"
   *     )
   * )
   */
  public function destroy(string $id)
  {
    try{
            $criteria = Criteria::whereId($id)->update(['is_deleted' => true]);

            if (!$criteria) {
                return response()->json(['error' => 'Critère non trouvé.'], 404);
            }
            $nbexist=Note::where('critere_id', $id)->count(); 
        
            if ($nbexist > 0) {
                return response()->json(['error' => "Suppression impossible car ce critère a déjà été utilisé dans une note d'un logement."],200);
            }

            return response()->json(['data' => 'Critère supprimé avec succès.'], 200);
    
    } catch(Exception $e) {    
          return response()->json(['error' => $e->getMessage()], 500);
    }

  }

      /**
* @OA\Put(
*     path="/api/criteria/block/{id}",
*     summary="Block a criteria",
*     tags={"Criteria"},
*security={{"bearerAuth": {}}},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the criteria to block",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Criteria successfully blocked",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="Criteria successfully blocked")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Criteria not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Criteria not found")
*         )
*     )
* )
*/

  public function block(string $id)
{
    try{
            $criteria = Criteria::whereId($id)->update(['is_blocked' => true]);

            if (!$criteria) {
                return response()->json(['error' => 'Critère non trouvé.'], 404);
            }

            return response()->json(['data' => 'This type of propriety is block successfuly.'], 200);
    
    } catch(Exception $e) {    
          return response()->json(['error' => $e->getMessage()], 500);
    }


}

/**
* @OA\Put(
*     path="/api/criteria/unblock/{id}",
*     summary="Unblock a criteria",
*     tags={"Criteria"},
*security={{"bearerAuth": {}}},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the criteria to unblock",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Criteria successfully unblocked",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="Criteria successfully unblocked")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Criteria not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Criteria not found")
*         )
*     )
* )
*/

public function unblock(string $id)
{
    try{
            $criteria = Criteria::whereId($id)->update(['is_blocked' => false]);

            if (!$criteria) {
                return response()->json(['error' => 'Critère non trouvé.'], 404);
            }

            return response()->json(['data' => 'his type of propriety is unblock successfuly.'], 200);
    } catch(Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
    }


}

}
