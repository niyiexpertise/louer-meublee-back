<?php

namespace App\Http\Controllers;

use App\Models\MethodPayement;
use App\Models\MoyenPayement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MoyenPayementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
/**
 * @OA\Get(
 *     path="/api/moyenPayement/ListeMoyenPayement",
 *     summary="liste de de tous les moyens de payements non bloqué et non supprimé",
 *     tags={"MoyenPayement"},
 * security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items()
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent()
 *     )
 * )
 */
public function ListeMoyenPayement()
    {
        try {
            // Récupérer tous les moyens de paiement non bloqués et non supprimés
            $moyenPayement = MoyenPayement::with(['user', 'methodPayement'])
                ->where('is_blocked', 0)
                ->where('is_deleted', 0)
                ->get();
            
            $data = $moyenPayement->map(function ($item) {
                return [
                    'moyen_payement_id' => $item->id,
                    'user_id' => $item->user->id,
                    'user_detail' => $item->user,
                    
                    'method_payement_id' => $item->methodPayement->id,
                    'method_payement_name' => $item->methodPayement->name,
                    'method_payement_icone' => $item->methodPayement->icone,
                    'valeur_method_payement' => $item->valeur_method_payement,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });
            
            return response()->json([
                'data' => $data,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }



/**
 * @OA\Get(
 *     path="/api/moyenPayement/ListeMoyenPayementUserAuth",
 *     tags={"MoyenPayement"},
 *     summary="Liste des moyens de paiement de l'utilisateur authentifié",
 *     description="Cette fonction permet de récupérer la liste des moyens de paiement de l'utilisateur authentifié.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des moyens de paiement récupérée avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(),
 *             ),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="error",
 *                 type="string",
 *                 example="An error occurred"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Message d'erreur spécifique"
 *             )
 *         )
 *     )
 * )
 */

public function ListeMoyenPayementUserAuth()
{
    {
        try {
            // Récupérer tous les moyens de paiement non bloqués et non supprimés
            $moyenPayement = MoyenPayement::with(['user', 'methodPayement'])
                ->where('is_blocked', 0)
                ->where('is_deleted', 0)
                ->where('user_id', Auth::user()->id)
                ->get();

               
            $data = $moyenPayement->map(function ($item) {
                return [
                    'moyen_payement_id' => $item->id,
                    'user_id' => $item->user->id,
                    'user_detail' => $item->user,
                    
                    'method_payement_id' => $item->methodPayement->id,
                    'method_payement_name' => $item->methodPayement->name,
                    'method_payement_icone' => $item->methodPayement->icone,
                    'valeur_method_payement' => $item->valeur_method_payement,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });
            
            return response()->json([
                'data' => $data,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}

/**
 * @OA\Get(
 *     path="/api/moyenPayement/ListeMoyenPayementBlocked",
 *     summary="Liste des moyens de payements bloqué",
 *     tags={"MoyenPayement"},
 * security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items()
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent()
 *     )
 * )
 */
    public function ListeMoyenPayementBlocked()
    {
         
        try {
            // Récupérer tous les moyens de paiement non bloqués et non supprimés
            $moyenPayement = MoyenPayement::with(['user', 'methodPayement'])
                ->where('is_blocked', 1)
                ->where('is_deleted', 0)
                ->get();
            
            $data = $moyenPayement->map(function ($item) {
                return [
                    'moyen_payement_id' => $item->id,
                    'user_id' => $item->user->id,
                    'user_detail' => $item->user,
                    
                    'method_payement_id' => $item->methodPayement->id,
                    'method_payement_name' => $item->methodPayement->name,
                    'method_payement_icone' => $item->methodPayement->icone,
                    'valeur_method_payement' => $item->valeur_method_payement,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });
            
            return response()->json([
                'data' => $data,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
 * Liste des moyens de paiement supprimés.
 *
 * @return \Illuminate\Http\JsonResponse
 *
 * @OA\Get(
 *      path="/api/moyenPayement/ListeMoyenPayementDeleted",
 *      operationId="ListeMoyenPayementDeleted",
 *      tags={"MoyenPayement"},
 * security={{"bearerAuth": {}}},
 *      summary="Liste des moyens de paiement supprimés",
 *      description="Récupère la liste des moyens de paiement qui ont été supprimés mais non bloqués.",
 *      @OA\Response(
 *          response=200,
 *          description="Liste des moyens de paiement supprimés récupérée avec succès",
 *          @OA\JsonContent(
 *              type="array",
 *              @OA\Items(),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=500,
 *          description="Erreur interne du serveur",
 *          @OA\JsonContent(
 *              @OA\Property(property="error", type="string", example="An error occurred"),
 *              @OA\Property(property="message", type="string", example="Message d'erreur spécifique"),
 *          ),
 *      ),
 * )
 */

    public function ListeMoyenPayementDeleted()
    {
         
        try {
            // Récupérer tous les moyens de paiement non bloqués et non supprimés
            $moyenPayement = MoyenPayement::with(['user', 'methodPayement'])
                ->where('is_blocked', 0)
                ->where('is_deleted', 1)
                ->get();
            
            $data = $moyenPayement->map(function ($item) {
                return [
                    'moyen_payement_id' => $item->id,
                    'user_id' => $item->user->id,
                    'user_detail' => $item->user,

                    'method_payement_id' => $item->methodPayement->id,
                    'method_payement_name' => $item->methodPayement->name,
                    'method_payement_icone' => $item->methodPayement->icone,
                    'valeur_method_payement' => $item->valeur_method_payement,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });
            
            return response()->json([
                'data' => $data,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


/**
 * Enregistrer un nouveau moyen de paiement.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\JsonResponse
 *
 * @OA\Post(
 *      path="/api/moyenPayement/store",
 *      operationId="storeMoyenPayement",
 *      tags={"MoyenPayement"},
 *      summary="Enregistrer un nouveau moyen de paiement",
 *      description="Cette fonction permet à un utilisateur d'enregistrer un nouveau moyen de paiement.",
 *      security={{"bearerAuth": {}}},
 *      @OA\RequestBody(
 *          required=true,
 *          description="Données du nouveau moyen de paiement",
 *          @OA\JsonContent(
 *              required={"user_id", "method_payement_id", "valeur_method_payement"},
 *              @OA\Property(property="method_payement_id", type="integer", description="ID du moyen de paiement"),
 *              @OA\Property(property="valeur_method_payement", type="string", description="Valeur du moyen de paiement"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Nouveau moyen de paiement enregistré avec succès",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Moyen de paiement enregistré avec succès"),
 *              @OA\Property(property="moyen_paiement", ),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Erreur de validation des données",
 *          @OA\JsonContent(
 *              @OA\Property(property="error", type="string", example="Validation failed"),
 *              @OA\Property(property="message", type="string", example="Message d'erreur spécifique"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=500,
 *          description="Erreur interne du serveur",
 *          @OA\JsonContent(
 *              @OA\Property(property="error", type="string", example="An error occurred"),
 *              @OA\Property(property="message", type="string", example="Message d'erreur spécifique"),
 *          ),
 *      ),
 * )
 */

    public function store(Request $request)
    {
          try{

            $request->validate([
                'method_payement_id' => 'required',
                'valeur_method_payement' => 'required',
            ]);

            if(!MethodPayement::find($request->method_payement_id)){
                return (new ServiceController())->apiResponse(404, [], 'Méthode de paiement non trouvé');
            }

            $exist = MoyenPayement::where('method_payement_id', $request->method_payement_id)
            ->where('valeur_method_payement', $request->valeur_method_payement)
            ->exists();
            if ($exist) {
                return response()->json([
                    "error" =>" la valeur du moyen de payement doit être unique par moyen de payement",
                ],200);
            }
                $moyenPayement = new MoyenPayement();
                $moyenPayement->user_id = Auth::user()->id;
                $moyenPayement->method_payement_id = $request->method_payement_id;
                $moyenPayement->valeur_method_payement = $request->valeur_method_payement;
                $moyenPayement->save();
                return response()->json(['message' =>'moyen de payement enregistré avec succcès']);
            } catch(Exception $e) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 500);
            }
     
    
    }
/**
 * @OA\Get(
 *     path="/api/moyenPayement/show/{idMoyenPayement}",
 *     operationId="showMoyenPayement",
 *     tags={"MoyenPayement"},
 *     summary="Afficher les détails d'un moyen de paiement",
 *     description="Cette fonction permet à un utilisateur d'afficher les détails d'un moyen de paiement spécifique.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="idMoyenPayement",
 *         in="path",
 *         required=true,
 *         description="ID du moyen de paiement à afficher",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Détails du moyen de paiement récupérés avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", ),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Moyen de paiement non trouvé",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Moyen de paiement non trouvé"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="An error occurred"),
 *             @OA\Property(property="message", type="string", example="Message d'erreur spécifique"),
 *         ),
 *     ),
 * )
 */

    public function show( $idMoyenPayement)
    {

          try{

            $moyenPayement = MoyenPayement::find($idMoyenPayement);

            if (!$moyenPayement) {
               return response()->json([
                'message ' => 'moyenPayement not found'
               ],404);
            }

        return response()->json([
            'data' => $moyenPayement
        ], 200);
            } catch(Exception $e) {
                return response()->json([
                    'error' => 'An error occurred',
                    'message' => $e->getMessage()
                ], 500);
            }
    }

/**
 * Mettre à jour un moyen de paiement existant.
 *
 * @param  int  $idMoyenPayement
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\JsonResponse
 *
 * @OA\Put(
 *      path="/api/moyenPayement/update/{idMoyenPayement}",
 *  tags={"MoyenPayement"},
 *   security={{"bearerAuth": {}}},
 *     summary="Modification des moyens de payement",
 *      @OA\Parameter(
 *          name="idMoyenPayement",
 *          in="path",
 *          required=true,
 *          description="ID du moyen de paiement à mettre à jour",
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          description="Nouvelles données du moyen de paiement",
 *          @OA\JsonContent(
 *              required={"method_payement_id", "valeur_method_payement"},
 *              @OA\Property(property="method_payement_id", type="integer", description="ID de la méthode de paiement"),
 *              @OA\Property(property="valeur_method_payement", type="string", description="Valeur du moyen de paiement"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Moyen de paiement mis à jour avec succès",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Moyen de paiement mis à jour avec succès"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Moyen de paiement non trouvé",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Moyen de paiement non trouvé"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=500,
 *          description="Erreur interne du serveur",
 *          @OA\JsonContent(
 *              @OA\Property(property="error", type="string", example="An error occurred"),
 *              @OA\Property(property="message", type="string", example="Message d'erreur spécifique"),
 *          ),
 *      ),
 * )
 */

    public function update(Request $request,  $idMoyenPayement)
    {
            try{

            $moyenPayement = MoyenPayement::find($idMoyenPayement);

            if (!$moyenPayement) {
               return response()->json([
                'message ' => 'moyenPayement not found'
               ],404);
            }
                $data = $request->validate([
                    'method_payement_id' => 'required',
                    'valeur_method_payement' => 'required',
                ]);
                $exist = MoyenPayement::where('method_payement_id', $request->method_payement_id)
                ->where('valeur_method_payement', $request->valeur_method_payement)
                ->exists();
                if ($exist) {
                    return response()->json([
                        "message" =>" la valeur du moyen de payement doit être unique par moyen de payement",
                    ],200);
                }
                MoyenPayement::whereId($idMoyenPayement)->where('user_id', Auth::user()->id)->update($data);
                return response()->json(['message' => 'modifié avec succès'], 200);
;            } catch(Exception $e) {
                return response()->json([
                    'error' => 'An error occurred',
                    'message' => $e->getMessage()
                ], 500);
            }
    }

 /**
 * Supprimer un moyen de paiement.
 *
 * @param  int  $idMoyenPayement
 * @return \Illuminate\Http\JsonResponse
 *
 * @OA\Delete(
 *      path="/api/moyenPayement/destroy/{idMoyenPayement}",
 *      operationId="deleteMoyenPayement",
 *      tags={"MoyenPayement"},
 *      summary="Supprimer un moyen de paiement",
 *      description="Cette fonction permet à un utilisateur de supprimer un moyen de paiement spécifique.",
 *      security={{"bearerAuth": {}}},
 *      @OA\Parameter(
 *          name="idMoyenPayement",
 *          in="path",
 *          required=true,
 *          description="ID du moyen de paiement à supprimer",
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Moyen de paiement supprimé avec succès",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Moyen de paiement supprimé avec succès"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Moyen de paiement non trouvé",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Moyen de paiement non trouvé"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=500,
 *          description="Erreur interne du serveur",
 *          @OA\JsonContent(
 *              @OA\Property(property="error", type="string", example="An error occurred"),
 *              @OA\Property(property="message", type="string", example="Message d'erreur spécifique"),
 *          ),
 *      ),
 * )
 */


    public function destroy( $idMoyenPayement)
    {
           try{
            $moyenPayement = moyenPayement::find($idMoyenPayement);
            if (!$moyenPayement) {
                return response()->json([
                 'message ' => 'moyenPayement not found'
                ],404);
             }
             if(Auth::user()->id != $moyenPayement->user_id){
                return (new ServiceController())->apiResponse(404, [], "Vous n'êtes pas autorisé à effectuer cette action");
             }
             MoyenPayement::whereId($idMoyenPayement)->update(['is_deleted' => true]);
            } catch(Exception $e) {
                return response()->json([
                    'error' =>$e->getMessage()
                ], 500);
            }
    }

    /**
 * @OA\Put(
 *     path="/api/moyenPayement/block/{idMoyenPayement}",
 *     tags={"MoyenPayement"},
 *     summary="Bloquer un moyen de paiement",
 *     description="Cette fonction permet de bloquer un moyen de paiement.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="idMoyenPayement",
 *         in="path",
 *         description="ID du moyen de paiement à bloquer",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Moyen de paiement bloqué avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Moyen de paiement bloqué avec succès"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Moyen de paiement non trouvé",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Moyen de paiement non trouvé"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="error",
 *                 type="string",
 *                 example="An error occurred"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Message d'erreur spécifique"
 *             )
 *         )
 *     )
 * )
 */

    public function block($idMoyenPayement)
    {
           try{
            $moyenPayement = moyenPayement::find($idMoyenPayement);
            if (!$moyenPayement) {
                return response()->json([
                 'message ' => 'moyenPayement not found'
                ],404);
             }
             MoyenPayement::whereId($idMoyenPayement)->update(['is_blocked' => true]);
            } catch(Exception $e) {
                return response()->json([
                    'error' => 'An error occurred',
                    'message' => $e->getMessage()
                ], 500);
            }
    }


    /**
 * @OA\Put(
 *     path="/api/moyenPayement/unblock/{idMoyenPayement}",
 *     tags={"MoyenPayement"},
 *     summary="Débloquer un moyen de paiement",
 *     description="Cette fonction permet de débloquer un moyen de paiement.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="idMoyenPayement",
 *         in="path",
 *         description="ID du moyen de paiement à débloquer",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Moyen de paiement débloqué avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Moyen de paiement débloqué avec succès"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Moyen de paiement non trouvé",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Moyen de paiement non trouvé"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="error",
 *                 type="string",
 *                 example="An error occurred"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Message d'erreur spécifique"
 *             )
 *         )
 *     )
 * )
 */

    public function unblock($idMoyenPayement)
    {
           try{
            $moyenPayement = moyenPayement::find($idMoyenPayement);
            if (!$moyenPayement) {
                return response()->json([
                 'message ' => 'moyenPayement not found'
                ],404);
             }
             MoyenPayement::whereId($idMoyenPayement)->update(['is_blocked' => false]);
            } catch(Exception $e) {
                return response()->json([
                    'error' => 'An error occurred',
                    'message' => $e->getMessage()
                ], 500);
            }
    }
}