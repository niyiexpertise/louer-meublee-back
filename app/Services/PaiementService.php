<?php

namespace App\Services;

use App\Http\Controllers\KkiapayController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServicePaiementController;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use App\Models\FileStockage;
use App\Models\MethodPayement;
use Illuminate\Support\Facades\Schema;



class PaiementService
{
      /**
 * @OA\Post(
 *     path="/api/portefeuille/verifyTransactionOfMethod/{methodPaiement}/{transactionId}",
 *     tags={"Verification Paiement"},
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
        $mtn = 'mtn';

        $method_paiement = (new ReservationController())->findSimilarPaymentMethod($methodPaiement);

        $methodPaiement = MethodPayement::whereName($method_paiement)->first();

        if(!$methodPaiement){
            return (new ServiceController())->apiResponse(404, [], 'Méthode de paiement non trouvée.');
        }


        // if (!$method_paiement->is_actif) {
        //     return (new ServiceController())->apiResponse(404, [], 'Méthode de paiement non actif.');
        // }

        $servicePaiement = (new ServicePaiementController())->showServiceActifByMethodPaiement($methodPaiement->id,true);


        if( is_null($servicePaiement)){
            return [
                'status' => 'ERROR',
                'message' =>"Cette méthode de paiement n'a aucun service actif."
            ] ;
            // return (new ServiceController())->apiResponse(404, [], "Cette méthode de paiement n'a aucun service actif.");
        }


        $responseDataType = $servicePaiement->type;

        switch ($responseDataType) {
            case $kkiapay:
              return $this->getVerificationKkiapayStatus($transactionId);
            case $mtn:
                return $this->getVerificationMtnStatus($transactionId);
        }

    } catch (\Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}

    private function getVerificationKkiapayStatus($transactionId){
        $result = (new KkiapayController())->verifyTransaction($transactionId);

        // return $result;

        $validTransaction = isset($result->status)?true:false;

        if($validTransaction == false){
            return [
                'status' => 'ERROR',
                'transaction_id' => $transactionId,
                'message' =>'ID de transaction invalid. '.$transactionId
            ] ;
        }
        if($result->status == "SUCCESS"){
            return [
                'status' => 'SUCCESS',
                'transaction_id' => $transactionId,
                'message' =>$result->reason
            ] ;
        }else{
            return [
                'status' => 'FAILED',
                'transaction_id' => $transactionId,
                'message' =>$result->reason
            ] ;
        }
    }

    private function getVerificationMtnStatus($transactionId){
        
    }
}

