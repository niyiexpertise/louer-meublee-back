<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Charge;
use App\Models\Housing;
use App\Models\housing_preference;
use App\Models\reduction;
use App\Models\promotion;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminReservationController extends Controller
{
    


                /**
     * @OA\Get(
     *     path="/api/reservation/housing_with_many_reservation",
     *     summary="Top 10 des logements avec le plus grand nombre de reservation",
     * description="Top 10 des logements avec le plus grand nombre de reservation",
     *     tags={"Administration"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of housing"
     *
     *     )
     * )
     */
     public function housing_with_many_reservation(){
        $topHousings = Housing::select('housings.id', 'housings.name', DB::raw('COUNT(reservations.id) as reservation_count'))
        ->leftJoin('reservations', 'housings.id', '=', 'reservations.housing_id')
        ->groupBy('housings.id', 'housings.name')
        ->orderByDesc('reservation_count')
        ->limit(10)
        ->get();
        return response()->json([
            'message' => $topHousings
        ]);
     }


   

                         /**
     * @OA\Get(
     *     path="/api/reservation/country_with_many_reservation",
     *     summary="Top 10 des pays avec le plus grand nombre de réservation sur la plateforme",
     * description="Top 10 des pays avec le plus grand nombre de réservation sur la plateforme",
     *     tags={"Administration"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of countries"
     *
     *     )
     * )
     */
     public function country_with_many_reservation(){
        $topCountries = DB::table('reservations')
        ->join('housings', 'reservations.housing_id', '=', 'housings.id')
        ->select('housings.country', DB::raw('COUNT(reservations.id) as reservation_count'))
        ->groupBy('housings.country')
        ->orderByDesc('reservation_count')
        ->limit(10)
        ->get();
        return response()->json([
            'message' => $topCountries
        ]);
     }

     
                         /**
     * @OA\Get(
     *     path="/api/reservation/housing_without_reservation",
     *     summary="Liste des logements avec 0 réservations",
     * description="Liste des logements avec 0 réservations",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of housing"
     *
     *     )
     * )
     */
     public function housing_without_reservation(){
        $unusedHousings = Housing::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                  ->from('reservations')
                  ->whereRaw('reservations.housing_id = housings.id');
        })->with('user')->get();
        return response()->json([
            'message' => $unusedHousings
        ]);
     }


                             /**
     * @OA\Get(
     *     path="/api/reservation/getReservationsCountByYear",
     *     summary="Evolution du Nombre total de réservation au fil des années",
     * description="Evolution du Nombre total de réservation au fil des années",
     *     tags={"Administration"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="number of reservation"
     *
     *     )
     * )
     */
    public function getReservationsCountByYear()
{
    $earliestYear = Reservation::orderBy('date_of_reservation')->value(DB::raw('YEAR(date_of_reservation)'));

    if (!$earliestYear) {
        return response()->json([
            'message' => []
        ]);
    }

    $currentYear = Carbon::now()->year;

    $reservationsByYears = Reservation::select(
            DB::raw('YEAR(date_of_reservation) as year'),
            DB::raw('COUNT(*) as reservation_count')
        )
        ->whereYear('date_of_reservation', '>=', $earliestYear)
        ->whereYear('date_of_reservation', '<=', $currentYear)
        ->groupBy(DB::raw('YEAR(date_of_reservation)'))
        ->orderBy(DB::raw('YEAR(date_of_reservation)'))
        ->get()
        ->toArray();

    $result = [];
    foreach (range($earliestYear, $currentYear) as $year) {
        $reservationCount = 0;
        foreach ($reservationsByYears as $reservation) {
            if ($reservation['year'] == $year) {
                $reservationCount = $reservation['reservation_count'];
                break;
            }
        }
        $result[] = [
            'year' => $year,
            'reservation_count' => $reservationCount
        ];
    }

    return response()->json([
        'message' => $result
    ]);
}



                         /**
     * @OA\Get(
     *     path="/api/reservation/getAllReservation",
     *     summary="Liste de toutes les réservations de la plateforme",
     * description="Liste de toutes les réservations de la plateforme",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of reservation"
     *
     *     )
     * )
     */
public function getAllReservation(){

    $reservations = Reservation::where('is_deleted', false)->with(['user','housing'])->get();
    return response()->json([
        'message' => $reservations
    ]);
}


                             /**
     * @OA\Get(
     *     path="/api/reservation/getUserReservations/{user}",
     *     summary="Liste et nombres des réservations d'un voyageur",
     * description="Liste et nombres des réservations d'un voyageur",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="Get user ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste et nombres des réservations d'un voyageur"
     *
     *     )
     * )
     */
