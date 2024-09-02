<?php

namespace App\Http\Controllers;

use App\Models\promotion;
use Exception;
use Illuminate\Http\Request;

class AdminPromotionController extends Controller
{

    /**
 * @OA\Post(
 *     path="/api/promotion/active/{promotionId}",
 *     summary="Activer une promotion",
 *     description="Cette fonction active une promotion spécifiée par son ID.",
 *     tags={"Promotion Admin"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="promotionId",
 *         in="path",
 *         description="ID de la promotion à activer",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Promotion activée avec succès"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Promotion spécifiée n'existe pas ou déjà active"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur"
 *     )
 * )
 */
    public function active($promotionId){
        try {
            $promotion = promotion::find($promotionId);
            if (!$promotion) {
                return response()->json(['message' => 'La promotion spécifié n\'existe pas'], 404);
            }
            if($promotion->is_actif == true){
                return (new ServiceController())->apiResponse(404,[],'Promotion déjà active');
            }
            $promotion->is_actif = true;
            $promotion->save();
            return (new ServiceController())->apiResponse(200,[],'Promotion activé avec succès');

        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

/**
 * @OA\Post(
 *     path="/api/promotion/desactive/{promotionId}",
 *     summary="Désactiver une promotion",
 *     description="Cette fonction désactive une promotion spécifiée par son ID.",
 *     tags={"Promotion Admin"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="promotionId",
 *         in="path",
 *         description="ID de la promotion à désactiver",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Promotion désactivée avec succès"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Promotion spécifiée n'existe pas"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur"
 *     )
 * )
 */
    public function desactive($promotionId){
        try {
            $promotion = promotion::find($promotionId);
            if (!$promotion) {
                return (new ServiceController())->apiResponse(404,[],'Le promotion spécifié n\'existe pas');
            }
            if($promotion->is_actif == false){
                return (new ServiceController())->apiResponse(200,[],'Promotion déjà désactivée');
            }
            $promotion->is_actif = false;
            $promotion->save();
            return (new ServiceController())->apiResponse(200,[],'Promotion désactivée avec succès');

        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/promotion/listActivePromotions",
     *     summary="Liste des promotions actives",
     *     description="Cette fonction retourne la liste des promotions actives.",
     *     tags={"Promotion Admin"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des promotions actives"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
public function listActivePromotions() {
    try {
        $promotions = Promotion::where('is_actif', true)->get();
        return (new ServiceController())->apiResponse(200, $promotions, 'Liste des promotions actives');
    } catch (Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
    }

    /**
     * @OA\Get(
     *     path="/api/promotion/listInactivePromotions",
     *     summary="Liste des promotions inactives",
     *     description="Cette fonction retourne la liste des promotions inactives.",
     *     tags={"Promotion Admin"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des promotions inactives"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
public function listInactivePromotions() {
    try {
        $promotions = Promotion::where('is_actif', false)->get();
        return (new ServiceController())->apiResponse(200, $promotions, 'Liste des promotions inactives');
    } catch (Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}
}
