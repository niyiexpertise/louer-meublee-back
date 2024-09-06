<?php

namespace App\Http\Controllers;

use App\Models\reduction;
use Exception;
use Illuminate\Http\Request;

class AdminReductionController extends Controller
{
      /**
* @OA\Post(
*     path="/api/reduction/activeReductionAdmin/{id}",
*     summary="Activer une réduction côté admin",
*     tags={"Reduction Admin"},
*security={{"bearerAuth": {}}},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the reduction to active",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Reduction successfully active",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="Reduction successfully active")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Reduction not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Reduction not found")
*         )
*     )
* )
*/
    public function activeReductionAdmin($id)
    {
        try {
            $reduction = reduction::find($id);
            if(!$reduction) {
                return response()->json(['error' => 'Réduction non trouvée'], 404);
            }
            if($reduction->is_actif == true){
                return (new ServiceController())->apiResponse(404,[], 'Réduction déjà activée');
            }
            $reduction->is_actif = true;
            $reduction->save();


        return (new ServiceController())->apiResponse(200,[], 'Réduction activée avec succès');

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    /**
* @OA\Post(
*     path="/api/reduction/desactiveReductionAdmin/{id}",
*     summary="Désactiver une réduction côté admin",
*     tags={"Reduction Admin"},
*security={{"bearerAuth": {}}},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the reduction to inactive",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Reduction successfully inactive",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="Reduction successfully inactive")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Reduction not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Reduction not found")
*         )
*     )
* )
*/

    public function desactiveReductionAdmin($id)
    {
        try {
            $reduction = reduction::find($id);
            if(!$reduction) {
                return response()->json(['error' => 'Réduction non trouvée'], 404);
            }
            if($reduction->is_actif == false){
                return (new ServiceController())->apiResponse(404,[], 'Réduction déjà désactivée');
            }
            $reduction->is_actif = false;
            $reduction->save();


        return (new ServiceController())->apiResponse(200,[], 'Réduction désactivée avec succès');

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    /**
     * @OA\Get(
     *     path="/api/reduction/listeActiveReductionAdmin",
     *     summary="Liste des réductions actives",
     *     description="Cette fonction retourne la liste des réductions actives.",
     *     tags={"Reduction Admin"},
     * security={{"bearerAuth": {}}},

     *     @OA\Response(
     *         response=200,
     *         description="Liste des réductions actives"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function listeActiveReductionAdmin()
    {
        try {
            $reduction = reduction::where('is_deleted', false)->where('is_actif', true)->get();

        return (new ServiceController())->apiResponse(200,$reduction, 'Liste des réductions activées');

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

/**
     * @OA\Get(
     *     path="/api/reduction/listeDesactiveReductionAdmin",
     *     summary="Liste des réductions inactives",
     *     description="Cette fonction retourne la liste des réductions inactives.",
     *     tags={"Reduction Admin"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des réductions inactives"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function listeDesactiveReductionAdmin()
    {
        try {
            $reduction = reduction::where('is_actif',false)->where('is_deleted',false)->get();

        return (new ServiceController())->apiResponse(200,$reduction, 'Liste des réductions désactivées');

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }
}
