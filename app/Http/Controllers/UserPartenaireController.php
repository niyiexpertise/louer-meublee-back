<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use App\Models\Commission;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Validation\Rule;
use App\Models\user_partenaire;

class UserPartenaireController extends Controller
{/**
 * @OA\Get(
 *     path="/api/users/partenaires",
 *     summary="Liste des utilisateurs partenaires",
 *     description="Récupère la liste de tous les utilisateurs partenaires.",
 *     tags={"Utilisateurs partenaires"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des utilisateurs partenaires récupérée avec succès",
 *         @OA\JsonContent(
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur. Veuillez réessayer ultérieurement.",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", description="Message d'erreur")
 *         )
 *     )
 * )
 */
public function getUsersPartenaire()
{
    try {
        $usersPartenaires = user_partenaire::with('user')->get();
        return response()->json($usersPartenaires, 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

/**
* @OA\Put(
*     path="/api/commissionpartenaire/updateCommissionValueByAnother",
*     summary="Remplacer la valeur d'une commission par défaut par une autre valeur",
*     tags={"Commission partenaire"},
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

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid input data',
            ], 422);
        }
        $commission = user_partenaire::where('commission', $request->commission)->get();

        if (!$commission) {
            return response()->json([
                'error' => 'Commission not found',
            ], 404);
        }
        

        foreach($commission as $com){
            
            $com->update(['commission' => $request->valeur_commission]);
        }


        return response()->json(['message' => 'Commissions updated successfully']);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'message' => $e->getMessage(),
        ], 500);
    }
}


/**
 * @OA\Post(
 *     path="/api/commissionpartenaire/updateCommissionForSpecifiqueUser",
 *     summary="Modifier la valeur de la commission pour un ou plusieurs utilisateurs donnés",
 *     tags={"Commission partenaire"},
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

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid input data'], 400);
        }

        $commissionPercentage = $request->input('commission_percentage');
        $userIds = $request->input('user_ids');

        user_partenaire::whereIn('user_id', $userIds)
            ->update(['commission' => $commissionPercentage]);

        return response()->json(['message' => 'Commissions updated successfully']);
    } catch (Exception $e) {
        return response()->json(['error' => 'Internal server error'], 500);
    }
}


/**
* @OA\Put(
*     path="/api/reductionpartenaire/updatereductionValueByAnother",
*     summary="Remplacer la valeur d'une reduction par défaut par une autre valeur",
*     tags={"Reduction partenaire"},
*security={{"bearerAuth": {}}},
*     @OA\RequestBody(
*         required=true,
*         @OA\JsonContent(
*             required={"reduction", "valeur_reduction"},
*             @OA\Property(property="reduction", type="integer", example=13),
*             @OA\Property(property="valeur_reduction", type="integer", example=20)
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Reduction successfully updated",
*         @OA\JsonContent(
*             @OA\Property(property="message", type="string", example="Reduction updated successfully")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Reduction not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Reduction not found")
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
public function updatereductionValueByAnother(Request $request)
{
    try {

        $validator = Validator::make($request->all(), [
            'reduction' => 'required|integer',
            'valeur_reduction' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid input data',
            ], 422);
        }
        $reduction = user_partenaire::where('reduction_traveler', $request->reduction)->get();

        if (!$reduction) {
            return response()->json([
                'error' => 'Reduction not found',
            ], 404);
        }
        

        foreach($reduction as $red){
            
            $red->update(['reduction_traveler' => $request->valeur_reduction]);
        }


        return response()->json(['message' => 'Reductions updated successfully']);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'message' => $e->getMessage(),
        ], 500);
    }
}


/**
 * @OA\Post(
 *     path="/api/reductionpartenaire/updatereductionForSpecifiqueUser",
 *     summary="Modifier la valeur de la reduction pour un ou plusieurs utilisateurs donnés",
 *     tags={"Reduction partenaire"},
 * security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"reduction_percentage", "user_ids"},
 *             @OA\Property(property="reduction_percentage", type="integer", example=10),
 *             @OA\Property(property="user_ids", type="array", @OA\Items(type="integer", example="[1, 2, ...]"))
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="reduction updated successfully"
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
public function updatereductionForSpecifiqueUser(Request $request)
{
    try {
        // Validation des données d'entrée
        $validator = Validator::make($request->all(), [
            'reduction_percentage' => 'required|integer',
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' =>$validator->errors()], 400);
        }

        $reductionPercentage = $request->input('reduction_percentage');
        $userIds = $request->input('user_ids');

        user_partenaire::whereIn('user_id', $userIds)
            ->update(['reduction_traveler' => $reductionPercentage]);

        return response()->json(['message' => 'Reductions updated successfully']);
    } catch (Exception $e) {
        return response()->json(['error' => 'Internal server error'], 500);
    }
}




/**
* @OA\Put(
*     path="/api/numberreservationpartenaire/updatenumberreservationValueByAnother",
*     summary="Remplacer la valeur du nombre de reservation  par défaut par une autre valeur",
*     tags={"Reduction partenaire"},
*security={{"bearerAuth": {}}},
*     @OA\RequestBody(
*         required=true,
*         @OA\JsonContent(
*             required={"numberreservation", "valeur_numberreservation"},
*             @OA\Property(property="numberreservation", type="integer", example=13),
*             @OA\Property(property="valeur_numberreservation", type="integer", example=20)
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Number of reservation successfully updated",
*         @OA\JsonContent(
*             @OA\Property(property="message", type="string", example="Number of reservation updated successfully")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Number of reservation not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Number of reservation not found")
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
public function updatenumberreservationValueByAnother(Request $request)
{
    try {

        $validator = Validator::make($request->all(), [
            'numberreservation' => 'required|integer',
            'valeur_numberreservation' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 422);
        }
        $numberreservation = user_partenaire::where('number_of_reservation', $request->numberreservation)->get();

        if (!$numberreservation) {
            return response()->json([
                'error' => 'Number of reservation not found',
            ], 404);
        }
        

        foreach($numberreservation as $red){
            
            $red->update(['number_of_reservation' => $request->valeur_numberreservation]);
        }


        return response()->json(['message' => 'Number or reservation updated successfully']);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'message' => $e->getMessage(),
        ], 500);
    }
}


/**
 * @OA\Post(
 *     path="/api/numberreservationpartenaire/updatenumberreservationForSpecifiqueUser",
 *     summary="Modifier la valeur de nombre de reservation pour un ou plusieurs utilisateurs donnés",
 *     tags={"Reduction partenaire"},
 * security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"numberreservation_percentage", "user_ids"},
 *             @OA\Property(property="numberreservation_percentage", type="integer", example=10),
 *             @OA\Property(property="user_ids", type="array", @OA\Items(type="integer", example="[1, 2, ...]"))
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="numberreservation updated successfully"
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
public function updatenumberreservationForSpecifiqueUser(Request $request)
{
    try {
        // Validation des données d'entrée
        $validator = Validator::make($request->all(), [
            'numberreservation_percentage' => 'required|integer',
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid input data'], 400);
        }

       $numberreservation = $request->input('numberreservation_percentage');
        $userIds = $request->input('user_ids');

        user_partenaire::whereIn('user_id', $userIds)
            ->update(['number_of_reservation' =>$numberreservation]);

        return response()->json(['message' => 'Number of reservation updated successfully']);
    } catch (Exception $e) {
        return response()->json(['error' => 'Internal server error'], 500);
    }
}




}
