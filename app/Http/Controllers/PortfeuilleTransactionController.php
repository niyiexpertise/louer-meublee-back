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
use App\Models\user_partenaire;
use App\Models\PortfeuilleTransactionHistory ;
use Illuminate\Support\Facades\DB ;
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

         return (new ServiceController())->apiResponse(404, [], 'Portefeuille non trouvé pour cet utilisateur.');

     }

     $transactions = Portfeuille_transaction::where('portfeuille_id', $portefeuille->id)
         ->orderBy('created_at', 'desc')
         ->get();

     // Filtrer les champs indésirables des transactions
     $filtered_transactions = $transactions->map(function($transaction) {
         return $transaction->only([
             'id',
             'debit',
             'credit',
             'amount',
             'valeur_commission',
             'montant_commission',
             'montant_restant' ,
             'motif',
             'reservation_id',
             'payment_method',
             'portfeuille_id',
             'id_transaction',
             'created_at',
             'updated_at',
         ]);
     });

     $data =[
        'solde_portefeuille' => $portefeuille->solde,
        'transactions' => $filtered_transactions,
    ];
          return (new ServiceController())->apiResponse(200, $data, "Detail de portefeuille de l'utilisateur recupéré avec succès .");
 }

  /**
   * @OA\Get(
   *     path="/api/portefeuille/transaction/all",
   *     summary="Voir toutes les transactions éffectué sur le site (Admin)",
   *     tags={"Transaction"},
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

    $transactions = Portfeuille_transaction::orderBy('id', 'desc')->get();

    if ($transactions->isEmpty()) {

        return (new ServiceController())->apiResponse(404, [], 'Aucune transaction trouvée sur le site.');

    }


    $data = [];
    foreach ($transactions as $transaction) {
        $user = $transaction->portfeuille->user;

        $data[] = [
            'transaction' => $transaction,
        ];
    }
    return (new ServiceController())->apiResponse(200, $data, "Detail de portefeuille de l'utilisateur recupéré avec succès .");

}






public function updateTransaction(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:portfeuille_transactions,id',
            'valeur_commission' => 'nullable|numeric',
            'valeur_commission_partenaire' => 'nullable|numeric',
            'valeur_commission_admin' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($request) {
            $transaction =Portfeuille_transaction::findOrFail($request->id);
            
            // Déterminer quel champ est modifié
            $modifiedField = null;
            if ($request->has('valeur_commission')) {
                $modifiedField = 'valeur_commission';
            } elseif ($request->has('valeur_commission_partenaire')) {
                $modifiedField = 'valeur_commission_partenaire';
            } elseif ($request->has('valeur_commission_admin')) {
                $modifiedField = 'valeur_commission_admin';
            }

            // Sauvegarder l'ancienne valeur
            $oldValue = $transaction->$modifiedField;
            
            // Mise à jour du champ modifié
            $transaction->$modifiedField = $request->$modifiedField;
            $transaction->save();

            // Enregistrer l'historique des modifications
            PortfeuilleTransactionHistory ::create([
                'transaction_id' => $transaction->id,
                'field_modified' => $modifiedField,
                'old_value' => $oldValue,
                'new_value' => $request->$modifiedField,
                'modified_by' => auth()->user()->id,
                'modified_at' => now(),
            ]);

            // Recalcul des montants et des soldes
            $this->recalculateFollowingTransactions($transaction, $modifiedField);
        });

        return response()->json(['message' => 'Transaction updated successfully']);
    }

    private function recalculateFollowingTransactions($transaction, $modifiedField)
    {
        $transactions = Portfeuille_transaction::where('id', '>=', $transaction->id)
            ->orderBy('id')
            ->get();

        $runningTotal = 0;
        $runningCommission = 0;
        $runningCommissionPartenaire = 0;
        $runningCommissionAdmin = 0;

        foreach ($transactions as $trx) {
            // Déterminer le montant de la commission
            if ($modifiedField === 'valeur_commission') {
                $trx->montant_commission = $trx->amount * ($trx->valeur_commission / 100);
                $trx->montant_restant = $trx->amount - $trx->montant_commission;
            }

            if ($modifiedField === 'valeur_commission_partenaire' || $modifiedField === 'valeur_commission') {
                $trx->montant_commission_partenaire = $trx->montant_commission * ($trx->valeur_commission_partenaire / 100);
            }

            if ($modifiedField === 'valeur_commission_admin' || $modifiedField === 'valeur_commission') {
                $trx->montant_commission_admin = $trx->montant_commission * ($trx->valeur_commission_admin / 100);
            }

            // Mise à jour du solde total en fonction des booléens credit et debit
            if ($trx->credit) {
                $runningTotal += $trx->amount;
            } elseif ($trx->debit) {
                $runningTotal -= $trx->amount;
            }

            // Mise à jour des soldes cumulés
            $trx->solde_total = $runningTotal;
            $trx->solde_commission = $runningCommission + $trx->montant_commission;
            $trx->solde_restant = $runningTotal - $trx->solde_commission;
            $trx->solde_commission_partenaire = $runningCommissionPartenaire + $trx->montant_commission_partenaire;
            $trx->new_solde_admin = $runningCommissionAdmin + $trx->montant_commission_admin;

            // Accumuler les valeurs pour les prochaines transactions
            $runningCommission = $trx->solde_commission;
            $runningCommissionPartenaire = $trx->solde_commission_partenaire;
            $runningCommissionAdmin = $trx->new_solde_admin;

            $trx->save();
        }
    }

}
