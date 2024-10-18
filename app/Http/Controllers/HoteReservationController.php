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
         * 
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

        foreach($reservations as $reservation){
            $reservation->is_solde = ($reservation->is_tranche_paiement==1 &&$reservation->valeur_payee >= $reservation->montant_a_paye);
        }

        return response()->json(['data' => $reservations]);
    }

    private function nombre_client_de_hote(){
        $hostId = Auth::user()->id;
        $reservations = Reservation::whereHas('housing', function ($query) use ($hostId) {
            $query->where('user_id', $hostId)
            ->where('is_deleted',0)
            ->where('is_blocked',0)
            ->where('is_integration', true)
            ->where('is_confirmed_hote',true)
            ->where('is_rejected_traveler',0)
            ->where('is_rejected_hote',0)
            ->where('statut', 'payee');
        })->with(['user'])->get();

        $clients = [];

        foreach($reservations as $reservation){
            if(!in_array($reservation->user, $clients)){
                $clients[] = $reservation->user;
            }
        }

        return $clients;
    }

    /**
 * @OA\Get(
 *     path="/api/reservation/hoteStatistique",
 *  security={{"bearerAuth": {}}},
 *     tags={"Dashboard hote"},
 *     summary="Récupère les statistiques pour l'hôte",
 *     description="Cette route permet de récupérer diverses statistiques liées aux réservations et aux logements de l'hôte connecté.",
 *     @OA\Response(
 *         response=200,
 *         description="Statistiques de l'hôte récupérées avec succès",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="nombre_reservation_confirme_par_hote", type="integer", example=5),
 *                 @OA\Property(property="nombre_reservation_rejete_par_hote", type="integer", example=2),
 *                 @OA\Property(property="nombre_reservation_de_hote_annule_par_traveler", type="integer", example=1),
 *                 @OA\Property(property="nombre_reservation_en_attente_de_confirmation_pour_hote", type="integer", example=3),
 *                 @OA\Property(property="nombre_logement_de_hote", type="integer", example=10),
 *                 @OA\Property(property="nombre_logement_de_hote_desactive", type="integer", example=1),
 *                 @OA\Property(property="nombre_logement_inacheve_de_hote", type="integer", example=0),
 *                 @OA\Property(property="nombre_clients", type="integer", example=7)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Non autorisé")
 *         )
 *     )
 * )
 */


    public function hoteStatistique(){
        try {
            $data[] = [
                "nombre_reservation_confirme_par_hote" => count($this->reservationsConfirmedByHost()->original['data']),
                "nombre_reservation_rejete_par_hote" => count($this->reservationsRejectedByHost()->original['data']),
                "nombre_reservation_de_hote_annule_par_traveler" => count($this->reservationsCanceledByTravelerForHost()->original['data']),
                "nombre_reservation_en_attente_de_confirmation_pour_hote" => count($this->reservationsNotConfirmedYetByHost()->original['data']),
                "nombre_logement_de_hote" => count((new HousingController())->getHousingForHote()->original['data']),
                "nombre_logement_de_hote_desactive" => count((new HousingController())->getHousingDisabledByHote()->original['data']),
                "nombre_logement_inacheve_de_hote" => count((new HousingController())->HousingHoteInProgress()->original['data']),
                "nombre_clients" =>count($this->nombre_client_de_hote())
            ];

            return (new ServiceController())->apiResponse(200,$data, "Statistique de l'hôte.");
                } catch(Exception $e) {
                     return (new ServiceController())->apiResponse(500,[],$e->getMessage());
                }
    }

}
