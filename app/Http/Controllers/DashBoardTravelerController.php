<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                                ->whereColumn('valeur_payee', '<', 'montant_total')
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

}
