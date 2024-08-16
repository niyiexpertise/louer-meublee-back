<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationEmail;
use App\Models\Portfeuille;
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
use App\Models\Portfeuille_transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmailwithoutfile;
use Illuminate\Validation\Rule;
class PortfeuilleController extends Controller
{
    /**
 * @OA\Post(
 *     path="/api/portefeuille/credit",
 *     summary="Créditer le portefeuille d'un utilisateur",
 *     description="Crédite le portefeuille d'un utilisateur avec un certain montant et enregistre la transaction. Un utilisateur doit être authentifié pour utiliser cet endpoint.",
 *     tags={"Portefeuille"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données nécessaires pour créditer le portefeuille",
 *         @OA\JsonContent(
 *             required={"amount", "paiement_methode", "transaction_id"},
 *             @OA\Property(property="amount", type="number", format="double", description="Le montant à créditer", example=100.0),
 *             @OA\Property(property="paiement_methode", type="string", description="Méthode de paiement utilisée", example="carte"),
 *             @OA\Property(property="transaction_id", type="string", description="ID de transaction unique", example="12345-abcde"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Le portefeuille a été crédité avec succès.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", description="Message de succès", example="Le portefeuille a été crédité avec succès."),
 *             @OA\Property(property="solde", type="number", format="double", description="Solde actuel du portefeuille")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Données de requête invalides.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", description="Message d'erreur", example="Données de requête invalides."),
 *             @OA\Property(property="errors", type="object", description="Détails des erreurs")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Portefeuille non trouvé.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", description="Message d'erreur", example="Portefeuille non trouvé pour cet utilisateur.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Utilisateur non authentifié.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", description="Message d'erreur", example="Utilisateur non authentifié.")
 *         )
 *     )
 * )
 */


    public function creditPortfeuille(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'paiement_methode' => 'required|string', 
            'transaction_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Données de requête invalides.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $userId = Auth::id();
        if (is_null($userId)) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        $amount = $request->input('amount');

        $portefeuille = Portfeuille::where('user_id', $userId)->first();

        if (!$portefeuille) {
            return response()->json([
                'message' => 'Portefeuille non trouvé pour cet utilisateur.',
            ], 404);
        }

        $soldeTotal = Portfeuille_transaction::sum('amount');
        $soldeCommission = Portfeuille_transaction::sum('montant_commission');
        $soldeRestant = Portfeuille_transaction::sum('montant_restant');

        $portefeuille->solde += $amount;
        $portefeuille->save();

        $portefeuilleTransaction = new Portfeuille_transaction();
        $portefeuilleTransaction->debit = false;
        $portefeuilleTransaction->credit = true;
        $portefeuilleTransaction->amount = $amount;
        $portefeuilleTransaction->motif = "Recharge de portfeuille";
        $portefeuilleTransaction->portfeuille_id = $portefeuille->id;
        $portefeuilleTransaction->id_transaction = $request->input('transaction_id'); 
        $portefeuilleTransaction->payment_method = $request->input('paiement_methode');
        
        $portefeuilleTransaction->solde_total = $soldeTotal  + $amount;
        
        $portefeuilleTransaction->save();
            (new ReservationController())->initialisePortefeuilleTransaction($portefeuilleTransaction->id);




        $mail = [
            "title" => "Confirmation de dépôt sur votre portefeuille",
            "body" => "Votre portefeuille a été crédité de {$amount} FCFA. Nouveau solde : {$portefeuille->solde} FCFA"
        ];


      dispatch( new SendRegistrationEmail(User::find($userId)->email, $mail['body'], $mail['title'], 2));
    
        return response()->json([
            'message' => 'Le portefeuille a été crédité avec succès.',
            'solde' => $portefeuille->solde,
        ], 200);
    }


    
}
