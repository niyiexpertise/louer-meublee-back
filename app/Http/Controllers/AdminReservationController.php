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


class AdminReservationController extends Controller
{
    


                /**
     * @OA\Get(
     *     path="/api/reservation/housing_with_many_reservation",
     *     summary="Top 10 des logements avec le plus grand nombre de reservation",
     * description="Top 10 des logements avec le plus grand nombre de reservation",
     *     tags={"Reservation"},
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
     *     tags={"Reservation"},
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
     *     tags={"Reservation"},
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
     *     path="/api/reservation/getReservationsByHousingId/{housingId}",
     *     summary="Liste et nombres des réservations pour un logement donné",
     * description="Liste et nombres des réservations pour un logement doinné",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="housingId",
     *         in="path",
     *         required=true,
     *         description="Get housing ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste et nombres des réservations pour un logement doinné"
     *
     *     )
     * )
     */
public function getReservationsByHousingIdForAdmin($housingId)
{

    $housing = Housing::findOrFail($housingId);

    $reservations = Reservation::where('housing_id', $housingId)->get();

    $reservationCount = $reservations->count();

    return response()->json( [
        'housing' => $housing,
        'reservations' => $reservations,
        'reservation_count' => $reservationCount,
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

}