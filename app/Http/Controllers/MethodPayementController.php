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
 *     path="/api/methodPayement/index",
 *     summary="Get all methodPayements based on acceptance and reception status",
 *     tags={"MethodPayement"},
 *     security={{"bearerAuth": {}}},
 * @OA\Parameter(
 *     name="is_accepted",
 *     in="query",
 *     required=false ,
 *     description="Defini si on veut voir les méthodes de paiement qui accepent les paiements externes",
 *     @OA\Schema(type="integer", example=1)
 *   ),
*@OA\Parameter(
 *     name="is_received",
 *     in="query",
 *     required=false ,
 *     description="Defini si on veut voir les méthodes de paiement qui accepent les opérations de retournement de fonds",
 *     @OA\Schema(type="integer", example=1)
 *   ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Liste des méthodes de paiement",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", description="ID de la méthode de paiement"),
 *                 @OA\Property(property="is_accepted", type="boolean", description="Indique si les paiements externes sont acceptés"),
 *                 @OA\Property(property="is_received", type="boolean", description="Indique si les retournements de fonds sont acceptés"),
 *                 @OA\Property(property="is_actif", type="boolean", description="Indique si la méthode est active"),
 *                 @OA\Property(property="is_deleted", type="boolean", description="Indique si la méthode est supprimée")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur"
 *     )
 * )
 */
  public function index(Request $request)
{

    try {
        $request->validate([
            'is_accepted' => 'required',
            'is_received' => 'required'
        ]);

        
        $is_accepted = $request->query('is_accepted',false);
        
        $is_received = $request->query('is_received',false);

        if ($is_accepted == true) {
            $methodPayements = MethodPayement::where('is_deleted', false)
                ->where('is_actif', true)
                ->with('servicePaiementactif')
                ->where('is_accepted', true)
                ->get()
                ->filter(function($methodPayement) {
                    
                    return $methodPayement->servicePaiement->contains(function($service) {
                        
                        return $service->is_actif == true;
                    });
                })
                ->map(function($methodPayement) {
                    return $methodPayement->makeHidden(['servicePaiement']);
                });

                $methodPayements [] = MethodPayement::whereName( (new ReservationController())->findSimilarPaymentMethod("portfeuille"))->first(); 

                // foreach($methodPayements as $methodPayement){
                //     $methodPayement->servicePaiement = (new ServicePaiementController())->showServiceActifByMethodPaiement($methodPayement->id);
                // }
        }
        

        if($is_received == true){
            $methodPayements = MethodPayement::where('is_deleted', false)
            ->where('is_actif', true)
            ->Where('is_received',true)
            ->get();
        }

        if($is_received == false && $is_accepted == false){
            $methodPayements = MethodPayement::where('is_deleted', false)
                ->where('is_actif', true)
                ->with('servicePaiementactif')
                ->where('is_accepted', true)
                ->get()
                ->filter(function($methodPayement) {
                    
                    return $methodPayement->servicePaiement->contains(function($service) {

                        return $service->is_actif == true;
                    });
                })
                ->map(function($methodPayement) {
                    return $methodPayement->makeHidden(['servicePaiement']);
                });
        }


        return response()->json(['data' => array_values($methodPayements->toArray())], 200);
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
 *  @OA\Property(property="is_accepted", type="string"),
 *  @OA\Property(property="is_received", type="string"),
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
                    'is_accepted' => '',
                    'is_received' => ''
                ]);

                if($request->is_accepted != 1 && $request->is_accepted != 0){
                    return (new ServiceController())->apiResponse(404, [], 'is_accepted doit être un 1 ou 0');
                }

                if($request->is_received != 1 && $request->is_received != 0){
                    return (new ServiceController())->apiResponse(404, [], 'is_received doit être un 1 ou 0');
                }
                $methodPayement = new MethodPayement();
                $identity_profil_url = '';
                if ($request->hasFile('icone')) {
                    $images = $request->file('icone');
                    if(!isset($images[0])){
                        return (new ServiceController())->apiResponse(404, [], 'L\'image n\'a  pas été correctement envoyé.');
                    }
                    $image =$images[0];

                    $identity_profil_url = $this->fileService->uploadFiles($image, 'image/iconeMethodPayement', 'extensionImage');

                    if ($identity_profil_url['fails']) {
                        return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
                    }
                    $methodPayement->icone = $identity_profil_url['result'];
                    }
                    $methodPayement->is_accepted = $request->is_accepted??0;
                    $methodPayement->is_received = $request->is_received??0;
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
        $methodPayement->save();
        return response()->json(['data' => 'Nom du Méthode de payement mise à jour avec succès.'], 200);
    } catch(Exception $e) {
        return response()->json($e->getMessage());
    }

  }

  /**
 * @OA\Post(
 *     path="/api/methodPayement/makeAccepted/{id}",
 *     tags={"MethodPayement"},
 * security={{"bearerAuth": {}}},
 *     summary="Accepter les paiements externes",
 *     description="Permet à une méthode de paiement d'accepter les paiements externes.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Méthode de paiement mise à jour."
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Méthode de payement non trouvé ou déjà acceptée."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur."
 *     )
 * )
 */

  public function makeAccepted($id){
    try{

        $methodPayement = MethodPayement::find($id);

        if (!$methodPayement) {
            return (new ServiceController())->apiResponse(404,[],'Méthode de payement non trouvé.');
        }

        if ($methodPayement->is_accepted == true) {
            return (new ServiceController())->apiResponse(404,[],'Cette méthode de paiement accepte déjà  les paiements externe.');
        }

        $methodPayement->is_accepted = true;
        $methodPayement->save();

        return (new ServiceController())->apiResponse(200,[],'Cette méthode de paiement peut maintenant accepter des paiements externes.');

    } catch(Exception $e) {
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
  }

  /**
 * @OA\Post(
 *     path="/api/methodPayement/makeNotAccepted/{id}",
 *     tags={"MethodPayement"},
 * security={{"bearerAuth": {}}},
 *     summary="Refuser les paiements externes",
 *     description="Permet à une méthode de paiement de refuser les paiements externes.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Méthode de paiement mise à jour."
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Méthode de payement non trouvé ou déjà refusée."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur."
 *     )
 * )
 */

  public function makeNotAccepted($id){
    try{
        $methodPayement = MethodPayement::find($id);

        if (!$methodPayement) {
            return (new ServiceController())->apiResponse(404,[],'Méthode de payement non trouvé.');
        }

        if ($methodPayement->is_accepted == false) {
            return (new ServiceController())->apiResponse(404,[],'Cette méthode de paiement refuse déjà  les paiements externe.');
        }

        $methodPayement->is_accepted = false;
        $methodPayement->save();

        return (new ServiceController())->apiResponse(200,[],'Cette méthode de paiement peut maintenant refuser des paiements externes.');
    } catch(Exception $e) {
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
  }


  /**
 * @OA\Post(
 *     path="/api/methodPayement/makeReceived/{id}",
 *     tags={"MethodPayement"},
 * security={{"bearerAuth": {}}},
 *     summary="Accepter le retour des fonds",
 *     description="Permet à une méthode de paiement d'accepter le retour des fonds.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Méthode de paiement mise à jour."
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Méthode de payement non trouvé ou déjà configurée pour retourner des fonds."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur."
 *     )
 * )
 */
  public function makeReceived($id){
    try{
        $methodPayement = MethodPayement::find($id);

        if (!$methodPayement) {
            return (new ServiceController())->apiResponse(404,[],'Méthode de payement non trouvé.');
        }

        if ($methodPayement->is_received == true) {
            return (new ServiceController())->apiResponse(404,[],'Cette méthode de paiement est déjà configurer pour retourner des fonds.');
        }

        $methodPayement->is_received = true;
        $methodPayement->save();

        return (new ServiceController())->apiResponse(200,[],'Cette méthode de paiement peut maintenant retourner des fonds.');
    } catch(Exception $e) {
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
  }


  /**
 * @OA\Post(
 *     path="/api/methodPayement/makeNotReceived/{id}",
 *     tags={"MethodPayement"},
 * security={{"bearerAuth": {}}},
 *     summary="Refuser le retour des fonds",
 *     description="Permet à une méthode de paiement de refuser le retour des fonds.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Méthode de paiement mise à jour."
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Méthode de payement non trouvé ou déjà configurée pour ne pas retourner les fonds."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur."
 *     )
 * )
 */
  public function makeNotReceived($id){
    try{
        $methodPayement = MethodPayement::find($id);

        if (!$methodPayement) {
            return (new ServiceController())->apiResponse(404,[],'Méthode de payement non trouvé.');
        }

        if ($methodPayement->is_received == false) {
            return (new ServiceController())->apiResponse(404,[],'Cette méthode de paiement est déjà configurer pour ne pas retourner les fonds.');
        }

        $methodPayement->is_received = false;
        $methodPayement->save();

        return (new ServiceController())->apiResponse(200,[],'Cette méthode de paiement peut maintenant refuser de retourner des fonds.');
    } catch(Exception $e) {
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
  }

    /**
 * @OA\Get(
 *     path="/api/methodPayement/getMethodPaiementWithAcceptedTrue",
 *     tags={"MethodPayement"},
 * security={{"bearerAuth": {}}},
 *     summary="Lister les méthodes de paiement acceptant les paiements externes",
 *     description="Retourne la liste des méthodes de paiement qui acceptent les paiements externes.",
 *     @OA\Response(
 *         response=200,
 *         description="Liste des méthodes de paiement."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur."
 *     )
 * )
 */
  public function getMethodPaiementWithAcceptedTrue(){
    try{
        $methodPayements = MethodPayement::where('is_deleted', false)
        ->where('is_actif', true)
        ->where('is_accepted',true)
        ->get();
        return (new ServiceController())->apiResponse(200,$methodPayements,'Liste des méthodes de paiement qui acceptent des paiement externes.');
    } catch(Exception $e) {
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
  }

  /**
 * @OA\Get(
 *     path="/api/methodPayement/getMethodPaiementWithAcceptedFalse",
 *     tags={"MethodPayement"},
 * security={{"bearerAuth": {}}},
 *     summary="Lister les méthodes de paiement refusant les paiements externes",
 *     description="Retourne la liste des méthodes de paiement qui refusent les paiements externes.",
 *     @OA\Response(
 *         response=200,
 *         description="Liste des méthodes de paiement."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur."
 *     )
 * )
 */

  public function getMethodPaiementWithAcceptedFalse(){
    try{
        $methodPayements = MethodPayement::where('is_deleted', false)
        ->where('is_actif', true)
        ->where('is_accepted',false)
        ->get();
        return (new ServiceController())->apiResponse(200,$methodPayements,'Liste des méthodes de paiement qui refusent les paiement externes.');
    } catch(Exception $e) {
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
  }

  /**
 * @OA\Get(
 *     path="/api/methodPayement/getMethodPaiementWithReceivedFalse",
 *     tags={"MethodPayement"},
 * security={{"bearerAuth": {}}},
 *     summary="Lister les méthodes de paiement refusant les retournements de fonds",
 *     description="Retourne la liste des méthodes de paiement qui refusent les retournements de fonds.",
 *     @OA\Response(
 *         response=200,
 *         description="Liste des méthodes de paiement."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur."
 *     )
 * )
 */

  public function getMethodPaiementWithReceivedFalse(){
    try{
        $methodPayements = MethodPayement::where('is_deleted', false)
        ->where('is_actif', true)
        ->where('is_received',false)
        ->get();
        return (new ServiceController())->apiResponse(200,$methodPayements,'Liste des méthodes de paiement qui refusent les retournements de fonds.');
    } catch(Exception $e) {
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
  }

    /**
 * @OA\Get(
 *     path="/api/methodPayement/getMethodPaiementWithReceivedTrue",
 *     tags={"MethodPayement"},
 * security={{"bearerAuth": {}}},
 *     summary="Lister les méthodes de paiement acceptant les retournements de fonds",
 *     description="Retourne la liste des méthodes de paiement qui acceptent les retournements de fonds.",
 *     @OA\Response(
 *         response=200,
 *         description="Liste des méthodes de paiement."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur."
 *     )
 * )
 */
  public function getMethodPaiementWithReceivedTrue(){
    try{
        $methodPayements = MethodPayement::where('is_deleted', false)
        ->where('is_actif', true)
        ->where('is_received',true)
        ->get();
        return (new ServiceController())->apiResponse(200,$methodPayements,'Liste des méthodes de paiement qui acceptent les retournements de fonds.');
    } catch(Exception $e) {
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());
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
