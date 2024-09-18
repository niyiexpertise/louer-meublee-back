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
use App\Jobs\SendRegistrationEmail;
use Exception;

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
     ->whereNot('operation_type',null)
         ->orderBy('created_at', 'desc')
         ->get();

     // Filtrer les champs indésirables des transactions
     $filtered_transactions = $transactions->map(function($transaction) {
         return $transaction->only([
             'id',
             'operation_type',
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
        if(!is_null($transaction->portfeuille_id)){
            $user = $transaction->portfeuille->user;
        }

        $data[] =   $transaction;
    }
    return (new ServiceController())->apiResponse(200, $data, "Detail de portefeuille de l'utilisateur recupéré avec succès .");

}


/**
 * @OA\Post(
 *     path="/api/portefeuille/transaction/update",
 *     summary="Mettre à jour une transaction",
 *     description="Met à jour les commissions ou autres champs d'une transaction, puis met à jour les soldes correspondants et enregistre les modifications dans l'historique.",
 *     operationId="updateTransaction",
 *     tags={"Transaction"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="id", type="integer", description="ID de la transaction"),
 *             @OA\Property(property="valeur_commission", type="number", description="Nouvelle valeur de commission"),
 *             @OA\Property(property="valeur_commission_partenaire", type="number", description="Nouvelle valeur de commission pour le partenaire"),
 *             @OA\Property(property="valeur_commission_admin", type="number", description="Nouvelle valeur de commission pour l'admin"),
 *             @OA\Property(property="motif", type="string", description="Motif de la modification", example="Correction de la commission partenaire")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Transaction mise à jour avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Transaction updated successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Données de requête non valides",
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Transaction non trouvée",
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *     ),
 *     security={{"bearerAuth":{}}}
 * )
 */


 public function updateTransaction(Request $request)
{
    try {

        // return $request;
        $validatedData = Validator::make($request->all(), [
            'id' => 'required|integer|exists:portfeuille_transactions,id',
            'valeur_commission' => 'nullable|numeric|min:0',
            'valeur_commission_partenaire' => 'nullable|numeric|min:0|max:100',
            'valeur_commission_admin' => 'nullable|numeric|min:0|max:100',
            'motif' => 'nullable|string',
        ]);

        $message = [];

        if ($validatedData->fails()) {
            $message[] = $validatedData->errors();
            return (new ServiceController())->apiResponse(505, [], $message);
        }

        $transaction = Portfeuille_transaction::find($request->id);

        if (!$transaction) {
            return (new ServiceController())->apiResponse(404, [], "Transaction non trouvée avec l'ID spécifié.");
        }

        // Vérifier si le partenaire_id est null
        $isPartenaire = $transaction->partenaire_id !== null;

        if (!$request->has('valeur_commission') && !$request->has('valeur_commission_partenaire') && !$request->has('valeur_commission_admin')) {
            return (new ServiceController())->apiResponse(404, [], "Aucune modification détectée. Veuillez fournir au moins une valeur à modifier.");
        }

        $modificationCount = 0;
        if ($request->has('valeur_commission')) $modificationCount++;
        if ($request->has('valeur_commission_partenaire')) $modificationCount++;
        if ($request->has('valeur_commission_admin')) $modificationCount++;

        if ($modificationCount !== 1) {
            return (new ServiceController())->apiResponse(404, [], "Vous ne pouvez modifier qu'une seule valeur de commission à la fois.");
        }

        // Si le partenaire_id est null, les modifications concernant le partenaire sont inutiles
        if (!$isPartenaire && ($request->has('valeur_commission_partenaire') || $request->has('valeur_commission_admin'))) {
            return (new ServiceController())->apiResponse(404, [], "Modification de la commission partenaire ou admin impossible car aucun partenaire n'est associé à cette transaction.");
        }

        DB::beginTransaction();

        $oldValues = [
            'montant_commission' => $transaction->montant_commission,
            'montant_commission_partenaire' => $transaction->montant_commission_partenaire,
            'montant_commission_admin' => $transaction->montant_commission_admin,
            'montant_restant' => $transaction->montant_restant,
            'valeur_commission' => $transaction->valeur_commission,
            'valeur_commission_partenaire' => $transaction->valeur_commission_partenaire,
            'valeur_commission_admin' => $transaction->valeur_commission_admin,
        ];

        if ($request->has('valeur_commission')) {
            if ($transaction->valeur_commission == $request->valeur_commission) {
                return (new ServiceController())->apiResponse(404, [], "Aucune modification : la valeur de commission est déjà identique.");
            }
            $transaction->valeur_commission = $request->valeur_commission;
            $transaction->montant_commission = $transaction->amount * ($request->valeur_commission / 100);
            $transaction->save();
            $transaction->montant_restant = $transaction->amount - $transaction->montant_commission;
            $transaction->montant_commission_partenaire = $transaction->montant_commission * ($transaction->valeur_commission_partenaire / 100);
            $transaction->save();
            $transaction->montant_commission_admin = $transaction->montant_commission - $transaction->montant_commission_partenaire;
        }

        if ($isPartenaire && $request->has('valeur_commission_partenaire')) {
            if ($transaction->valeur_commission_partenaire == $request->valeur_commission_partenaire) {
                return (new ServiceController())->apiResponse(404, [], "Aucune modification : la valeur de commission partenaire est déjà identique.");
            } 
            $transaction->valeur_commission_partenaire = $request->valeur_commission_partenaire;
            $transaction->valeur_commission_admin = 100 - $request->valeur_commission_partenaire;
            $transaction->montant_commission_partenaire = $transaction->montant_commission * ($request->valeur_commission_partenaire / 100);
            $transaction->save();
            $transaction->montant_commission_admin = $transaction->montant_commission - $transaction->montant_commission_partenaire;
        }

        if ($isPartenaire && $request->has('valeur_commission_admin')) {
            if ($transaction->valeur_commission_admin == $request->valeur_commission_admin) {
                return (new ServiceController())->apiResponse(404, [], "Aucune modification : la valeur de commission admin est déjà identique.");
            } 
            $transaction->valeur_commission_admin = $request->valeur_commission_admin;
            $transaction->valeur_commission_partenaire = 100 - $request->valeur_commission_admin;
            $transaction->montant_commission_admin = $transaction->montant_commission * ($request->valeur_commission_admin / 100);
            $transaction->save();
            $transaction->montant_commission_partenaire = $transaction->montant_commission - $transaction->montant_commission_admin;
        }

        $transaction->save();

        // Enregistrement dans l'historique
        if ($request->has('valeur_commission') || $request->has('valeur_commission_partenaire') || $request->has('valeur_commission_admin')) {
            $modifiedField = $request->has('valeur_commission') ? 'valeur_commission' : (
                $request->has('valeur_commission_partenaire') ? 'valeur_commission_partenaire' : 'valeur_commission_admin'
            );

            PortfeuilleTransactionHistory::create([
                'transaction_id' => $transaction->id,
                'column_name' => $modifiedField,
                'old_value' => $oldValues[$modifiedField],
                'new_value' => $request->input($modifiedField),
                'motif' => $request->motif,
                'modified_by' => auth()->user()->id,
                'modified_at' => now(),
            ]);
        }
        $this->recalculerSoldes($transaction->id);
        // Notifier le titulaire
        if ($oldValues['montant_restant'] !== $transaction->montant_restant) {
            $userPortefeuille = Portfeuille::where('id', $transaction->portfeuille_id)->first();
            if ($userPortefeuille) {
 
                // Mise à jour du portefeuille
                $userPortefeuille->solde += $transaction->montant_restant - $oldValues['montant_restant'];
                $userPortefeuille->save();
                $mail = [
                    "title" => "Mise à jour de votre portefeuille",
                    "body" => "Une mise à jour a été effectuée sur votre portefeuille concernant la transaction {$transaction->id}. Ancien montant reçu de la transaction: {$oldValues['montant_restant']} FCFA. Nouveau montant reçu de la transaction : {$transaction->montant_restant} FCFA. Motif : {$request->motif}.Nouveau solde du portefeuille:{$userPortefeuille->solde}",
                ];

                dispatch(new SendRegistrationEmail(
                    $userPortefeuille->user->email,
                    $mail['body'],
                    $mail['title'],
                    2
                ));
            }
        }

        // Notifier le partenaire (si applicable)
        
        if ($isPartenaire) {
          if ($oldValues['montant_commission_partenaire'] !== $transaction->montant_commission_partenaire) {
            $partenaire = user_partenaire::where('id', $transaction->partenaire_id)->first();
            
            if ($partenaire) {
                $partenaireUser = User::find($partenaire->user_id);
                if ($partenaireUser) {
                    $partenairePortefeuille = Portfeuille::where('user_id', $partenaireUser->id)->first();
                    if ($partenairePortefeuille) {
                        $partenairePortefeuille->solde += $transaction->montant_commission_partenaire - $oldValues['montant_commission_partenaire'];
                        $partenairePortefeuille->save();
                    }
                    $mail = [
                        "title" => "Mise à jour de votre portefeuille",
                        "body" => "Une mise à jour a été effectuée sur votre portefeuille concernant la transaction {$transaction->id}.  Ancienne commission partenaire reçue   : {$oldValues['montant_commission_partenaire']} FCFA. Nouvelle commission partenaire : {$transaction->montant_commission_partenaire} FCFA. Motif : {$request->motif}. Nouveau solde du portefeuille:{$partenairePortefeuille->solde}",
                    ];

                    dispatch(new SendRegistrationEmail(
                        $partenaireUser->email,
                        $mail['body'],
                        $mail['title'],
                        2
                    ));

                    // Mise à jour du portefeuille du partenaire
                   
                }
            }
        }
        }

        DB::commit();

        return (new ServiceController())->apiResponse(200, [], 'Transaction mise à jour avec succès.');
    } catch (\Exception $e) {
        DB::rollback();
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}




     private function recalculerSoldes($transactionId)
     {
         $transactions = Portfeuille_transaction::where('id', '>=', $transactionId)->orderBy('id', 'asc')->get();

         $currentTransaction = Portfeuille_transaction::where('id', '<', $transactionId)->orderBy('id', 'desc')->first();

         if (!$currentTransaction) {
             return;
         }

         foreach ($transactions as $transaction) {
             $transaction->solde_commission = $currentTransaction->solde_commission + $transaction->montant_commission;
             $transaction->solde_commission_partenaire = $currentTransaction->solde_commission_partenaire + $transaction->montant_commission_partenaire;
             $transaction->new_solde_admin = $currentTransaction->new_solde_admin + $transaction->montant_commission_admin;

             $transaction->save();

             $currentTransaction = $transaction;
         }
     }






/**
     * @OA\Get(
     *     path="/api/portefeuille/transaction/{id}/history",
     *     summary="Récupère l'historique des modifications d'une transaction",
     *     description="Retourne l'historique des modifications pour une transaction spécifique en fonction de son ID.",
     *     tags={"Transaction"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la transaction dont l'historique doit être récupéré",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Historique des modifications de la transaction",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="transaction_id", type="integer", example=81),
     *                 @OA\Property(property="field_modified", type="string", example="valeur_commission"),
     *                 @OA\Property(property="old_value", type="string", example="10"),
     *                 @OA\Property(property="new_value", type="string", example="15"),
     *                 @OA\Property(property="modified_by", type="integer", example=2),
     *                 @OA\Property(property="modified_at", type="string", format="date-time", example="2024-08-15T12:34:56Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transaction non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Transaction not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur interne",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Une erreur est survenue lors de la récupération de l'historique.")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */

     public function getTransactionHistory($id)
{
    try {
        // Trouver la transaction
        $transaction = Portfeuille_transaction::find($id);

        if (!$transaction) {
            return (new ServiceController())->apiResponse(404, [], 'Transaction non trouvée.');
        }

        $history = PortfeuilleTransactionHistory::where('transaction_id', $id)
            ->with('user')
            ->orderBy('modified_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'transaction_id' => $item->transaction_id,
                    'column_name' => $item->column_name,
                    'old_value' => $item->old_value,
                    'new_value' => $item->new_value,
                    'motif' => $item->motif,
                    'modified_at' => $item->modified_at,
                    'modified_by' => [
                        'id' => $item->user->id,
                        'first_name' => $item->user->firstname,
                        'last_name' => $item->user->lastname
                    ]
                ];
            });


        $data = [
            'data' => $history,
            'nombre' => $history->count()
        ];

        return (new ServiceController())->apiResponse(200, $data, 'Historique des transactions récupéré avec succès.');

    } catch (Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}


}
