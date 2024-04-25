<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HoteReservationController extends Controller
{
/**
     * @OA\Get(
     *     path="/api/reservation/reservationsConfirmedByHost",
     *     summary="Liste des réservations confirmées par l'hote connecté",
     * description="Liste des réservations confirmées par l'hote connecté",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des réservations confirmées par l'hote connecté"
     *     )
     * )
     */
    public function reservationsConfirmedByHost()
    {
        $hostId = Auth::user()->id;
        $reservations = Reservation::whereHas('housing', function ($query) use ($hostId) {
            $query->where('user_id', $hostId)
            ->where('is_deleted',0)
            ->where('is_blocked',0)
            ->where('is_confirmed_hote',1)
            ->where('is_rejected_traveler',0)
            ->where('is_rejected_hote',0);
        })->with(['housing','user'])->get();
    
        return response()->json(['data' => $reservations]);
    }
    
         /**
         * @OA\Get(
         *     path="/api/reservation/reservationsRejectedByHost",
         *     summary="Liste des réservations rejetées par l'hote connecté",
         * description="Liste des réservations rejetées par l'hote connecté",
         *     tags={"Reservation"},
         * security={{"bearerAuth": {}}},
         *     @OA\Response(
         *         response=200,
         *         description="Liste des réservations rejetées par l'hote connecté"
         *     )
         * )
         */
        public function reservationsRejectedByHost()
        {
            $hostId = Auth::user()->id;
            $reservations = Reservation::whereHas('housing', function ($query) use ($hostId) {
                $query->where('user_id', $hostId)
                ->where('is_deleted',0)
                ->where('is_blocked',0)
                ->where('is_confirmed_hote',0)
                ->where('is_rejected_traveler',0)
                ->where('is_rejected_hote',1);
            })->with(['housing','user'])->get();
            return response()->json(['data' => $reservations]);
        }
        
    
         /**
         * @OA\Get(
         *     path="/api/reservation/reservationsCanceledByTravelerForHost",
         *     summary="Liste des réservations appartenant à l'hôte connecté annulées par le voyageur",
         * description="Liste des réservations appartenant à l'hôte connecté annulées par le voyageur",
         *     tags={"Reservation"},
         * security={{"bearerAuth": {}}},
         *     @OA\Response(
         *         response=200,
         *         description="Liste des réservations appartenant à l'hôte connecté annulées par le voyageur"
         *     )
         * )
         */
        public function reservationsCanceledByTravelerForHost()
        {
            $hostId = Auth::user()->id;
            $reservations = Reservation::whereHas('housing', function ($query) use ($hostId) {
                $query->where('user_id', $hostId)
                ->where('is_deleted',0)
                ->where('is_blocked',0)
                ->where('is_confirmed_hote',0)
                ->where('is_rejected_traveler',1)
                ->where('is_rejected_hote',0);
            })->with(['housing','user'])->get();
            return response()->json(['data' => $reservations]);
        }

}
