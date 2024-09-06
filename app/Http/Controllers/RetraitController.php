<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationEmail;
use App\Models\Notification;
use App\Models\Portfeuille;
use App\Models\portfeuille_transaction;
use App\Models\Retrait;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmailwithoutfile;
use App\Models\MethodPayement;
use Illuminate\Validation\Rule;
use App\Models\User_right;
use App\Models\Right;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
     *     summary="Liste des retraits effectué et accepté d'un utilisateur connecté",
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
     *     path="/api/retrait/ListRetraitOfUserPendingAuth",
     *     summary="Liste des retraits effectué mais en attente d'une réponse de l'admin par un utilisateur connecté",
     * description="Liste des retraits effectué par un utilisateur connecté",
     *     tags={"Retraits"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des retraits effectué par un utilisateur connecté"
     *     )
     * )
     */
    public function ListRetraitOfUserPendingAuth(){
        $retraits = Retrait::where('user_id', Auth::user()->id)->where('is_reject', false)->where('statut', false)->get();
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
 *                 ),
 *                  @OA\Property(
 *                     property="id_transaction",
 *                     description="id de la transaction",
 *                     type="string",example="hf45e"
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



 public function validateRetraitByAdmin(Request $request, $retraitId)
 {
     try {
         $retrait = Retrait::find($retraitId);
 
         if (!$retrait) {
             return (new ServiceController())->apiResponse(404, [], 'Retrait non trouvé');
         }
 
         if (!$request->id_transaction) {
             return (new ServiceController())->apiResponse(404, [], 'Le champ id_transaction est obligatoire');
         }
 
         $existTransaction = Portfeuille_transaction::where('id_transaction', $request->id_transaction)->exists();
         if ($existTransaction) {
             return (new ServiceController())->apiResponse(404, [], 'L\'id de la transaction existe déjà');
         }
 
         $montant_valid = $request->input('montant_valid');
 
         // Vérifiez que montant_valid est renseigné et valide
         if ($montant_valid !== null) {
             if ($montant_valid <= 0) {
                 return (new ServiceController())->apiResponse(404, [], 'Le montant doit être supérieur à 0');
             }
 
             if (!is_numeric($montant_valid)) {
                 return (new ServiceController())->apiResponse(404, [], 'Le montant doit être un nombre');
             }
 
             if ($montant_valid > $retrait->montant_reel) {
                 return (new ServiceController())->apiResponse(404, [], 'Vérifiez bien le montant, il ne doit pas dépasser la somme demandée qui est de '.$retrait->montant_reel." FCFA");
             }
 
             $montant = $montant_valid;
         } else {
             // Si montant_valid n'est pas renseigné, utilisez montant_reel
             $montant = $retrait->montant_reel;
         }
 
         if ($retrait->statut == 1) {
             return (new ServiceController())->apiResponse(404, [], 'Retrait déjà validé');
         }
 
         DB::beginTransaction();
 
         // Mettre à jour le retrait avec le montant validé
         $retrait->statut = 1;
         $retrait->montant_valid = $montant;
         $retrait->save();
 
         // Mettre à jour le solde du portefeuille
         $portfeuille = Portfeuille::where('user_id', $retrait->user_id)->first();
         $portfeuille->update(['solde' => $portfeuille->solde - $montant]);
 
         // Créer une nouvelle transaction
         $transaction = new Portfeuille_transaction();
         $transaction->credit = false;
         $transaction->debit = true;
         $transaction->amount = $montant;
         $transaction->valeur_commission = 0;
         $transaction->montant_commission = 0;
         $transaction->operation_type = 'debit';
         $transaction->portfeuille_id = $portfeuille->id;
         $transaction->retrait_id = $retrait->id;
         $transaction->id_transaction = $request->id_transaction;
         $transaction->payment_method = $retrait->payment_method;
         $transaction->motif = "Retrait portefeuille";
         $transaction->save();
 
         (new ReservationController())->initialisePortefeuilleTransaction($transaction->id);
         DB::commit();
 
         // Envoyer un email de confirmation
         $mail = [
             'title' => 'Confirmation de la demande de retrait',
             'body' => "Votre demande de retrait a été validée par l'administrateur. Le montant transféré est de {$montant} FCFA. Votre nouveau solde est de {$portfeuille->solde} FCFA."
         ];
 
         dispatch(new SendRegistrationEmail($retrait->user->email, $mail['body'], $mail['title'], 2));
 
         return (new ServiceController())->apiResponse(200, [], 'Retrait validé avec succès');
     } catch (Exception $e) {
         DB::rollback();
         return response()->json([
             'error' => 'Une erreur est survenue',
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
                    
                       $mail = [
                        'title' => 'Rejet de la demande de retrait',
                        'body' => "Votre demande de retrait a été rejeté pour le motif suivant <<".$request->input('motif').">>. Pour plus d'informations vous pouvez contactez l'administrateur"
                    ];

                     dispatch( new SendRegistrationEmail($retrait->user->email, $mail['body'], $mail['title'], 2));
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

          try {
            $validator = Validator::make($request->all(), [
                'payment_method' => 'required',
                'montant_reel' => 'required',
                'identifiant_payement_method' => 'required',
            ]);
            
    
            $message = [];
    
            if ($validator->fails()) {
                $message[] = $validator->errors();
                return (new ServiceController())->apiResponse(505,[],$message);
            }
    
            if(!is_numeric($request->input('montant_reel'))){
                return (new ServiceController())->apiResponse(404, [], 'Le montant doit être un entier');
            }
    
            if($request->input('montant_reel')<=0){
                return (new ServiceController())->apiResponse(404, [], 'Le montant doit être supérieur à 0 ');
            }
    
            $user = Auth::user();
            if($user->portfeuille->solde <$request->input('montant_reel')){
                return (new ServiceController())->apiResponse(404, [], 'solde insuffisant');
            }
    
            if(!is_null(Setting::first()->montant_minimum_retrait)){
                if($request->input('montant_reel')< Setting::first()->montant_minimum_retrait){
                    return (new ServiceController())->apiResponse(404, [], "Le montant minimum à retirer doit être supérieur à  ".Setting::first()->montant_minimum_retrait. " FCFA");
                }
            }

            if(!MethodPayement::whereName($request->payment_method)->where('is_deleted', false)->where('is_actif', true)->exists()){
                return  (new ServiceController())->apiResponse(404, [], 'Méthode de paiement non trouvé.');
            }
    
           if(!is_null(Setting::first()->montant_maximum_retrait)){
                if($request->input('montant_reel')> Setting::first()->montant_maximum_retrait){
                    return (new ServiceController())->apiResponse(404, [], "Le montant maximum à retirer doit être inférieur à  ".Setting::first()->montant_maximum_retrait. " FCFA");
                }
           }
    
           if(!is_null(Setting::first()->montant_minimum_solde_retrait)){
            if($user->portfeuille->solde <Setting::first()->montant_minimum_solde_retrait){
                return response()->json([
                    'message' => 'Vous devez avoir une somme d\'au moins '.Setting::first()->montant_minimum_solde_retrait.' avant de faire une demande de retrait'
                ]);
            }
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
    
                      
                           $mail = [
                            'title' => 'Demande de retrait',
                            'body' => "Votre demande de retrait a été pris en compte, patientez un moment pour recevoir le paiement."
                           ];
                        
                         dispatch( new SendRegistrationEmail($user->email, $mail['body'], $mail['title'], 2));
    
                         $right = Right::where('name', 'admin')->first();
                         $adminUsers = User_right::where('right_id', $right->id)->get();
                 
                         foreach ($adminUsers as $adminUser) {
                             
                 
                            $mail = [
                                'title' => 'Demande de retrait',
                                'body' => "Un utilisateur vient de soumettre une demande de retrait. Veuillez vous connecter rapidement pour la traiter."
                               ];
                             dispatch(new SendRegistrationEmail($adminUser->user->email, $mail['body'], $mail['title'], 2));
                 
                 
                         }
                         return (new ServiceController())->apiResponse(200,$retrait, 'save successfuly');
        
        } catch(Exception $e) {
             return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }

        
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



    /**
 * @OA\Get(
 *     path="/api/retrait/show/{id}",
 *     summary="Afficher les détails d'un retrait",
 *     description="Récupère les détails d'un retrait spécifique par ID.",
 *     operationId="showRetrait",
 *     tags={"Retraits"},
 *  security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID du retrait",
 *         required=true,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Détail du retrait récupéré avec succès.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="data", type="array", 
 *                 @OA\Items(type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="amount", type="number", format="float", example=150.50),
 *                     @OA\Property(property="status", type="string", example="validé"),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-21T10:00:00Z"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-21T10:00:00Z"),
 *                 )
 *             ),
 *             @OA\Property(property="message", type="string", example="Détail d'un retrait."),
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Retrait non trouvé.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="integer", example=404),
 *             @OA\Property(property="data", type="array", 
 *                 @OA\Items(type="object")
 *             ),
 *             @OA\Property(property="message", type="string", example="Retrait non trouvé."),
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Détails de l'erreur"),
 *         )
 *     )
 * )
 */

    public function show(string $id)
    {
        try{
            $retraits = Retrait::with('user')->find($id);

            if (!$retraits) {
                return (new ServiceController())->apiResponse(404, [], "Retrait non trouvé.");
            }

            return (new ServiceController())->apiResponse(404, [$retraits], "Détail d'un retrait.");
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

}