public function getUserReservationsForAdmin(User $user)
{
    $reservations = $user->reservation()->get();
    $detail = [];
    foreach ($reservations as $reservation) {
        $detail[] = [
            'reservations' => $reservation,
            'propretaire du logement' =>$reservation->housing->user,
            'logement' => $reservation->housing
        ];
    }
    $reservationCount = $reservations->count();

    return response()->json([
        'message' => [
            'details' => $detail,
            'reservation_count' => $reservationCount
        ]
    ]);
}


    /**
     * @OA\Get(
     *     path="/api/reservation/showDetailOfReservation/{idReservation}",
     *     summary="Détail d'une réservation donnée coté admin",
     * description="Détail d'une réservation donée admin",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="idReservation",
     *         in="path",
     *         required=true,
     *         description="Get reservation ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détail d'une réservation côté Admin"
     *     )
     * )
     */
function showDetailOfReservationForAdmin($idReservation){
    $reservation = Reservation::find($idReservation);
    if(!$reservation){
        return response()->json([
            'message' => 'Reservation not found'
        ], 404);
    }
    return response()->json([
        'data' =>[
            ' detail de la reservation' => $reservation->toArray(),
            'logement' => $reservation->housing->toArray(),
            'proprietaire_du_logement_reserve' => $reservation->housing->user->toArray(),
            'voyageur' => $reservation->user->toArray()
        ]
    ]);
}

/**
     * @OA\Get(
     *     path="/api/reservation/topTravelersWithMostReservations",
     *     summary="Top 10 des utilisateurs(voyageurs) avec le plus grand nombre de réservations",
     * description="Top 10 des utilisateurs(voyageurs) avec le plus grand nombre de réservations",
     *     tags={"Administration"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="op 10 des utilisateurs(voyageurs) avec le plus grand nombre de réservations"
     *
     *     )
     * )
     */

     public function topTravelersWithMostReservations()
     {
         $topTravelers = User::select('users.id', 'users.firstname', 'users.lastname', DB::raw('COUNT(reservations.id) as reservation_count'))
                             ->whereHas('reservation')
                             ->leftJoin('reservations', 'users.id', '=', 'reservations.user_id')
                             ->groupBy('users.id', 'users.firstname', 'users.lastname')
                             ->orderByDesc('reservation_count')
                             ->limit(10)
                             ->get();
     
         // Retourner le top 10 des voyageurs avec le plus grand nombre de réservations
         return response()->json(['data' => $topTravelers]);
     }


    /**
     * @OA\Get(
     *     path="/api/reservation/getAllReservationConfirmedForAdmin",
     *     summary="Liste de toutes les réservations confirmées par les hotes de la plateforme(admin)",
     * description="Liste de toutes les réservations confirmées par les hotes de la plateforme(admin)",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste de toutes les réservations confirmées par les hotes de la plateforme(admin)"
     *
     *     )
     * )
     */
public function getAllReservationConfirmedForAdmin(){

    $reservations = Reservation::where('is_deleted', false)
    ->where('is_blocked',0)
    ->where('is_confirmed_hote',1)
    ->where('is_rejected_traveler',0)
    ->where('is_rejected_hote',0)
    ->get();
    $formattedReservations = $reservations->map(function ($reservation) {
        return [
            'reservation' => $reservation->toArray(),
            'voyageur' => $reservation->user->toArray(),
            'housing' =>$reservation->housing->toArray(),
            'hote' => $reservation->housing->user->toArray(),
        ];
    });

    return response()->json([
        'message' => $formattedReservations
    ]);
}


                         /**
     * @OA\Get(
     *     path="/api/reservation/getAllReservationRejectedForAdmin",
     *     summary="Liste de toutes les réservations rejetées par les hotes de la plateforme(admin)",
     * description="Liste de toutes les réservations rejetées par les hotes de la plateforme(admin)",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste de toutes les réservations rejetées par les hotes de la plateforme(admin)"
     *
     *     )
     * )
     */
    public function getAllReservationRejectedForAdmin(){

        $reservations = Reservation::where('is_deleted', false)
        ->where('is_blocked',0)
        ->where('is_confirmed_hote',0)
        ->where('is_rejected_traveler',0)
        ->where('is_rejected_hote',1)
        ->get();
        $formattedReservations = $reservations->map(function ($reservation) {
            return [
                'reservation' => $reservation->toArray(),
                'voyageur' => $reservation->user->toArray(),
                'housing' =>$reservation->housing->toArray(),
                'hote' => $reservation->housing->user->toArray(),
            ];
        });

        return response()->json([
            'message' => $formattedReservations
        ]);
    }

    
                         /**
     * @OA\Get(
     *     path="/api/reservation/getAllReservationCanceledByTravelerForAdmin(admin)",
     *     summary="Liste de toutes les réservations annuler par les voyageurs de la plateforme(admin)",
     * description="Liste de toutes les réservations annuler par les voyageurs de la plateforme(admin)",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste de toutes les réservations annuler par les voyageurs de la plateforme(admin)"
     *     )
     * )
     */
