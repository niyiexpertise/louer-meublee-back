<?php

namespace App\Http\Controllers;
use App\Models\Portfeuille;
use App\Models\Portfeuille_transaction;
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
use Carbon\Carbon;

class PortfeuilleTransactionController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/portefeuille/user/transaction",
 *     summary="Obtenir les détails du portefeuille d'un utilisateur(Liste des transactions éffectuées par l'utilisateur sur le site)",
 *     description="Retourne le solde du portefeuille de l'utilisateur connecté et la liste des transactions associées.",
 *     tags={"Portefeuille"},
 *     @OA\Response(
 *         response=200,
 *         description="Détails du portefeuille retournés avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="solde",
 *                 type="number",
 *                 description="Solde actuel du portefeuille",
 *             ),
 *             @OA\Property(
 *                 property="transactions",
 *                 type="array",
 *                 description="Liste des transactions associées au portefeuille",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(
 *                         property="debit",
 *                         type="boolean",
 *                         description="Indique si la transaction est un débit",
 *                     ),
 *                     @OA\Property(
 *                         property="credit",
 *                         type="boolean",
 *                         description="Indique si la transaction est un crédit",
 *                     ),
 *                     @OA\Property(
 *                         property="amount",
 *                         type="number",
 *                         description="Montant de la transaction",
 *                     ),
 *                     @OA\Property(
 *                         property="motif",
 *                         type="string",
 *                         description="Motif de la transaction",
 *                     ),
 *                     @OA\Property(
 *                         property="created_at",
 *                         type="string",
 *                         format="date-time",
 *                         description="Date de création de la transaction",
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Portefeuille non trouvé pour cet utilisateur",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="Message d'erreur",
 *             )
 *         )
 *     ),
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */

    public function getPortfeuilleDetails(Request $request)
    {
        $user = $request->user();
    
        $portefeuille = Portfeuille::where('user_id', $user->id)->first();
    
        if (!$portefeuille) {
            return response()->json([
                'message' => 'Portefeuille non trouvé pour cet utilisateur.'
            ], 404);
        }
    
        $transactions = Portfeuille_transaction::where('portfeuille_id', $portefeuille->id)
            ->orderBy('created_at', 'desc')
            ->get();
    
        return response()->json([
            'solde' => $portefeuille->solde,
            'transactions' => $transactions,
        ], 200);
    }
  /**
   * @OA\Get(
   *     path="/api/portefeuille/transaction/all",
   *     summary="Voir toutes les transactions éffectué sur le site (Admin)",
   *     tags={"Portefeuille"},
   * security={{"bearerAuth": {}}},
   *     @OA\Response(
   *         response=200,
   *         description="Liste de toutes les transactions éffectuées sur le site"
   * 
   *     )
   * )
   */
    public function getAllTransactions()
{

    $transactions = Portfeuille_transaction::orderBy('created_at', 'desc')->get();

    if ($transactions->isEmpty()) {
        return response()->json([
            'message' => 'Aucune transaction trouvée sur le site.'
        ], 404);
    }

    $data = [];
    foreach ($transactions as $transaction) {
        $user = $transaction->portfeuille->user;

        $data[] = [
            'transaction' => $transaction,
        ];
    }

    return response()->json([
        'data' => $data,
    ], 200);
}

    
}
