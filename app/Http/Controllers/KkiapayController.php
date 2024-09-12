<?php

namespace App\Http\Controllers;

use App\Models\ServicePaiement;
use Illuminate\Http\Request;

class KkiapayController extends Controller
{

    /**
 * @OA\Post(
 *     path="/api/kkiapay/verifyTransaction/{transaction_id}",
 *     summary="Vérifie une transaction avec Kkiapay",
 *     tags={"Kkiapay"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="transaction_id",
 *         in="path",
 *         description="L'identifiant de la transaction à vérifier",
 *         required=true,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Réponse de vérification de la transaction",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 type="string",
 *                 example="success"
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 description="Les détails de la vérification de la transaction"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="error",
 *                 type="string",
 *                 example="Message d'erreur"
 *             )
 *         )
 *     )
 * )
 */
    public function verifyTransaction($transaction_id){
        try {
            $services = ServicePaiement::all();

            $target = 'kkiapay';
            $closestService = null;
            $highestSimilarity = 0;

            foreach ($services as $service) {
                similar_text($service->type, $target, $percent);
                if ($percent >= 80 && $percent > $highestSimilarity) {
                    $highestSimilarity = $percent;
                    $closestService = $service;
                }
            }

            $serviceKKiapay = $closestService;

            $public_key =$serviceKKiapay->public_key;
            $private_key =$serviceKKiapay->private_key;
            $secret =$serviceKKiapay->secret;
            $sandbox = $serviceKKiapay->is_sandbox ? true : false;


            $kkiapay = new \Kkiapay\Kkiapay($public_key,
            $private_key,
            $secret,
            $sandbox=$sandbox);
            $verification = $kkiapay->verifyTransaction($transaction_id);

            return $verification;

        } catch(\Exception $e) {
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }
}
