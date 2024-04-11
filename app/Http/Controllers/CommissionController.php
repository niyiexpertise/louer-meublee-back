<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
class CommissionController extends Controller
{

        /**
* @OA\Put(
*     path="/api/commission/updateCommissionValueByAnother/{commission}",
*     summary="modifier la valeur de la commission pour un ou plusieurs utilisateurs donné",
*     tags={"Commission"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the commission to updated",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Commission successfully updated",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="Commission successfully updated")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Commission not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Commission not found")
*         )
*     )
* )
*/
    //remplacer la valeur d'une commission par défaut par une autre
    public function updateCommissionValueByAnother(Request $request, $commission){
        try{
        Commission::where('valeur',$commission)->update(['valeur' => $request->valeur_commission]);
        return response()->json(['message' => 'Commissions updated successfully']);
        }catch (Exception $e) {
            return response()->json([
              'status_code' => 500,
              'message' => $e->getMessage(),
            ]);
          }

    }

        /**
     * Store a newly created resource in storage.
     */

      /**
* @OA\Post(
*     path="/api/commission/updateCommissionForSpecifiqueUser",
*     summary="Create a new commission",
*     tags={"Commission"},
*      @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"name"},
   *             @OA\Property(property="user_ids", type="integer", example ="[1,2,...]")
   *         )
   *     ),
*     @OA\Response(
*         response=201,
*         description="Commission created successfully"
*     ),
*     @OA\Response(
*         response=401,
*         description="Invalid credentials"
*     )
* )
*/

        //modifier la valeur de la commission pour un ou plusieurs utilisateurs donné
    public function updateCommissionForSpecifiqueUser(Request $request)
    {
        try{
            $commissionPercentage = $request->input('commission_percentage');
            $userIds = $request->input('user_ids');
            Commission::whereIn('user_id', $userIds)
                ->update(['valeur' => $commissionPercentage]);
        
            return response()->json(['message' => 'Commissions updated successfully']);
        }catch (Exception $e){
            return response()->json($e);
        }
        
    }

    /**
     * Display the specified resource.
     */

     /**
   * @OA\Get(
   *     path="/api/commission/usersWithCommission/{commission}",
   *     summary="Récupérer les utilisateurs associés aux commissions spécifiques",
   *     tags={"Commission"},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the commission",
   *         @OA\Schema(type="integer")
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
    //lister les utilisateurs ayant une commission donner en parametre
    public function usersWithCommission($commission)
    {
        try {
            // Récupérer les utilisateurs associés aux commissions spécifiques
            $users = User::join('commissions', 'users.id', '=', 'commissions.user_id')
                         ->where('commissions.is_deleted', false)
                         ->where('commissions.valeur', $commission)
                         ->select('users.*')
                         ->get();
    
            return response()->json([
                'data' => $users,
            ]);
        } catch (Exception $e) {
            return response()->json($e);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
