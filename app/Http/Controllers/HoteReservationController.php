<?php

namespace App\Http\Controllers;
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
use App\Models\User_right;
use App\Models\Right;
use App\Models\Reservation;
use App\Models\Payement;
use App\Models\Portfeuille;
use App\Models\Portfeuille_transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmailwithoutfile;
use DateTime;
use App\Mail\NotificationEmail;
use Exception;

class HoteReservationController extends Controller
{
/**
     * @OA\Get(
     *     path="/api/reservation/reservationsConfirmedByHost",
     *     summary="Liste des réservations confirmées par l'hote connecté",
     * description="Liste des réservations confirmées par l'hote connecté",
     *     tags={"Dashboard hote"},
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
            ->where('is_rejected_hote',0)
            ->where('statut', 'payee');

        })->with(['housing','user'])->get();
    
        return response()->json(['data' => $reservations]);
    }
    
         /**
         * @OA\Get(
         *     path="/api/reservation/reservationsRejectedByHost",
         *     summary="Liste des réservations rejetées par l'hote connecté",
         * description="Liste des réservations rejetées par l'hote connecté",
         *     tags={"Dashboard hote"},
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
                ->where('statut', 'payee')

                ->where('is_rejected_hote',1);
            })->with(['housing','user'])->get();
            return response()->json(['data' => $reservations]);
        }
        
    
         /**
         * @OA\Get(
         *     path="/api/reservation/reservationsCanceledByTravelerForHost",
         *     summary="Liste des réservations appartenant à l'hôte connecté annulées par le voyageur",
         * description="Liste des réservations appartenant à l'hôte connecté annulées par le voyageur",
         *     tags={"Dashboard hote"},
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
                ->where('is_rejected_hote',0)
                ->where('statut', 'payee');

            })->with(['housing','user'])->get();
            return response()->json(['data' => $reservations]);
        }

        /**
     * @OA\Get(
     *     path="/api/reservation/reservationsNotConfirmedYetByHost",
     *     summary="Liste des réservations en attente de confirmation pour l'hote connecté",
     * description="Liste des réservations en attente de confirmation pour l'hote connecté",
     *     tags={"Dashboard hote"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des réservations en attente de confirmation pour l'hote connecté"
     *     )
     * )
     */
    public function reservationsNotConfirmedYetByHost()
    {
        $hostId = Auth::user()->id;
        $reservations = Reservation::whereHas('housing', function ($query) use ($hostId) {
            $query->where('user_id', $hostId)
            ->where('is_deleted',0)
            ->where('is_blocked',0)
            ->where('is_confirmed_hote',0)
            ->where('is_rejected_traveler',0)
            ->where('is_rejected_hote',0)
            ->where('statut', 'payee');

        })->with(['housing','user'])->get();

        
    
        return response()->json(['data' => $reservations]);
    }

    

}
