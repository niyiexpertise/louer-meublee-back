<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Commission;
use App\Models\User;
use Exception;
use Illuminate\Validation\Rule;
use App\Models\user_partenaire;
use Illuminate\Support\Facades\Auth;
use App\Models\Portfeuille;
use App\Models\Portfeuille_transaction;
use App\Models\Reservation;
class DashboardPartenaireController extends Controller
{
  /**
     * @OA\Get(
     *     path="/api/partenaire/users",
     *     summary="Liste des utilisateurs d'un partenaire connecté",
     *     description="Récupère la liste de tous les utilisateurs qui appartiennent à un partenaire connecté.",
     *     tags={"Dashboard partenaire"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des utilisateurs récupérée avec succès",
     *         @OA\JsonContent(
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Partenaire non trouvé.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Message d'erreur")
     *         )
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
    public function getUsersForPartenaire(Request $request)
    {
        try {
            $user = auth()->user();
            $userPartenaire = user_partenaire::where('user_id', $user->id)->first();

            if (!$userPartenaire) {
                return response()->json(['error' => 'Partenaire non trouvé.'], 404);
            }

            $users = User::where('partenaire_id', $userPartenaire->id)->get();
            $count = $users->count();

            return response()->json(['count' => $count, 'data' => $users], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
 * @OA\Get(
 *     path="/api/partenaire/users/transaction",
 *     summary="Obtenir les détails des transactions du portefeuille pour un partenaire connecté",
 *     description="Retourne le solde du portefeuille  et la liste des transactions de partenariat associées pour le partenaire connecté.",
 *     tags={"Dashboard partenaire"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Response(
 *         response=200,
 *         description="Détails des transactions du partenaire récupérés avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="solde_portefeuille",
 *                 type="number",
 *                 format="float",
 *                 description="Solde actuel du portefeuille de l'utilisateur",
 *                 example=1500.00
 *             ),
 *             @OA\Property(
 *                 property="transactions",
 *                 type="array",
 *                 description="Liste des transactions associées au portefeuille du partenaire",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(
 *                         property="id",
 *                         type="integer",
 *                         description="ID de la transaction",
 *                         example=1
 *                     ),
 *                     @OA\Property(
 *                         property="debit",
 *                         type="boolean",
 *                         description="Indique si la transaction est un débit",
 *                         example=false
 *                     ),
 *                     @OA\Property(
 *                         property="credit",
 *                         type="boolean",
 *                         description="Indique si la transaction est un crédit",
 *                         example=true
 *                     ),
 *                     @OA\Property(
 *                         property="amount",
 *                         type="number",
 *                         format="float",
 *                         description="Montant de la transaction",
 *                         example=100.00
 *                     ),
 *                     @OA\Property(
 *                         property="valeur_commission_partenaire",
 *                         type="number",
 *                         format="float",
 *                         description="Valeur de la commission pour le partenaire",
 *                         example=5.00
 *                     ),
 *                     @OA\Property(
 *                         property="montant_commission_partenaire",
 *                         type="number",
 *                         format="float",
 *                         description="Montant de la commission pour le partenaire",
 *                         example=5.00
 *                     ),
 *                     @OA\Property(
 *                         property="solde_commission_partenaire",
 *                         type="number",
 *                         format="float",
 *                         description="Solde de la commission pour le partenaire",
 *                         example=10.00
 *                     ),
 *                     @OA\Property(
 *                         property="motif",
 *                         type="string",
 *                         description="Motif de la transaction",
 *                         example="Virement sur le compte du partenaire"
 *                     ),
 *                     @OA\Property(
 *                         property="reservation_id",
 *                         type="integer",
 *                         description="ID de la réservation associée",
 *                         example=123
 *                     ),
 *                     @OA\Property(
 *                         property="payment_method",
 *                         type="string",
 *                         description="Méthode de paiement utilisée",
 *                         example="portfeuille"
 *                     ),
 *                     @OA\Property(
 *                         property="portfeuille_id",
 *                         type="integer",
 *                         description="ID du portefeuille",
 *                         example=1
 *                     ),
 *                     @OA\Property(
 *                         property="id_transaction",
 *                         type="string",
 *                         description="ID de la transaction",
 *                         example="0"
 *                     ),
 *                     @OA\Property(
 *                         property="created_at",
 *                         type="string",
 *                         format="date-time",
 *                         description="Date de création de la transaction",
 *                         example="2024-08-08T12:00:00Z"
 *                     ),
 *                     @OA\Property(
 *                         property="updated_at",
 *                         type="string",
 *                         format="date-time",
 *                         description="Date de dernière mise à jour de la transaction",
 *                         example="2024-08-08T12:00:00Z"
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Partenaire non trouvé pour cet utilisateur",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="Message d'erreur",
 *                 example="Partenaire non trouvé pour cet utilisateur."
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="error",
 *                 type="string",
 *                 description="Message d'erreur interne",
 *                 example="Une erreur est survenue."
 *             )
 *         )
 *     )
 * )
 */
public function getPartnerPortfeuilleDetails(Request $request)
{
    $user = $request->user();

    // Trouver le partenaire associé à l'utilisateur connecté
    $partenaire = user_partenaire::where('user_id', $user->id)->first();

    if (!$partenaire) {
        return response()->json([
            'message' => 'Partenaire non trouvé pour cet utilisateur.'
        ], 404);
    }

    $partenaire_id = $partenaire->id;

    // Récupérer le portefeuille de l'utilisateur
    $portefeuille = Portfeuille::where('user_id', $user->id)->first();

    if (!$portefeuille) {
        return response()->json([
            'message' => 'Portefeuille non trouvé pour cet utilisateur.'
        ], 404);
    }

    // Récupérer les transactions du portefeuille de l'utilisateur avec les informations de réservation et d'utilisateur
    $transactions = Portfeuille_transaction::where('partenaire_id', $partenaire_id)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($transaction) {
            $reservation = Reservation::where('id', $transaction->reservation_id)->first();
            $user = User::where('id', $reservation->user_id ?? null)->first();

            return [
                'id' => $transaction->id,
                'debit' => $transaction->debit,
                'credit' => $transaction->credit,
                'amount' => $transaction->amount,
                'valeur_commission_partenaire' => $transaction->valeur_commission_partenaire,
                'montant_commission_partenaire' => $transaction->montant_commission_partenaire,
                'solde_commission_partenaire' => $transaction->solde_commission_partenaire,
                'motif' => $transaction->motif,
                'reservation_id' => $transaction->reservation_id,
                'housing_id' => $reservation->housing_id ?? null,
                'firstname_user' => $user->firstname ?? null,
                'lastname_user' => $user->lastname ?? null,
                'payment_method' => $transaction->payment_method,


                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
            ];
        });

