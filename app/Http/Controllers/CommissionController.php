<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;

use App\Models\Commission;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Validation\Rule;
class CommissionController extends Controller
{
/**
* @OA\Put(
*     path="/api/commission/updateCommissionValueByAnother",
*     summary="Remplacer la valeur d'une commission par défaut par une autre valeur",
*     tags={"Commission hote"},
*security={{"bearerAuth": {}}},
*     @OA\RequestBody(
*         required=true,
*         @OA\JsonContent(
*             required={"commission", "valeur_commission"},
*             @OA\Property(property="commission", type="integer", example=13),
*             @OA\Property(property="valeur_commission", type="integer", example=20)
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Commission successfully updated",
*         @OA\JsonContent(
*             @OA\Property(property="message", type="string", example="Commissions updated successfully")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Commission not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Commission not found")
*         )
*     ),
*     @OA\Response(
*         response=422,
*         description="Validation error",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Invalid input data")
*         )
*     )
* )
*/
public function updateCommissionValueByAnother(Request $request)
{
    try {

        $validator = Validator::make($request->all(), [
            'commission' => 'required|integer',
            'valeur_commission' => 'required|integer',
        ]);

        $message = [];

        if ($validator->fails()) {
            $message[] = $validator->errors();
            return (new ServiceController())->apiResponse(505,[],$message);
        }

        $commission = Commission::where('valeur', $request->commission)->get();

        if (!$commission) {
            return (new ServiceController())->apiResponse(404,[], "Commission non trouvé");
        }

        if(!is_null(Setting::first()->commission_seuil_hote_partenaire)){
            if($request->input('valeur_commission') <= Setting::first()->commission_seuil_hote_partenaire){
                return (new ServiceController())->apiResponse(404,[], "La valeur de lacommission ne doit pas être en dessous de ".Setting::first()->commission_seuil_hote_partenaire);
            }
        }

        foreach($commission as $com){
            $com->update(['valeur' => $request->valeur_commission]);
        }

        return (new ServiceController())->apiResponse(200,[], "Commissions updated successfully");
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'message' => $e->getMessage(),
        ], 500);
    }
}


/**
 * @OA\Post(
 *     path="/api/commission/updateCommissionForSpecifiqueUser",
 *     summary="Modifier la valeur de la commission pour un ou plusieurs utilisateurs donnés",
 *     tags={"Commission hote"},
 * security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"commission_percentage", "user_ids"},
 *             @OA\Property(property="commission_percentage", type="integer", example=10),
 *             @OA\Property(property="user_ids", type="array", @OA\Items(type="integer", example="[1, 2, ...]"))
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Commission updated successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input data"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error"
 *     )
 * )
 */
public function updateCommissionForSpecifiqueUser(Request $request)
{
    try {
        // Validation des données d'entrée
        $validator = Validator::make($request->all(), [
            'commission_percentage' => 'required|integer',
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer',
        ]);

        $message = [];

        if ($validator->fails()) {
            $message[] = $validator->errors();
            return (new ServiceController())->apiResponse(505,[],$message);
        }

        if(floatval($request->commission_percentage)<= 0){
            return (new ServiceController())->apiResponse(404,[], "Assurez vous que la nouvelle valeur de la commission soit positive et non nulle");
        }

        if(!is_null(Setting::first()->commission_seuil_hote_partenaire)){
            if($request->input('commission_percentage') <= Setting::first()->commission_seuil_hote_partenaire){
                return (new ServiceController())->apiResponse(404,[], "La valeur de la  commission ne doit pas être en dessous de ".Setting::first()->commission_seuil_hote_partenaire);
            }
        }

        // Récupération des données d'entrée
        $commissionPercentage = $request->input('commission_percentage');
        $userIds = $request->input('user_ids');

        // Mise à jour des commissions pour les utilisateurs spécifiés
        Commission::whereIn('user_id', $userIds)
            ->update(['valeur' => $commissionPercentage]);

        return (new ServiceController())->apiResponse(200,[], "Commissions updated successfully");
    } catch (Exception $e) {
        return response()->json(['error' => 'Internal server error'], 500);
    }
}


/**
 * @OA\Get(
 *     path="/api/commission/usersWithCommission/{commission}",
 *     summary="Récupérer les utilisateurs associés à une commission spécifique",
 *     tags={"Commission hote"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="commission",
 *         in="path",
 *         required=true,
 *         description="value of the commission",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Commission details"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Commission not found"
 *     )
 * )
 */
public function usersWithCommission($commission)
{
    if ($commission === null) {
        return response()->json([
            'message' => 'Le champ commission est requis.',
        ], 400);
    }

    $users = User::join('commissions', 'users.id', '=', 'commissions.user_id')
                 ->where('commissions.valeur', $commission)
                 ->select('users.*', 'commissions.valeur AS commission_value')
                 ->get();

    if ($users->isEmpty()) {
        return response()->json([
            'message' => 'Aucun utilisateur trouvé pour cette commission.',
        ], 404);
    }

    return response()->json([
        'data' => $users,
    ]);
}

}