public function getAllReservationCanceledByTravelerForAdmin(){

    $reservations = Reservation::where('is_deleted', false)
    ->where('is_blocked', 0)
    ->where('is_confirmed_hote', 0)
    ->where('is_rejected_traveler', 1)
    ->where('is_rejected_hote', 0)
    ->get();

        $formattedReservations = $reservations->map(function ($reservation) {
            return [
                'reservation' => $reservation->toArray(),
                'voyageur' => $reservation->user->toArray(),
                'housing' =>$reservation->housing->toArray(),
                'hote' => $reservation->housing->user->toArray(),
            ];
        });

        return response()->json([
            'message' => $formattedReservations
        ]);


    }

/**
 * @OA\Get(
 *     path="/api/reservation/getReservationsCountByYearAndMonth",
 *     summary="Obtenir le nombre de réservations par année et par mois",
 *     description="Récupère le nombre de réservations par année et par mois. Fournit le total annuel et le détail mensuel pour chaque année.",
 *     tags={"Administration"},
 *  security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Données récupérées avec succès.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="Message de retour"
 *             ),
 *             @OA\Property(
 *                 property="reservations_by_year_and_month",
 *                 type="array",
 *                 description="Détails des réservations par année et par mois",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(
 *                         property="year",
 *                         type="integer",
 *                         description="Année"
 *                     ),
 *                     @OA\Property(
 *                         property="reservations_per_month",
 *                         type="array",
 *                         description="Nombre de réservations par mois",
 *                         @OA\Items(
 *                             type="integer",
 *                             description="Nombre de réservations"
 *                         )
 *                     ),
 *                     @OA\Property(
 *                         property="total_reservations",
 *                         type="integer",
 *                         description="Nombre total de réservations pour l'année"
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucune réservation trouvée.",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="Message d'erreur"
 *             )
 *         )
 *     )
 * )
 */
public function getReservationsCountByYearAndMonth()
{
 
    $monthNames = [
        1 => 'janvier',
        2 => 'février',
        3 => 'mars',
        4 => 'avril',
        5 => 'mai',
        6 => 'juin',
        7 => 'juillet',
        8 => 'août',
        9 => 'septembre',
        10 => 'octobre',
        11 => 'novembre',
        12 => 'décembre',
    ];

    $earliestYear = Reservation::orderBy('date_of_reservation')->value(DB::raw('YEAR(date_of_reservation)'));

    if (!$earliestYear) {
        return response()->json([
            'message' => 'Aucune réservation trouvée.',
        ], 404);
    }

    $currentYear = Carbon::now()->year;

    // Récupérer les données de réservation par année et mois
    $reservationsByYearAndMonth = Reservation::select(
        DB::raw('YEAR(date_of_reservation) as year'),
        DB::raw('MONTH(date_of_reservation) as month'),
        DB::raw('COUNT(*) as reservation_count')
    )
    ->whereYear('date_of_reservation', '>=', $earliestYear)
    ->whereYear('date_of_reservation', '<=', $currentYear)
    ->groupBy(DB::raw('YEAR(date_of_reservation)'), DB::raw('MONTH(date_of_reservation)'))
    ->orderBy(DB::raw('YEAR(date_of_reservation)'))
    ->orderBy(DB::raw('MONTH(date_of_reservation)'))
    ->get();

    $result = [];
    foreach (range($earliestYear, $currentYear) as $year) {
        $monthlyCounts = [];
        foreach ($monthNames as $num => $name) {
            $monthlyCounts[$name] = 0;
        }

        foreach ($reservationsByYearAndMonth as $reservation) {
            if ($reservation->year == $year) {
                $monthName = $monthNames[$reservation->month];
                $monthlyCounts[$monthName] = $reservation->reservation_count;
            }
        }

        $result[] = [
            'year' => $year,
            'reservations_per_month' => $monthlyCounts,
            'total_reservations' => array_sum($monthlyCounts),
        ];
    }

    return response()->json([
        'message' => 'Données récupérées avec succès.',
        'reservations_by_year_and_month' => $result,
    ], 200);
}


}