    return response()->json([
        'solde_portefeuille' => $portefeuille->solde,
        'transactions' => $transactions,
    ], 200);
}
/**
 * @OA\Get(
 *     path="/api/partenaire/users/reservation",
 *     summary="Liste des réservations effectuées avec le code promo d'un partenaire connecté",
 *     tags={"Dashboard partenaire"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="reservations",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="reservation_id", type="integer"),
 *                     @OA\Property(property="housing_id", type="integer"),
 *                     @OA\Property(property="date_of_reservation", type="string", format="date"),
 *                     @OA\Property(property="date_of_starting", type="string", format="date"),
 *                     @OA\Property(property="date_of_end", type="string", format="date"),
 *                     @OA\Property(property="number_of_adult", type="integer"),
 *                     @OA\Property(property="number_of_child", type="integer"),
 *                     @OA\Property(property="number_of_domestical_animal", type="integer"),
 *                     @OA\Property(property="number_of_baby", type="integer"),
 *                     @OA\Property(property="telephone_traveler", type="string"),
 *                     @OA\Property(property="is_confirmed_hote", type="boolean"),
 *                     @OA\Property(property="is_rejected_traveler", type="boolean"),
 *                     @OA\Property(property="is_rejected_hote", type="boolean"),
 *                     @OA\Property(property="is_vire", type="boolean"),
 *                     @OA\Property(property="valeur_commission_partenaire", type="number", format="float", nullable=true),
 *                     @OA\Property(property="montant_commission_partenaire", type="number", format="float", nullable=true),
 *                     @OA\Property(property="lastname_traveler", type="string"),
 *                     @OA\Property(property="firstname_traveler", type="string"),
 *                     @OA\Property(property="created_at", type="string", format="date-time"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time"),
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Partenaire non trouvé ou aucune réservation avec code promo"
 *     )
 * )
 */

 public function getReservationsWithPromoCode(Request $request)
 {
     $user = $request->user();
 
     // Trouver le partenaire associé à l'utilisateur connecté
     $partenaire = user_partenaire::where('user_id', $user->id)->first();
 
     if (!$partenaire) {
         return response()->json([
             'message' => 'Partenaire non trouvé pour cet utilisateur.'
         ], 404);
     }
 
     $partenaire_id = $partenaire->id;
 
     // Récupérer les réservations faites avec un code promo pour ce partenaire
     $reservations = Reservation::where('valeur_reduction_code_promo', '!=', 0)
         ->get()
         ->filter(function($reservation) use ($partenaire_id) {
             // Trouver l'utilisateur qui a fait la réservation
             $user = User::where('id', $reservation->user_id)->first();
             // Vérifier si l'utilisateur est associé au partenaire connecté
             return $user && $user->partenaire_id == $partenaire_id;
         })
         ->map(function($reservation) use ($partenaire_id) {
             // Vérifier si cette réservation a une transaction associée dans le portefeuille du partenaire
             $transaction = Portfeuille_transaction::where('reservation_id', $reservation->id)
                 ->where('partenaire_id', $partenaire_id)
                 ->first();
 
             // Ajouter les informations sur la transaction si elle existe
             $transaction_info = $transaction ? [
                 'is_vire' => true,
                 'valeur_commission_partenaire' => $transaction->valeur_commission_partenaire,
                 'montant_commission_partenaire' => $transaction->montant_commission_partenaire,
             ] : [
                 'is_vire' => false,
                 'valeur_commission_partenaire' => null,
                 'montant_commission_partenaire' => null,
             ];
 
             return array_merge([
                 'reservation_id' => $reservation->id,
                 'housing_id' => $reservation->housing_id,
                 'date_of_reservation' => $reservation->date_of_reservation,
                 'date_of_starting' => $reservation->date_of_starting,
                 'date_of_end' => $reservation->date_of_end,
                 'is_confirmed_hote' => $reservation->is_confirmed_hote,
                 'is_rejected_traveler' => $reservation->is_rejected_traveler,
                 'is_rejected_hote' => $reservation->is_rejected_hote,
                 'lastname_traveler' => $reservation->user->lastname,
                 'firstname_traveler' => $reservation->user->firstname,
                 'created_at' => $reservation->created_at,
                 'updated_at' => $reservation->updated_at,
             ], $transaction_info);
         });
 
     return response()->json([
         'reservations' => $reservations,
     ], 200);
 }
 




}
