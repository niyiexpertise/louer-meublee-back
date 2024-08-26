<?php

namespace App\Http\Controllers;

use App\Models\Housing;
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
         *     path="/api/reservation/getUnpaidReservationsForTraveler",
         *     summary="Liste des réservations non payées pour le voyageur connecté",
         *     tags={"Dashboard traveler"},
         * security={{"bearerAuth": {}}},
         *     @OA\Response(
         *         response=200,
         *         description="Liste des réservations non payées pour le voyageur connecté.",
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
        public function getUnpaidReservationsForTraveler()
        {
            try {
                $userId = Auth::id();

                $data["data"] = Reservation::where('user_id', $userId)->where('statut', 'non_payee')->get();
                $data["nombre"] = count($data["data"]) ;


                return (new ServiceController())->apiResponse(200, $data, 'Liste des réservations non payées pour le voyageur connecté.');
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
                                ->where('statut', 'payee')
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
                                ->where('statut', 'payee')
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
         *     summary="Liste des réservations annulées par l'hôte",
         *     tags={"Dashboard traveler"},
         * security={{"bearerAuth": {}}},
         *     @OA\Response(
         *         response=200,
         *         description="Liste des réservations annulées par l'hôte.",
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
                                   ->where('statut', 'payee')
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
                ->where('statut', 'payee')
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
         *     summary="Liste des réservations en attente de confirmation",
         *     tags={"Dashboard traveler"},
         *  security={{"bearerAuth": {}}},
         *     @OA\Response(
         *         response=200,
         *         description="Liste des réservations en attente de confirmation.",
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
                                    ->where('statut', 'payee')
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
 *                @OA\Property(
 *                     property="valeur_payee",
 *                     description="Statut du paiement (facultatif)",
 *                     type="integer",
 *                     example=1000
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
 *                 property="valeur_payee",
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
                'valeur_payee' => 'nullable|numeric'
            ]);

            $user_id = Auth::id();
            $reservation = Reservation::find($validatedData['reservation_id']);

            if (!$reservation) {
                return response()->json(['message' => 'Réservation non trouvée'], 404);
            }

            if (!$reservation->is_tranche_paiement) {
                return (new ServiceController())->apiResponse(404, [], "Vous ne pouvez pas terminer le paiement d'une reservation qui ne peut  être payé par tranche.");
            }

            if ($reservation->montant_a_paye == $reservation->valeur_payee) {
                return (new ServiceController())->apiResponse(404, [], 'Logement déjà soldé .');
            }

            $existPremierTranche = Portfeuille_transaction::where('reservation_id',$request->reservation_id)->exists();
                    if (!$existPremierTranche) {
                        return (new ServiceController())->apiResponse(404, [], "Payé d'abord la première tranche avant de solder cette réservation.");
                    }


            $required_paid_value = $reservation->montant_a_paye / 2;

            $method_paiement = (new ReservationController())->findSimilarPaymentMethod($request->payment_method);

            $portfeuille = (new ReservationController())->findSimilarPaymentMethod("portfeuille");

            $espece = (new ReservationController())->findSimilarPaymentMethod("espece");

            DB::beginTransaction();

            try {
                $reservation->valeur_payee += $required_paid_value;
                $reservation->save();

                $payment = new Payement();
                $payment->reservation_id = $reservation->id;
                $payment->amount = $request->valeur_payee;
                $payment->payment_method = $method_paiement;
                $payment->id_transaction = $request->id_transaction;
                $payment->statut = $request->statut_paiement;
                $payment->is_confirmed = true;
                $payment->is_canceled = false;

                // return  $request->valeur_payee;

                if ($method_paiement == $portfeuille) {

                    if ($reservation->user_id != $user_id) {
                        return (new ServiceController())->apiResponse(403, [], "Vous n'êtes pas autorisé à effectuer ce paiement");
                    }

                    $portefeuille = Portfeuille::where('user_id', $user_id)->first();
                    if($portefeuille->solde < $required_paid_value){
                        return (new ServiceController())->apiResponse(404, [], "Vous n'avez pas assez d'argent sur votre portefeuille pour effectuer ce paiement");
                    }
                    $portefeuille->solde -= $required_paid_value;

                    $portefeuilleTransaction = new Portfeuille_transaction();
                    $portefeuilleTransaction->debit = false;
                    $portefeuilleTransaction->credit = false;
                    $portefeuilleTransaction->amount = $required_paid_value;
                    $portefeuilleTransaction->motif = "Finalisation de paiement avec le portefeuille";

                    $portefeuilleTransaction->reservation_id = $reservation->id;
                    $portefeuilleTransaction->payment_method = $request->payment_method;
                    $portefeuilleTransaction->operation_type = 'debit';
                    $portefeuilleTransaction->id_transaction = "0";
                    $portefeuilleTransaction->portfeuille_id = $portefeuille->id;

                    $portefeuilleTransaction->save();
                    $portefeuille->save();
                    $portefeuilleTransaction->save();
                    (new ReservationController())->initialisePortefeuilleTransaction($portefeuilleTransaction->id);

                }else if($method_paiement == $espece){

                    $portefeuilleTransaction = new Portfeuille_transaction();
                    $portefeuilleTransaction->debit = false;
                    $portefeuilleTransaction->credit = false;
                    $portefeuilleTransaction->amount = $required_paid_value;
                    $portefeuilleTransaction->motif = "Finalisation de paiement (reçu en espèce par l'hote)";

                    $portefeuilleTransaction->reservation_id = $reservation->id;
                    $portefeuilleTransaction->payment_method = $request->payment_method;
                    $portefeuilleTransaction->id_transaction = "0";

                    $portefeuilleTransaction->save();
                    (new ReservationController())->initialisePortefeuilleTransaction($portefeuilleTransaction->id);

                }else {

                    $existTransaction = Payement::where('id_transaction',$request->id_transaction)->exists();
                    if ($existTransaction) {
                        return $this->apiResponse(404, [], 'L\'id de la transaction existe déjà');
                    }

                    $portefeuille = Portfeuille::where('user_id', $reservation->user_id)->first();

                    if(!$request->valeur_payee){
                        return (new ServiceController())->apiResponse(404, [], "Vous devez saisir la valeur à payer");
                    }

                    if($request->valeur_payee < $required_paid_value){
                        return (new ServiceController())->apiResponse(404, [], "Montant insuffisant. Vous devez payer $required_paid_value FCFA ou plus");
                    }


                    $portefeuilleTransaction = new Portfeuille_transaction();
                    $portefeuilleTransaction->credit = true;
                    $portefeuilleTransaction->debit = false;
                    $portefeuilleTransaction->amount = $request->valeur_payee ;
                    $portefeuilleTransaction->motif = "Finalisation de paiement par un moyen de paiement autre que portefeuille et  espece";
                    $portefeuilleTransaction->reservation_id = $reservation->id;
                    $portefeuilleTransaction->payment_method = $request->payment_method;
                    $portefeuilleTransaction->id_transaction =$request->id_transaction;

                    $portefeuilleTransaction->save();
                    (new ReservationController())->initialisePortefeuilleTransaction($portefeuilleTransaction->id);
                }

                DB::commit();
                $data = [
                    'reservation' => $reservation,
                ];

                return (new ServiceController())->apiResponse(200, $data, "Paiement effectué avec succès");

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Une erreur s\'est produite : ' . $e->getMessage()], 500);
            }
        }

        /**
 * @OA\Get(
 *     path="/api/reservation/showDetailReservation/{reservationId}",
 *  security={{"bearerAuth": {}}},
 *     summary="Afficher les détails d'une réservation",
 *     description="Récupère les détails d'une réservation spécifique par ID. Seul le propriétaire de la réservation peut accéder à ces détails.",
 *     operationId="showDetailReservation",
 *     tags={"Dashboard traveler"},
 *     @OA\Parameter(
 *         name="reservationId",
 *         in="path",
 *         required=true,
 *         description="ID de la réservation",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Détails de la réservation récupérés avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="detail de la reservation",
 *                 description="Les détails de la réservation",
 *                 type="string", example="[]"
 *
 *             ),
 *             @OA\Property(
 *                 property="voyageur",
 *                 description="Les détails du voyageur",
 *                 type="string", example="[]"
 *
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Réservation non trouvée ou l'utilisateur n'est pas autorisé à accéder à cette réservation",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Reservation non trouvée ou Vous ne pouvez pas consulter les détails d'une réservation qui ne vous concerne pas."
 *             )
 *         )
 *     ),
 *     @OA\SecurityScheme(
 *         securityScheme="BearerToken",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT"
 *     )
 * )
 */
    public function showDetailReservation($reservationId){
        $reservation = Reservation::find($reservationId);
         if(!$reservation){
             return (new ServiceController())->apiResponse(404,[], "Reservation non trouvée. ");

         }

         if (!(Auth::user()->id == $reservation->user_id)) {
              return (new ServiceController())->apiResponse(404,[], "Vous ne pouvez pas consulter les détails d' une réservation qui ne vous concerne pas. ");
         }

         $data = [
            'detail de la reservation' => $reservation->toArray(),
            'propriétaire' => [
                'id' => Housing::whereId(Reservation::find($reservationId)->housing_id)->first()->user->id,
                'nom' => Housing::whereId(Reservation::find($reservationId)->housing_id)->first()->user->lastname,
                'prenom' => Housing::whereId(Reservation::find($reservationId)->housing_id)->first()->user->firstname,
            ]
         ];


       return (new ServiceController())->apiResponse(200,$data, 'Detail de reservation recupéré avec succès');
    }


}
