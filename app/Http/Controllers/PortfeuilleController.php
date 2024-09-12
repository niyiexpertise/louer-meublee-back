<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationEmail;
use App\Models\Portfeuille;
use Illuminate\Http\Request;
use App\Models\Charge;
use App\Models\Housing;
use App\Models\housing_preference;
use App\Models\reduction;
use App\Models\promotion;
use App\Models\Commission;
use App\Models\photo;
use App\Models\housing_price;
use App\Models\File;
use App\Models\Notification;
use App\Models\User;
use App\Models\Equipment;
use App\Models\Equipment_category;
use App\Models\Housing_equipment;
use App\Models\Housing_category_file;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File as F;
use App\Models\Category;
use App\Models\Housing_charge;
use App\Models\Reservation;
use App\Models\Payement;
use App\Models\Portfeuille_transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmailwithoutfile;
use App\Models\MethodPayement;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class PortfeuilleController extends Controller
{
    /**
 * @OA\Post(
 *     path="/api/portefeuille/credit",
 *     summary="Créditer le portefeuille d'un utilisateur",
 *     description="Crédite le portefeuille d'un utilisateur avec un certain montant et enregistre la transaction. Un utilisateur doit être authentifié pour utiliser cet endpoint.",
 *     tags={"Portefeuille"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données nécessaires pour créditer le portefeuille",
 *         @OA\JsonContent(
 *             required={"amount", "paiement_methode", "transaction_id"},
 *             @OA\Property(property="amount", type="number", format="double", description="Le montant à créditer", example=100.0),
 *             @OA\Property(property="paiement_methode", type="string", description="Méthode de paiement utilisée", example="carte"),
 *             @OA\Property(property="transaction_id", type="string", description="ID de transaction unique", example="12345-abcde"),
 *              @OA\Property(property="statut_paiement", type="boolean", description="statut du paiement", example="0"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Le portefeuille a été crédité avec succès.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", description="Message de succès", example="Le portefeuille a été crédité avec succès."),
 *             @OA\Property(property="solde", type="number", format="double", description="Solde actuel du portefeuille")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Données de requête invalides.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", description="Message d'erreur", example="Données de requête invalides."),
 *             @OA\Property(property="errors", type="object", description="Détails des erreurs")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Portefeuille non trouvé.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", description="Message d'erreur", example="Portefeuille non trouvé pour cet utilisateur.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Utilisateur non authentifié.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", description="Message d'erreur", example="Utilisateur non authentifié.")
 *         )
 *     )
 * )
 */

    public function creditPortfeuille(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0.01',
                'paiement_methode' => 'required|string',
                'transaction_id' => 'required|string',
                'statut_paiement' => 'nullable|boolean'
            ]);

            $message = [];

            if ($validator->fails()) {
                $message[] = $validator->errors();
                return (new ServiceController())->apiResponse(505, [], $message);
            }

            $userId = Auth::id();
            if (is_null($userId)) {
                return (new ServiceController())->apiResponse(404, [], 'Utilisateur non authentifié');
            }

            $amount = $request->input('amount');
            $portefeuille = Portfeuille::where('user_id', $userId)->first();

            if (!$portefeuille) {
                return (new ServiceController())->apiResponse(404, [], 'Portefeuille non trouvé pour cet utilisateur.');
            }

           $errorPayement = (new ServiceController())->validatePayement($request->amount,$request->paiement_methode,$request->transaction_id);

           if($errorPayement){
            return $errorPayement;
           }
           
           
           DB::beginTransaction();
           $statusPayement =  $request->statut_paiement;

            $status = $this->verifyTransactionOfMethod($request->paiement_methode,$request->transaction_id);

            if($status['status'] == 'ERROR'){
                return (new ServiceController())->apiResponse(404, [], 'ID de transaction invalid.');
            }

            if($status['status'] == 'FAILED'){
                $statusPayement = 0;
            }

            if($status['status'] == 'SUCCESS'){
                $statusPayement = 1;
            }

            

            $payement = new Payement();
            $payement->amount =  $amount;
            $payement->payment_method = $request->paiement_methode;
            $payement->id_transaction = $request->transaction_id;
            $payement->statut =$statusPayement;
            $payement->user_id = Auth::user()->id;
            $payement->is_confirmed = true;
            $payement->is_canceled = false;
          

            if( $statusPayement ==1){
                $portefeuille->solde += $amount;
                $portefeuille->save();
    
                $portefeuilleTransaction = new Portfeuille_transaction();
                $portefeuilleTransaction->credit = true;
                $portefeuilleTransaction->debit = false;
                $portefeuilleTransaction->operation_type = 'credit';
                $portefeuilleTransaction->amount = $amount;
                $portefeuilleTransaction->motif = "Recharge de portefeuille";
                $portefeuilleTransaction->portfeuille_id = $portefeuille->id;
                $portefeuilleTransaction->id_transaction = 0;
                $portefeuilleTransaction->payment_method = $request->input('paiement_methode');
                $portefeuilleTransaction->save();
                (new ReservationController())->initialisePortefeuilleTransaction($portefeuilleTransaction->id);
            }

            $payement->motif =  $portefeuilleTransaction->motif ?? "Echec de Payement lors de l'approvisionnement de son portefeuille";
            $payement->save();


           
            DB::commit();

            

            if( $statusPayement==1){
                $mail = [
                    "title" => "Confirmation de dépôt sur votre portefeuille",
                    "body" => "Votre portefeuille a été crédité de {$amount} FCFA. Nouveau solde : {$portefeuille->solde} FCFA"
                ];
    
                dispatch(new SendRegistrationEmail(User::find($userId)->email, $mail['body'], $mail['title'], 2));
                $data = ["solde" => $portefeuille->solde];
                return (new ServiceController())->apiResponse(200, $data, 'Le portefeuille a été crédité avec succès.');
            }else{
                return (new ServiceController())->apiResponse(404,[], "Echec de paiement");
            }

        } catch (Exception $e) {
            DB::rollBack();
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


    /**
 * @OA\Post(
 *     path="/api/portefeuille/verifyTransactionOfMethod/{methodPaiement}/{transactionId}",
 *     tags={"Portefeuille"},
 *     summary="Vérifie la transaction d'une méthode de paiement",
 *     description="Cette fonction permet de vérifier une transaction pour une méthode de paiement spécifique.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="methodPaiement",
 *         in="path",
 *         required=true,
 *         description="Le nom de la méthode de paiement à utiliser.",
 *         @OA\Schema(type="string", example="kkiapay")
 *     ),
 *     @OA\Parameter(
 *         name="transactionId",
 *         in="path",
 *         required=true,
 *         description="L'ID de la transaction à vérifier.",
 *         @OA\Schema(type="string", example="123456789")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="data", type="object", description="Détails de la transaction")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Méthode de paiement non trouvée",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Méthode de paiement non trouvée.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Service de paiement non supporté",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Service de paiement non supporté.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Message d'erreur détaillé")
 *         )
 *     )
 * )
 * 
 * @param string $methodPaiement Nom de la méthode de paiement
 * @param string $transactionId ID de la transaction
 * @return \Illuminate\Http\JsonResponse
 */
    public function verifyTransactionOfMethod($methodPaiement, $transactionId)
{
    try {
        $kkiapay = 'kkiapay';

        $method_paiement = (new ReservationController())->findSimilarPaymentMethod($methodPaiement);

        $methodPaiement = MethodPayement::whereName($method_paiement)->first();

        // if(!$methodPaiement){
        //     return (new ServiceController())->apiResponse(404, [], 'Méthode de paiement non trouvée.');
        // }


        // if (is_null($method_paiement)) {
        //     return (new ServiceController())->apiResponse(404, [], 'Méthode de paiement non trouvée.');
        // }

        $servicePaiement = (new ServicePaiementController())->showServiceActifByMethodPaiement($methodPaiement->id);


        $responseDataType = ($servicePaiement->original['data']->type);


        if ($responseDataType == $kkiapay) {
            $result = (new KkiapayController())->verifyTransaction($transactionId);

            $validTransaction = isset($result->status)?true:false;

            if($validTransaction == false){
                return [
                    'status' => 'ERROR',
                    'transaction_id' => $transactionId,
                    'message' =>'ID de transaction invalid.'
                ] ;
            }
            if($result->status == "SUCCESS"){
                return [
                    'status' => 'SUCCESS',
                    'transaction_id' => $transactionId,
                    'message' =>''
                ] ;
            }else{
                return [
                    'status' => 'FAILED',
                    'transaction_id' => $transactionId,
                    'message' =>''
                ] ;
            }
        } else {
            return (new ServiceController())->apiResponse(400, [], 'Service de paiement non supporté.');
        }

    } catch (\Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}



 }
