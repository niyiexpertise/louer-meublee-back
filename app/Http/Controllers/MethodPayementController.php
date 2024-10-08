<?php

namespace App\Http\Controllers;

use App\Models\MethodPayement;
use App\Services\FileService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File as F ;
use Illuminate\Validation\ValidationException ;
use Illuminate\Validation\Rule;
class MethodPayementController extends Controller
{

    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

     /**
   * @OA\Get(
   *     path="/api/methodPayement/index/{is_retrait}",
   *   @OA\Parameter(
 *          name="is_retrait",
 *          in="path",
 *          required=true,
 *          description="Voir si la route est appelé au niveau du retrait",
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),
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
  public function index($is_retrait = false)
{

    try {
        if($is_retrait != 1 && $is_retrait != 0){
            return (new ServiceController())->apiResponse(404, [], 'is_retrait doit être un booleen');
        }
        $methodPayements = MethodPayement::where('is_deleted', false)
            ->where('is_actif', true);

        if ($is_retrait) {
            $portfeuille = (new ReservationController())->findSimilarPaymentMethod("portfeuille");
            $methodPayements = $methodPayements->where('name', '!=', $portfeuille);
        }

        $methodPayements = $methodPayements->get();

        return response()->json(['data' => $methodPayements], 200);
    } catch (Exception $e) {
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
                $identity_profil_url = '';
                if ($request->hasFile('icone')) {
                    $images = $request->file('icone');
                    if(!isset($images[0])){
                        return (new ServiceController())->apiResponse(404, [], 'L\'image n\'a  pas été correctement envoyé.');
                    }
                    $image =$images[0];

                    $identity_profil_url = $this->fileService->uploadFiles($image, 'image/iconeMethodPayement', 'extensionImage');;
                    if ($identity_profil_url['fails']) {
                        return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
                    }
                    $methodPayement->icone = $identity_profil_url['result'];
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
        $methodPayement = MethodPayement::find($id);

        if (!$methodPayement) {
            return response()->json(['error' => 'Méthode de payementnon trouvé.'], 404);
        }
        $methodPayement->name = $request->name;
        $methodPayement->save();        return response()->json(['data' => 'Nom du Méthode de payement mise à jour avec succès.'], 200);
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
                $identity_profil_url = '';
                if ($request->hasFile('icone')) {
                    $images = $request->file('icone');
                    if(!isset($images[0])){
                        return (new ServiceController())->apiResponse(404, [], 'L\'image n\'a  pas été correctement envoyé.');
                    }
                    $image =$images[0];
                    $identity_profil_url = $this->fileService->uploadFiles($image, 'image/iconeMethodPayement', 'extensionImage');;
                    if ($identity_profil_url['fails']) {
                        return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
                    }
                    $methodPayement->icone = $identity_profil_url['result'];
                    $methodPayement->save();
                    return response()->json(['data' => 'icône du méthode de payement mis à jour avec succès.'], 200);
                } else {

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

        if($methodPayement->is_deleted == true){
            return response()->json(['error' => 'Méthode de payement déjà supprimé.'], 404);
        }

        $methodPayement->is_deleted = true;
        $methodPayement->save();

        return response()->json(['data' => 'Méthode de payement supprimé avec succès.'], 200);

    } catch(Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
    }

  }


  /**
 * @OA\Post(
 *     path="/api/methodPayement/active/{methodPayementId}",
 *     tags={"MethodPayement"},
 *     summary="Activer une méthode de paiement",
 *     description="Cette route permet d'activer une méthode de paiement.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="methodPayementId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Méthode de paiement activée avec succès",
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="La méthode de paiement spécifiée n'existe pas ou est déjà active",
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur"
 *     )
 * )
 */


  public function active($methodPayementId){
    try {
        $methodPayement = MethodPayement::find($methodPayementId);
        if (!$methodPayement) {
            return response()->json(['message' => 'La méthode de paiement spécifié n\'existe pas'], 404);
        }
        if($methodPayement->is_actif == true){
            return (new ServiceController())->apiResponse(404,[],'La méthode de paiement est déjà active');
        }
        $methodPayement->is_actif = true;
        $methodPayement->save();
        return (new ServiceController())->apiResponse(200,[],'La méthode de paiement activé avec succès');

    } catch (Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}

/**
 * @OA\Post(
 *     path="/api/methodPayement/desactive/{methodPayementId}",
 *     tags={"MethodPayement"},
 *     summary="Désactiver une méthode de paiement",
 *     description="Cette route permet de désactiver une méthode de paiement.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="methodPayementId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Méthode de paiement désactivée avec succès",
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="La méthode de paiement spécifiée n'existe pas ou est déjà inactive",
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur"
 *     )
 * )
 */

public function desactive($methodPayementId){
    try {
        $methodPayement = MethodPayement::find($methodPayementId);
        if (!$methodPayement) {
            return response()->json(['message' => 'La méthode de paiement spécifié n\'existe pas'], 404);
        }
        if($methodPayement->is_actif == false){
            return (new ServiceController())->apiResponse(404,[],'La méthode de paiement est déjà inactive');
        }
        $methodPayement->is_actif = false;
        $methodPayement->save();
        return (new ServiceController())->apiResponse(200,[],'La méthode de paiement a été désactivé  avec succès');

    } catch (Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}


/**
 * @OA\Get(
 *     path="/api/methodPayement/indexActive",
 *     tags={"MethodPayement"},
 *     summary="Liste des méthodes de paiement actives",
 *     description="Cette route permet de récupérer la liste des méthodes de paiement actives.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des méthodes de paiement actives",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur"
 *     )
 * )
 */
public function indexActive() {
    try {
        $methodPayements = MethodPayement::where('is_deleted', false)->where('is_actif', true)->get();
        return (new ServiceController())->apiResponse(200, $methodPayements, 'Liste des méthodes de paiement actives');
    } catch (Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
    }


     /**
     * @OA\Get(
     *     path="/api/methodPayement/indexInactive",
     *     tags={"MethodPayement"},
     *     summary="Liste des méthodes de paiement inactives",
     *     description="Cette route permet de récupérer la liste des méthodes de paiement inactives.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des méthodes de paiement inactives",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */

    public function indexInactive() {
        try {
            $promotions = MethodPayement::where('is_deleted', false)->where('is_actif', false)->get();
            return (new ServiceController())->apiResponse(200, $promotions, 'Liste des méthodes de paiement inactives');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


}
