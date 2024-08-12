<?php

namespace App\Http\Controllers;

use App\Models\Payement;
use App\Models\Portfeuille;
use App\Models\Portfeuille_transaction;
use App\Models\Reservation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashBoardTravelerController extends Controller
{
       // try {
            //$data["data"] = [];
             //     return (new ServiceController())->apiResponse(500,$data,'liste des résrvation ...');

        // } catch (Exception $e) {
        //     return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        // }
    


        /**
         * @OA\Get(
         *     path="/api/reservation/getReservationsForTraveler",
         *     summary="Liste des réservations pour le voyageur connecté",
         *     tags={"Dashboard traveler"},
         * security={{"bearerAuth": {}}},
         *     @OA\Response(
         *         response=200,
         *         description="Liste des réservations pour le voyageur connecté.",
         *         @OA\JsonContent(
         *             type="array",
         *             @OA\Items(ref="")
         *         )
         *     ),
         *     @OA\Response(
         *         response=500,
         *         description="Erreur lors de la récupération des réservations.",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Erreur de serveur.")
         *         )
         *     )
         * )
         */
        public function getReservationsForTraveler()
        {
            try {
                $userId = Auth::id();
        
                $data["data"] = Reservation::where('user_id', $userId)->get();
                $data["nombre"] = count($data["data"]) ;

        
                return (new ServiceController())->apiResponse(200, $data, 'Liste des réservations pour le voyageur connecté.');
            } catch (Exception $e) {
                return (new ServiceController())->apiResponse(500, [], $e->getMessage());
            }
        }


        /**
         * @OA\Get(
         *     path="/api/reservation/getRejectedReservationsByTraveler",
         *     summary="Liste des réservations rejetées par le voyageur",
         *     tags={"Dashboard traveler"},
         * security={{"bearerAuth": {}}},
         *     @OA\Response(
         *         response=200,
         *         description="Liste des réservations rejetées par le voyageur.",
         *         @OA\JsonContent(
         *             type="array",
         *             @OA\Items(ref="")
         *         )
         *     ),
         *     @OA\Response(
         *         response=500,
         *         description="Erreur lors de la récupération des réservations rejetées par le voyageur.",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Erreur de serveur.")
         *         )
         *     )
         * )
         */
        public function getRejectedReservationsByTraveler()
        {
            try {
                $userId = Auth::id();

                $data["data"] = Reservation::where('user_id', $userId)
                                ->where('is_rejected_traveler', true)
                                ->get();
                $data["nombre"] = count($data["data"]) ;

                return (new ServiceController())->apiResponse(200, $data, 'Liste des réservations rejetées par le voyageur.');
            } catch (Exception $e) {
                return (new ServiceController())->apiResponse(500, [], $e->getMessage());
            }
        }


        /**
         * @OA\Get(
         *     path="/api/reservation/getConfirmedReservations",
         *     summary="Liste des réservations confirmées",
         *     tags={"Dashboard traveler"},
         * security={{"bearerAuth": {}}},
         *     @OA\Response(
         *         response=200,
         *         description="Liste des réservations confirmées.",
         *         @OA\JsonContent(
         *             type="array",
         *             @OA\Items(ref="")
         *         )
         *     ),
         *     @OA\Response(
         *         response=500,
         *         description="Erreur lors de la récupération des réservations confirmées.",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Erreur de serveur.")
         *         )
         *     )
         * )
         */
        public function getConfirmedReservations()
        {
            try {
                $userId = Auth::id();

                $data["data"] = Reservation::where('user_id', $userId)
                                ->where('is_confirmed_hote', true)
                                ->where('is_integration', true)
                                ->get();
                $data["nombre"] = count($data["data"]) ;

                return (new ServiceController())->apiResponse(200, $data, 'Liste des réservations confirmées.');
            } catch (Exception $e) {
                return (new ServiceController())->apiResponse(500, [], $e->getMessage());
            }
        }


        /**
         * @OA\Get(
         *     path="/api/reservation/getRejectedReservationsByHost",
         *     summary="Liste des réservations rejetées par l'hôte",
         *     tags={"Dashboard traveler"},
         * security={{"bearerAuth": {}}},
         *     @OA\Response(
         *         response=200,
         *         description="Liste des réservations rejetées par l'hôte.",
         *         @OA\JsonContent(
         *             type="array",
         *             @OA\Items(ref="")
         *         )
         *     ),
         *     @OA\Response(
         *         response=500,
         *         description="Erreur lors de la récupération des réservations rejetées par l'hôte.",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Erreur de serveur.")
         *         )
         *     )
         * )
         */
        public function getRejectedReservationsByHost()
        {
            try {
                $userId = Auth::id();
        
                $data["data"] = Reservation::where('user_id', $userId)
                                   ->where('is_rejected_hote', true)
                                   ->get();
                $data["nombre"] = count($data["data"]) ;
        
                return (new ServiceController())->apiResponse(200, $data, 'Liste des réservations rejetées par l\'hôte.');
            } catch (Exception $e) {
                return (new ServiceController())->apiResponse(500, [], $e->getMessage());
            }
        }

        /**
         * @OA\Get(
         *     path="/api/reservation/getUnpaidReservations",
         *     summary="Liste des réservations non entièrement payées",
         *     tags={"Dashboard traveler"},
         * security={{"bearerAuth": {}}},
         *     @OA\Response(
         *         response=200,
         *         description="Liste des réservations non entièrement payées.",
         *         @OA\JsonContent(
         *             type="array",
         *             @OA\Items(ref="")
         *         )
         *     ),
         *     @OA\Response(
         *         response=500,
         *         description="Erreur lors de la récupération des réservations non entièrement payées.",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Erreur de serveur.")
         *         )
         *     )
         * )
         */

        public function getUnpaidReservations()
        {
            try {
                $userId = Auth::id();

                
                $data["data"] = Reservation::where('user_id', $userId)
                ->where('is_tranche_paiement', true)
                ->whereColumn('valeur_payee', '<', 'montant_a_paye')
                ->get();

                $data["nombre"] = count($data["data"]) ;

                return (new ServiceController())->apiResponse(200, $data, 'Liste des réservations non entièrement payées.');
            } catch (Exception $e) {
                return (new ServiceController())->apiResponse(500, [], $e->getMessage());
            }
        }

        /**
         * @OA\Get(
         *     path="/api/reservation/getPendingConfirmations",
         *     summary="Liste des logements en attente de confirmation",
         *     tags={"Dashboard traveler"},
         *  security={{"bearerAuth": {}}},
         *     @OA\Response(
         *         response=200,
         *         description="Liste des logements en attente de confirmation.",
         *         @OA\JsonContent(
         *             type="array",
         *             @OA\Items(ref="")
         *         )
         *     ),
         *     @OA\Response(
         *         response=500,
         *         description="Erreur lors de la récupération des logements en attente de confirmation.",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Erreur de serveur.")
         *         )
         *     )
         * )
         */

        public function getPendingConfirmations()
        {
            try {
                $userId = Auth::id();

                $data["data"] = Reservation::where('user_id', $userId)
                                    ->where('is_confirmed_hote', true)
                                    ->get();
                $data["nombre"] = count($data["data"]);

                return (new ServiceController())->apiResponse(200, $data, 'Liste des logements en attente de confirmation.');
            } catch (Exception $e) {
                return (new ServiceController())->apiResponse(500, [], $e->getMessage());
            }
        }


      /**
 * @OA\Post(
 *     path="/api/reservation/soldeReservation",
 *     summary="Effectuer le paiement d'une réservation",
 *     tags={"Dashboard traveler"},
 *  security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"reservation_id", "payment_method"},
 *                 @OA\Property(
 *                     property="reservation_id",
 *                     description="ID de la réservation",
 *                     type="integer",
 *                     example=123
 *                 ),
 *                 @OA\Property(
 *                     property="payment_method",
 *                     description="Méthode de paiement",
 *                     type="string",
 *                     example="portfeuille"
 *                 ),
 *                 @OA\Property(
 *                     property="id_transaction",
 *                     description="ID de la transaction (facultatif)",
 *                     type="string",
 *                     example="txn_abc123"
 *                 ),
 *                 @OA\Property(
 *                     property="statut_paiement",
 *                     description="Statut du paiement (facultatif)",
 *                     type="string",
 *                     example="completed"
 *                 ),
 *                
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Paiement effectué avec succès",
 * 
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Paiement effectué avec succès"
 *             ),
 *             @OA\Property(
 *                 property="reservation",
 *                 type="object",
 *                 @OA\Property(
 *                     property="id",
 *                     type="integer",
 *                     example=123
 *                 ),
 *                 @OA\Property(
 *                     property="user_id",
 *                     type="integer",
 *                     example=456
 *                 ),
 *             ),
 *             @OA\Property(
 *                 property="montant_a_payer",
 *                 type="number",
 *                 format="float",
 *                 example=50.00
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Requête invalide",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Vous ne pouvez pas terminer le paiement d'une réservation qui ne peut être payé par tranche"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Accès interdit",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Vous n'êtes pas autorisé à effectuer ce paiement"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Réservation non trouvée",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Réservation non trouvée"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="error",
 *                 type="string",
 *                 example="Une erreur s'est produite : message de l'erreur"
 *             )
 *         )
 *     )
 * )
 */




        public function soldeReservation(Request $request)
        {
            $validatedData = $request->validate([
                'reservation_id' => 'required|exists:reservations,id',
                'payment_method' => 'required|string',
                'id_transaction' => 'nullable|string',
                'statut_paiement' => 'boolean',
            ]);

            $user_id = Auth::id();
            $reservation = Reservation::find($validatedData['reservation_id']);

            if (!$reservation) {
                return response()->json(['message' => 'Réservation non trouvée'], 404);
            }

            if (!$reservation->is_tranche_paiement) {
                return response()->json(['message' => "Vous ne pouvez pas terminer le paiement d'une reservation qui ne peut  être payé par tranche "], 400);
            }

            if ($reservation->montant_a_paye == $reservation->valeur_payee) {
                return response()->json(['message' => "Logement déjà soldé "], 400);
            }


            $required_paid_value = $reservation->montant_a_paye / 2;

            DB::beginTransaction();

            try {
                $reservation->valeur_payee += $required_paid_value;
                $reservation->save();

                $paymentData = [
                    'reservation_id' => $reservation->id,
                    'amount' => $required_paid_value,
                    'payment_method' => $validatedData['payment_method'],
                    'id_transaction' => $validatedData['id_transaction'],
                    'statut' => $validatedData['statut_paiement'],
                    'is_confirmed' => true,
                    'is_canceled' => false,
                ];

                Payement::create($paymentData);

                if ($validatedData['payment_method'] == "portfeuille" ) {

                    if ($reservation->user_id != $user_id) {
                        return response()->json(['message' => 'Vous n\'êtes pas autorisé à effectuer ce paiement'], 403);
                    }

                    $portefeuille = Portfeuille::where('user_id', $user_id)->first();
                    if($portefeuille->solde < $required_paid_value){
                        return response()->json(['message' => "Vous n'avez pas assez d'argent sur votre portefeuille pour effectuer ce paiement"], 400);
                    }
                    $portefeuille->solde -= $required_paid_value;
                    

                    $portefeuilleTransaction = new Portfeuille_transaction();
                    $portefeuilleTransaction->debit = true;
                    $portefeuilleTransaction->credit = false;
                    $portefeuilleTransaction->amount = $required_paid_value;
                    $portefeuilleTransaction->motif = "Finalisation de paiement";
                    
                    $portefeuilleTransaction->reservation_id = $reservation->id;
                    $portefeuilleTransaction->payment_method = $validatedData['payment_method'];
                    $portefeuilleTransaction->id_transaction = $validatedData['id_transaction']??null;
                    $portefeuilleTransaction->portfeuille_id = $portefeuille->id;
                    
                    $portefeuilleTransaction->save();
                    $portefeuille->save();
                    $portefeuilleTransaction->save();
                    (new ReservationController())->initialisePortefeuilleTransaction($portefeuilleTransaction->id);
                }

                if ($validatedData['payment_method'] == "espece" ) {

                    $portefeuille = Portfeuille::where('user_id', $reservation->user_id)->first();
                    

                    $portefeuilleTransaction = new Portfeuille_transaction();
                    $portefeuilleTransaction->debit = true;
                    $portefeuilleTransaction->credit = false;
                    $portefeuilleTransaction->amount = $required_paid_value;
                    $portefeuilleTransaction->motif = "Finalisation de paiement";
                    
                    $portefeuilleTransaction->reservation_id = $reservation->id;
                    $portefeuilleTransaction->payment_method = $validatedData['payment_method'];
                    $portefeuilleTransaction->id_transaction = $validatedData['id_transaction']??null;
                    $portefeuilleTransaction->portfeuille_id = $portefeuille->id;
                   
                    $portefeuilleTransaction->save();
                    (new ReservationController())->initialisePortefeuilleTransaction($portefeuilleTransaction->id);
                }

                DB::commit();

                return response()->json([
                    'message' => 'Paiement effectué avec succès',
                    'reservation' => $reservation,
                    'montant_a_payer' => $required_paid_value
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Une erreur s\'est produite : ' . $e->getMessage()], 500);
            }
        }


}
