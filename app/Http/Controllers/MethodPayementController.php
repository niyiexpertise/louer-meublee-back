<?php

namespace App\Http\Controllers;

use App\Models\MethodPayement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File as F ;
use Illuminate\Validation\ValidationException ;
use Illuminate\Validation\Rule;
class MethodPayementController extends Controller
{
     /**
   * @OA\Get(
   *     path="/api/methodPayement/index",
   *     summary="Get all methodPayements",
   *     tags={"MethodPayement"},
   * security={{"bearerAuth": {}}},
   *     @OA\Response(
   *         response=200,
   *         description="List of methodPayements"
   *
   *     )
   * )
   */
  public function index()
  {
    try{
            $methodPayements = MethodPayement::all();
            return response()->json(['data' => $methodPayements], 200);
      } catch(Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
     }

  }


/**
         * @OA\Post(
         *     path="/api/methodPayement/store",
         *     summary="Create a new methodPayement ",
         *     tags={"MethodPayement"},
         * security={{"bearerAuth": {}}},
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(property="name", type="string", example="flooz , momo"),
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
         *         description="MethodPayement  created successfully"
         *     ),
         *     @OA\Response(
         *         response=401,
         *         description="Invalid credentials"
         *     )
         * )
         */
    public function store(Request $request)
    {
          // try{

            // } catch(Exception $e) {
            //     return response()->json([
            //         'error' => 'An error occurred',
            //         'message' => $e->getMessage()
            //     ], 500);
            // }

              try{
                $data = $request->validate([
                    'name' => 'required|unique:method_payements|max:255',
                ]);
                $methodPayement = new MethodPayement();
                if ($request->hasFile('icone')) {
                    $icone_name = uniqid() . '.' . $request->file('icone')->getClientOriginalExtension();
                    $identity_profil_path = $request->file('icone')->move(public_path('image/iconeMethodPayement'), $icone_name);
                    $base_url = url('/');
                    $icone_url = $base_url . '/image/iconeMethodPayement/' . $icone_name;
                    $methodPayement->icone = $icone_url;
                    }
                    $methodPayement->name = $request->name;
                    $methodPayement->save();
                    return response()->json(['data' => 'Méthode de payement créé avec succès.'
                ], 201);
            } catch(Exception $e) {
                return response()->json([
                    'error' => 'An error occurred',
                    'message' => $e->getMessage()
                ], 500);
            }

    }





/**
   * @OA\Get(
   *     path="/api/methodPayement/show/{id}",
   *     summary="Get a specific methodPayement by ID",
   *     tags={"MethodPayement"},
   * security={{"bearerAuth": {}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the methodPayement",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="MethodPayement details"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="MethodPayement not found"
   *     )
   * )
   */
  public function show(string $id)
  {
    try{
        $methodPayement = MethodPayement::find($id);

        if (!$methodPayement) {
            return response()->json(['error' => 'Méthode de payementnon trouvé.'], 404);
        }

        return response()->json(['data' => $methodPayement], 200);
    } catch(Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
    }

  }

  /**
   * @OA\Put(
   *     path="/api/methodPayement/updateName/{id}",
   *     summary="Update a methodPayement name by ID",
   *     tags={"MethodPayement"},
   * security={{"bearerAuth": {}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the methodPayement",
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
   *         description="MethodPayement updated successfully"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="MethodPayement not found"
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
                Rule::unique('method_payements')->ignore($id),
            ],
        ]);
        $methodPayement = MethodPayement::whereId($id)->update($data);
        return response()->json(['data' => 'Nom du Méthode de payement mise à jour avec succès.'], 200);
    } catch(Exception $e) {
        return response()->json($e->getMessage());
    }

  }



 /**
     * @OA\Post(
     *     path="/api/methodPayement/updateIcone/{id}",
     *     summary="Update a methodPayement icone by ID",
     *     tags={"MethodPayement"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the methodPayement to update",
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
     *         description="MethodPayement updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="MethodPayement updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="MethodPayement not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="MethodPayement not found")
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
            $methodPayement = MethodPayement::find($id);

            if (!$methodPayement) {
                return response()->json(['error' => 'MethodPayement non trouvé.'], 404);
            }

            // $request->validate([
            //         'icone' => 'image|mimes:jpeg,jpg,png,gif'
            //     ]);

            $oldProfilePhotoUrl = $methodPayement->icone;
            if ($oldProfilePhotoUrl) {
                $parsedUrl = parse_url($oldProfilePhotoUrl);
                $oldProfilePhotoPath = public_path($parsedUrl['path']);
                if (F::exists($oldProfilePhotoPath)) {
                    F::delete($oldProfilePhotoPath);
                }
            }

                if ($request->hasFile('icone')) {
                    $icone_name = uniqid() . '.' . $request->file('icone')->getClientOriginalExtension();
                    $icone_path = $request->file('icone')->move(public_path('image/iconeMethodPayement'), $icone_name);
                    $base_url = url('/');
                    $icone_url = $base_url . '/image/iconeMethodPayement/' . $icone_name;

                    MethodPayement::whereId($id)->update(['icone' => $icone_url]);

                    return response()->json(['data' => 'icône du méthode de payement mis à jour avec succès.'], 200);
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
   *     path="/api/methodPayement/destroy/{id}",
   *     summary="Delete a methodPayement by ID",
   *     tags={"MethodPayement"},
   * security={{"bearerAuth": {}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the methodPayement",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=204,
   *         description="MethodPayement deleted successfully"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="MethodPayement not found"
   *     )
   * )
   */
  public function destroy(string $id)
  {
    try{

        $methodPayement = MethodPayement::find($id);

        if (!$methodPayement) {
            return response()->json(['error' => 'Méthode de payement non trouvé.'], 404);
        }

        MethodPayement::whereId($id)->delete();

        return response()->json(['data' => 'Méthode de payement supprimé avec succès.'], 200);

    } catch(Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
    }

  }







}
