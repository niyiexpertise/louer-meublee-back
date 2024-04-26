<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Portfeuille;
use App\Models\portfeuille_transaction;
use App\Models\Retrait;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RetraitController extends Controller
{
                         /**
     * @OA\Get(
     *     path="/api/retrait/ListRetraitRejectForAdmin",
     *     summary="Liste des retraits rejetés sur la  plateforme(admin)",
     * description="Liste des retraits rejetés sur la  plateforme(admin)",
     *     tags={"Retraits"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des retraits rejetés sur la  plateforme(admin)"
     *     )
     * )
     */
    public function ListRetraitRejectForAdmin()
    {
        $retraits = Retrait::where('is_reject', true)->with('user')->get();
        return response()->json([
            'data' => $retraits
        ], 200);
    }


    /**
     * @OA\Get(
     *     path="/api/retrait/ListRetraitWaitingConfirmationByAdmin",
     *     summary="Liste des retrait en attente de validation sur la plateforme(admin)",
     * description="Liste des retrait en attente de validation sur la plateforme(admin)",
     *     tags={"Retraits"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des retrait en attente de validation sur la plateforme(admin)"
     *     )
     * )
     */
    public function ListRetraitWaitingConfirmationByAdmin()
    {
        $retraits = Retrait::where('statut', false)->where('is_reject',false)->with('user')->get();
        return response()->json([
            'data' => $retraits
        ], 200);
    }

                             /**
     * @OA\Get(
     *     path="/api/retrait/ListRetraitConfirmedByAdmin",
     *     summary="Liste des retraits déjà confirmé par l'admin la plateforme(admin)",
     * description="Liste des retraits déjà confirmé par l'admin la plateforme(admin)",
     *     tags={"Retraits"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des retraits déjà confirmé par l'admin la plateforme(admin)"
     *     )
     * )
     */

    public function ListRetraitConfirmedByAdmin()
    {
        $retraits = Retrait::where('statut', true)->with('user')->get();
        return response()->json([
            'data' => $retraits
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/retrait/ListRetraitOfUserAuth",
     *     summary="Liste des retraits effectué par un utilisateur connecté",
     * description="Liste des retraits effectué par un utilisateur connecté",
     *     tags={"Retraits"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des retraits effectué par un utilisateur connecté"
     *     )
     * )
     */
    public function ListRetraitOfUserAuth(){
        $retraits = Retrait::where('user_id', Auth::user()->id)->where('is_reject', false)->where('statut', true)->get();
        return response()->json([
            'data' => $retraits
        ], 200);
    }

        /**
     * @OA\Get(
     *     path="/api/retrait/ListRetraitRejectOfUserAuth",
     *     summary="Liste des retraits effectué par un utilisateur connecté mais rejeté par l'administrateur",
     * description="Liste des retraits effectué par un utilisateur connecté mais rejeté par l'administrateur",
     *     tags={"Retraits"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des retraits effectué par un utilisateur connecté"
     *     )
     * )
     */
    public function ListRetraitRejectOfUserAuth(){
        $retraits = Retrait::where('user_id', Auth::user()->id)->where('is_reject', true)->where('statut', false)->get();
        return response()->json([
            'data' => $retraits
        ], 200);
    }


 /**
 * @OA\Put(
 *     path="/api/retrait/validateRetraitByAdmin/{retraitId}",
 *     tags={"Retraits"},
 *     security={{"bearerAuth": {}}},
 *     summary="Validation d'une demande de retrait par l'admin",
 *     @OA\Parameter(
 *         name="retraitId",
 *         in="path",
 *         description="ID du retrait à valider",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="montant_valid",
 *                     description="Montant validé pour le retrait",
 *                     type="integer"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful validation of withdrawal",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Retraits successfully validated"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request or validation error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="error",
 *                 type="string"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Withdrawal not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="error",
 *                 type="string"
 *             )
 *         )
 *     )
 * )
 */



    public function validateRetraitByAdmin(Request $request, $retraitId){

        
        try{
            $retrait = Retrait::find($retraitId);
           

            if (!$retrait) {
                return response()->json([
                    'error' => 'Retrait not found for id ' 
                ], 404);
            }

            $montant = $request->input('montant_valid');

            if(empty($request->input('montant_valid'))){
                $montant = $retrait->montant_reel;
             }

            //  return response()->json([
            //     'valid' =>     $montant,
            //     'reel' =>     $retrait->montant_reel,
            //     'valid > reel' => ( $montant > $retrait->montant_reel )
            // ]);

            if ($retrait->statut == 1) {
                return response()->json([
                    'error' => 'Retrait already validated ' 
                ]);
            }

            if( $montant > $retrait->montant_reel ){
                return response()->json([
                    'error' => 'Vérifier bien le montant, il ne doit pas dépasser la somme demandé '
                ]);
            }

                $retrait->update(['statut' => 1]);

                $retrait->update(['montant_valid' => $montant]);

                $portfeuille = Portfeuille::where('user_id',$retrait->user_id)->first();
                $portfeuille->update(['solde' => $portfeuille->solde - $montant]);

                $transaction = new portfeuille_transaction();
                $transaction->portfeuille_id = $portfeuille->id;
                $transaction->amount = $montant;
                $transaction->debit = 1;
                $transaction->credit =0;
                $transaction->id_transaction = $retrait->identifiant_payement_method;
                $transaction->payment_method = $retrait->payment_method;
                $transaction->motif = "Demande de retrait";
                $transaction->save();

                $notification = new Notification();
                $notification->user_id = $retrait->user_id;
                $notification->name = 'Votre demande de retrait a été validé par l\'administrateur,  montant transféré: '.$montant.' fcfa. Votre solde est de '.$portfeuille->solde.' fcfa';
                $notification->save();

            return response()->json([
                'message' => 'Retraits successfully validated',
            ]);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ], 500);
        }

            

        }

        /**
 * Rejeter un retrait par un administrateur.
 *
 * @param {integer} $retraitId - L'identifiant du retrait à rejeter
 * @param {object} $request.body.required - Les données de la requête
 * @param {string} $request.body.motif.required - Le motif du blocage
 * @return {object} 200 - Le message de confirmation du blocage du retrait
 * @return {object} 404 - Retrait non trouvé
 * @return {object} 409 - Retrait déjà validé ou déjà rejeté
 * @return {object} 500 - Erreur serveur
 *
 * @OA\Put(
 *     path="/api/retrait/rejectRetraitByAdmin/{retraitId}",
 *     tags={"Retraits"},
 *     summary="Rejet d'un retrait par un administrateur",
 *     description="Cette fonction permet à un administrateur de rejeter un retrait en spécifiant un motif de blocage.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="retraitId",
 *         in="path",
 *         description="L'identifiant du retrait à rejeter",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données de la requête",
 *         @OA\JsonContent(
 *             required={"motif"},
 *             @OA\Property(property="motif", type="string", description="Le motif du blocage")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Le retrait a été rejeté avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Retrait successfully rejected")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Retrait non trouvé",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Retrait not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=409,
 *         description="Retrait déjà validé ou déjà rejeté",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Retrait already validated or already rejected")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="An error occurred")
 *         )
 *     )
 * )
 */


        public function rejectRetraitByAdmin(Request $request, $retraitId)
        {

            // try{
          
            // } catch(Exception $e) {
            //     return response()->json([
            //         'error' => 'An error occurred',
            //         'message' => $e->getMessage()
            //     ], 500);
            // }

            
            try{
                $retrait = Retrait::find($retraitId);
        
                if (!$retrait) {
                    return response()->json([
                        'error' => 'Retrait not found '
                    ], 404);
                }

                if ($retrait->statut == 1) {
                    return response()->json([
                        'error' => 'Retrait already validated'
                    ]);
                }

                if ($retrait->is_reject == 1) {
                    return response()->json([
                        'error' => 'Retrait already rejected'
                    ]);
                }

                $request->validate([
                    'motif' => 'required|string',
                ]);

                if(Retrait::whereId( $retraitId)->update([
                    'is_reject' => 1,
                    'motif' => $request->input('motif')
                ])){
                    $notification = new Notification([
                        'name' => "Votre demande de retrait a été rejeté pour le motif suivant <<".$request->input('motif').">>. Pour plus d'informations vous pouvez contactez l'administrateur",
                        'user_id' => $retrait->user_id,
                       ]);
                       $notification->save();
                    return response()->json([
                    'message' => 'Retrait successfully rejected',
                 ]);
                }
            } catch(Exception $e) {
                return response()->json([
                    'error' => 'An error occurred',
                    'message' => $e->getMessage()
                ], 500);
            }

        }


            /**
 * Enregistrer une demande de retrait.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\JsonResponse
 *
 * @OA\Post(
 *      path="/api/retrait/store",
 *      operationId="storeRetrait",
 *      tags={"Retraits"},
 *      security={{"bearerAuth": {}}},
 *      summary="Enregistrer une demande de retrait",
 *      description="Cette fonction permet à un utilisateur d'enregistrer une demande de retrait.",
 *      @OA\RequestBody(
 *          required=true,
 *          description="Données de la demande de retrait",
 *          @OA\JsonContent(
 *              required={"payment_method", "montant_reel", "identifiant_payement_method"},
 *              @OA\Property(property="payment_method", type="string", description="Méthode de paiement utilisée pour le retrait", example="momo"),
 *              @OA\Property(property="montant_reel", type="number", format="float", description="Montant réel à retirer", example=100.00),
 *              @OA\Property(property="identifiant_payement_method", type="string", description="Identifiant de la méthode de paiement", example="742876298"),
 *              @OA\Property(property="libelle", type="string", description="Libellé de la demande de retrait", example="Demande de retrait de fonds"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Demande de retrait enregistrée avec succès",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Demande de retrait enregistrée avec succès"),
 *              @OA\Property(property="date", type="object", ref=""),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Solde insuffisant",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Solde insuffisant"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Non authentifié",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Non authentifié"),
 *          ),
 *      ),
 * )
 */


    public function store(Request $request)
    {
         $request->validate([
            'payment_method' => 'required',
            'montant_reel' => 'required',
            'identifiant_payement_method' => 'required',
        ]);

        $user = Auth::user();
        if($user->portfeuille->solde <$request->input('montant_reel')){
            return response()->json([
                'message' => 'solde insuffisant'
            ]);

        }
        $retrait = new Retrait();
        $roles = User::find($user->id)->getRoleNames('0');
        $retrait->user_id = $user->id ;
        $retrait->payment_method = $request->payment_method;
        $retrait->montant_reel = $request->montant_reel;
        $retrait->montant_valid = $request->montant_reel;
        $retrait->libelle = $request->libelle;
        $retrait->user_role = $roles[0] ;
        $retrait->identifiant_payement_method = $request->identifiant_payement_method;
        $retrait->save();

        $userId = Auth::id();
                    $notification = new Notification([
                        'name' => "Votre demande de retrait a été pris en compte, patientez un moment pour recevoir le paiement",
                        'user_id' => $userId,
                       ]);
                       $notification->save();
                     $adminUsers = User::where('is_admin', 1)->get();
                            foreach ($adminUsers as $adminUser) {
                                $notification = new Notification();
                                $notification->user_id = $adminUser->id;
                                $notification->name = "Un utilisateur  vient d'enregistrer une demande de retrait.";
                                $notification->save();
                            }
        return response()->json([
            'message' => 'save successfuly',
            'date' => $retrait
        ], 200);
    }

     /**
     * @OA\Get(
     *     path="/api/retrait/ListRetraitOfTravelerWaitingConfirmationByAdmin",
     *     summary="Liste des demandes retrait des voyageurs en attente de validation sur la plateforme(admin)",
     * description="Liste des demandes retrait des voyageurs en attente de validation sur la plateforme(admin)",
     *     tags={"Retraits"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des demandes retrait des voyageurs en attente de validation sur la plateforme(admin)"
     *     )
     * )
     */
    public function ListRetraitOfTravelerWaitingConfirmationByAdmin()
    {
        $retraits = Retrait::where('statut', false)->where('is_reject',false)->where('user_role','traveler')->with('user')->get();
        return response()->json([
            'data' => [
                'liste' =>$retraits,
                'nombre_demande' =>$retraits->count()
            ]
        ], 200);
    }

         /**
     * @OA\Get(
     *     path="/api/retrait/ListRetraitOfHoteWaitingConfirmationByAdmin",
     *     summary="Liste des demandes retrait des hotes en attente de validation sur la plateforme(admin)",
     * description="Liste des demandes retrait des hotes en attente de validation sur la plateforme(admin)",
     *     tags={"Retraits"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des demandes retrait des hotes en attente de validation sur la plateforme(admin)"
     *     )
     * )
     */
    public function ListRetraitOfHoteWaitingConfirmationByAdmin()
    {
        $retraits = Retrait::where('statut', false)->where('is_reject',false)->where('user_role', 'hote')->with('user')->get();
        return response()->json([
            'data' => [
                'liste' =>$retraits,
                'nombre_demande' =>$retraits->count()
            ]
        ], 200);
    }

}
