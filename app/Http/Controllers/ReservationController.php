<?php

namespace App\Http\Controllers;

use App\Models\Housing;
use App\Models\Notification;
use App\Models\Portfeuille;
use App\Models\portfeuille_transaction;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
          try{
            $reservations = Reservation::where('is_deleted', false)->get();
            return response()->json(['data' => $reservations], 200);
            }catch(ValidationException $e) {
        return response()->json([
            'error' => 'Validation failed',
            'message' => $e->validator->errors()->first()
        ], 422);
    } catch(Exception $e) {
        return response()->json([
            'error' => 'An error occurred',
            'message' => $e->getMessage()
        ], 500);
    }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $request->validate([
                'housing_id' => 'required|exists:housings,id',
                'date_of_reservation' => 'required|date',
                'date_of_starting' => 'required|date',
                'date_of_end' => 'required|date',
                'number_of_adult' => 'required|integer',
                'number_of_child' => 'required|integer',
                'number_of_baby' => 'required|integer',
                'number_of_domestical_animal' => 'required|integer',
            ]);
            $reservation = new Reservation();
            $reservation->user_id = Auth::user()->id;
            $reservation->housing_id = $request->housing_id;
            $reservation->date_of_reservation = $request->date_of_reservation;
            $reservation->date_of_starting = $request->date_of_starting;
            $reservation->date_of_end = $request->date_of_end;
            $reservation->number_of_adult = $request->number_of_adult;
            $reservation->number_of_child = $request->number_of_child;
            $reservation->number_of_baby = $request->number_of_baby;
            $reservation->number_of_domestical_animal = $request->number_of_domestical_animal;
            $reservation->save();
            return response()->json(['data' => [
                'message' => 'Reservation effectuee avec succès.',
                'reservation' => $reservation
            ]], 200);
        }catch(ValidationException $e) {
        return response()->json([
            'error' => 'Validation failed',
            'message' => $e->validator->errors()->first()
        ], 422);
    } catch(Exception $e) {
        return response()->json([
            'error' => 'An error occurred',
            'message' => $e->getMessage()
        ], 500);
    }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
                $reservation = Reservation::find($id);
                if (!$reservation) {
                    return response()->json(['error' => 'Reservation non trouvé.'], 404);
                }

            return response()->json(['data' => $reservation], 200);
        }catch(ValidationException $e) {
        return response()->json([
            'error' => 'Validation failed',
            'message' => $e->validator->errors()->first()
        ], 422);
    } catch(Exception $e) {
        return response()->json([
            'error' => 'An error occurred',
            'message' => $e->getMessage()
        ], 500);
    }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            $data = $request->validate([
                'user_id' => 'required|exists:users,id',
                'housing_id' => 'required|exists:housings,id',
                'date_of_reservation' => 'required|date',
                'date_of_starting' => 'required|date',
                'date_of_end' => 'required|date',
                'number_of_adult' => 'required|integer',
                'number_of_child' => 'required|integer',
                'number_of_baby' => 'required|integer',
                'number_of_domestical_animal' => 'required|integer',
            ]);
            Reservation::whereId($id)->update($data);
            return response()->json(['data' => 'Reservation mise à jour avec succès.'], 200);
    
        }catch(ValidationException $e) {
        return response()->json([
            'error' => 'Validation failed',
            'message' => $e->validator->errors()->first()
        ], 422);
    } catch(Exception $e) {
        return response()->json([
            'error' => 'An error occurred',
            'message' => $e->getMessage()
        ], 500);
    }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
                $reservation = Reservation::find($id);
                if (!$reservation) {
                    return response()->json(['error' => 'Reservation non trouvé.'], 404);
                }
                $reservation = Reservation::whereId($id)->update(['is_deleted' => true]);
                return response()->json(['message' => 'Reservation deleted successfully'], 404);
        }catch(ValidationException $e) {
        return response()->json([
            'error' => 'Validation failed',
            'message' => $e->validator->errors()->first()
        ], 422);
    } catch(Exception $e) {
        return response()->json([
            'error' => 'An error occurred',
            'message' => $e->getMessage()
        ], 500);
    }
    }

    public function block(string $id)
    {
        try{
                $reservation = Reservation::find($id);
                if (!$reservation) {
                    return response()->json(['error' => 'Reservation non trouvé.'], 404);
                }
                $reservation = Reservation::whereId($id)->update(['is_blocked' => true]);
                return response()->json(['message' => 'Reservation blocked successfully'], 404);
        }catch(ValidationException $e) {
        return response()->json([
            'error' => 'Validation failed',
            'message' => $e->validator->errors()->first()
        ], 422);
    } catch(Exception $e) {
        return response()->json([
            'error' => 'An error occurred',
            'message' => $e->getMessage()
        ], 500);
    }
    }

    public function unblock(string $id)
    {
        try{
                $reservation = Reservation::find($id);
                if (!$reservation) {
                    return response()->json(['error' => 'Reservation non trouvé.'], 404);
                }
                $reservation = Reservation::whereId($id)->update(['is_blocked' => false]);
                return response()->json(['message' => 'Reservation unblocked successfully'], 404);
        }catch(ValidationException $e) {
        return response()->json([
            'error' => 'Validation failed',
            'message' => $e->validator->errors()->first()
        ], 422);
    } catch(Exception $e) {
        return response()->json([
            'error' => 'An error occurred',
            'message' => $e->getMessage()
        ], 500);
    }
    }



          /**
     * @OA\Put(
     *     path="/api/reservation/hote_confirm_reservation/{idReservation}",
     *     summary="Confirmer la reservation d un voyageur sur un de ses biens",
     * description="Confirmer la reservation d un voyageur sur un de ses biens",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="idReservation",
     *         in="path",
     *         required=true,
     *         description="ID of the reservation",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="reservation confirmed successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="reservation not found"
     *     )
     * )
     */
    public function hote_confirm_reservation($idReservation){
        try{
          $reservation = Reservation::find($idReservation);
          if(!$reservation){
            return response()->json([
                'message' => 'Reservation not found'
            ], 404);
          }
          if (!($reservation->housing->user_id == Auth::user()->id)) {
            return response()->json([
                'message' => 'Impossible de confirmer la réservation d un logement qui ne vous appartient pas'
            ]);
          }
          if ($reservation->is_rejected_hote) {
            return response()->json([
                'message' => 'Vous ne pouvez pas confirmer une réservation déjà rejeter'
            ]);
          }
          if ($reservation->is_confirmed_hote) {
            return response()->json([
                'message' => 'Reservation already confirmed'
            ]);
          }
          Reservation::whereId($idReservation)->update(['is_confirmed_hote'=>1]);
          $notification = new Notification();
          $notification->user_id = $reservation->user_id;
          $notification->name = 'Votre reservation vient d\être confirmée par l\'hôte';
          $notification->save();
          return response()->json([
            'message' => 'Reservation confirmed successfully'
        ]);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ], 500);
        }

     }

               /**
     * @OA\Put(
     *     path="/api/reservation/hote_reject_reservation/{idReservation}",
     *     summary="Rejeter la reservation d un voyageur sur un de ses biens avec un motif à l'appui",
     * description="Rejeter la reservation d un voyageur sur un de ses biens avec un motif à l'appui",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="idReservation",
     *         in="path",
     *         required=true,
     *         description="ID of the reservation",
     *         @OA\Schema(type="integer")
     *     ),
  *@OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"motif_rejet_hote"},
     *             @OA\Property(property="motif_rejet_hote", type="string", example="motif")
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="reservation rejected successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="reservation not found"
     *     )
     * )
     */
     public function hote_reject_reservation($idReservation, Request $request){
        try{
            $request->validate([
                'motif_rejet_hote' =>'required | string',
            ]);
            $reservation = Reservation::find($idReservation);
          if(!$reservation){
            return response()->json([
                'message' => 'Reservation not found'
            ], 404);
          }
          if (!($reservation->housing->user_id == Auth::user()->id)) {
            return response()->json([
                'message' => 'Impossible de rejeter la réservation d un logement qui ne vous appartient pas'
            ]);
          }
          if ($reservation->is_confirmed_hote) {
            return response()->json([
                'message' => 'Vous ne pouvez pas rejeter une réservation déjà confirmer'
            ]);
          }

          if ($reservation->is_rejected_hote) {
            return response()->json([
                'message' => 'Reservation déjà rejeté'
            ]);
          }
          if(Reservation::whereId($idReservation)->update([
            'is_rejected_hote'=>1,
            'motif_rejet_hote'=>$request->motif_rejet_hote
          ])){

              $portfeuille =Portfeuille::where('user_id',$reservation->user_id)->first();
              $portfeuille->where('user_id',$reservation->user_id)->update(['solde'=>$reservation->user->portfeuille->solde + $reservation->montant_total]);
            $transaction = new portfeuille_transaction();
            $transaction->portfeuille_id = $portfeuille->id;
            $transaction->amount = $reservation->montant_total;
            $transaction->debit = 0;
            $transaction->credit =1;
            $transaction->reservation_id = $reservation->id;
            $transaction->payment_method = "portfeuille";
            $transaction->motif = "Remboursement suite à un rejet de la réservation par l'hôte";
            $transaction->save();
          }
          $notification = new Notification();
          $notification->user_id = $reservation->user_id;
          $notification->name = 'Votre reservation concernant '.$reservation->housing->name.'  vient d\être rejetée  par l\'hôte pour le motif suivant << '.$request->motif_rejet_hote. ">>";
          $notification->save();
          $adminUsers = User::where('is_admin', 1)->get();
          foreach ($adminUsers as $adminUser) {
              $notification = new Notification();
              $notification->user_id = $adminUser->id;
              $notification->name = 'Une reservation vient d\être annulé  par l hote pour le motif suivant << '.$request->motif_rejet_hote. ">>  et le logement appartient à ".$reservation->housing->user->firstname." ".$reservation->housing->user->lastname." il a pour identifiant ".$reservation->housing->user->id;
              $notification->save();
          }
          return response()->json([
            'message' => 'Reservation rejected successfully'
        ]);


          } catch(Exception $e) {
              return response()->json([
                  'error' => 'An error occurred',
                  'message' => $e->getMessage()
              ], 500);
          }
     }


              /**
     * @OA\Put(
     *     path="/api/reservation/traveler_reject_reservation/{idReservation}",
     *     summary="Annulation d une reservation par un voyageur avec le motif à l'appui",
     * description="Annulation d une reservation par un voyageur avec le motif à l'appui",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="idReservation",
     *         in="path",
     *         required=true,
     *         description="ID of the reservation",
     *         @OA\Schema(type="integer")
     *     ),
  *@OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"motif_rejet_traveler"},
     *             @OA\Property(property="motif_rejet_traveler", type="string", example="motif")
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="reservation canceled successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="reservation not found"
     *     )
     * )
     */

     public function traveler_reject_reservation($idReservation, Request $request){
        try{
            $request->validate([
                'motif_rejet_traveler' =>'required | string',
            ]);
            $reservation = Reservation::find($idReservation);
          if(!$reservation){
            return response()->json([
                'message' => 'Reservation not found'
            ], 404);
          }
          if (!($reservation->user_id == Auth::user()->id)) {
            return response()->json([
                'message' => 'Impossible d annuler la réservation d un logement dont vous n avez pas fait la réservation'
            ]);
          }
          if(!($reservation->is_confirmed_hote)) {
            return response()->json([
                'message' => 'Vous ne pouvez pas annuler une réservation qui n est pas confirmer par l hote'
            ]);
          }

          if ($reservation->is_rejected_hote) {
            return response()->json([
                'message' => 'Vous ne pouvez pas confirmer un reservation déjà rejeté'
            ]);
          }
          Reservation::whereId($idReservation)->update([
            'is_rejected_traveler'=>1,
            'motif_rejet_traveler'=>$request->motif_rejet_traveler
          ]);
          $notification = new Notification();
          $notification->user_id = $reservation->housing->user_id;
          $notification->name = 'Une reservation vient d\être annulé  par le client pour le motif suivant << '.$request->motif_rejet_traveler. ">>";
          $notification->save();
          $adminUsers = User::where('is_admin', 1)->get();
                    foreach ($adminUsers as $adminUser) {
                        $notification = new Notification();
                        $notification->user_id = $adminUser->id;
                        $notification->name = 'Une reservation vient d\être annulé  par un client ayant pour id '.$reservation->user->id.'  pour le motif suivant << '.$request->motif_rejet_traveler. ">>  et le logement appartient à ".$reservation->housing->user->firstname." ".$reservation->housing->user->lastname." il a pour identifiant ".$reservation->housing->user->id;
                        $notification->save();
                    }
          return response()->json([
            'message' => 'Reservation canceled successfully'
        ]);


          } catch(Exception $e) {
              return response()->json([
                  'error' => 'An error occurred',
                  'message' => $e->getMessage()
              ], 500);
          }
     }


           /**
     * @OA\Get(
     *     path="/api/reservation/hote_with_many_housing",
     *     summary="Top 10 des utilisateurs(hotes) avec le plus grand nombre de logement",
     * description="Top 10 des utilisateurs(hotes) avec le plus grand nombre de logement",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of hote"
     *
     *     )
     * )
     */
     public function hote_with_many_housing(){
        $topHotes = User::select('users.id', 'users.firstname', 'users.lastname', DB::raw('COUNT(housings.id) as housing_count'))
    ->leftJoin('housings', 'users.id', '=', 'housings.user_id')
    ->groupBy('users.id', 'users.firstname', 'users.lastname')
    ->orderByDesc('housing_count')
    ->limit(10)
    ->get();
    return response()->json([
        'message' => $topHotes
    ]);
     }


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
     *     path="/api/reservation/country_with_many_housing",
     *     summary="Top 10 des pays avec le plus grand nombre de logement sur la plateforme",
     * description="Top 10 des pays avec le plus grand nombre de logement sur la plateforme",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of countries"
     *
     *     )
     * )
     */
     public function country_with_many_housing(){
        $topCountries = Housing::select('country', DB::raw('COUNT(id) as housing_count'))
        ->groupBy('country')
        ->orderByDesc('housing_count')
        ->limit(10)
        ->get();
        return response()->json([
            'message' => $topCountries
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
     *     summary="Nombre total de réservation sur une année",
     * description="Nombre total de réservation sur une année",
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
    $earliestReservation = Reservation::orderBy('date_of_reservation')->first();

    if (!$earliestReservation) {
        return response()->json([
            'message' => [],
            'total_reservation_count' => 0
        ]);
    }

    $earliestYear = Carbon::parse($earliestReservation->date_of_reservation)->year;
    $currentYear = Carbon::now()->year;

    $reservationsByYears = Reservation::select(
            DB::raw('YEAR(date_of_reservation) as year'),
            DB::raw('MONTH(date_of_reservation) as month'),
            DB::raw('COUNT(*) as reservation_count')
        )
        ->whereYear('date_of_reservation', '>=', $earliestYear)
        ->whereYear('date_of_reservation', '<=', $currentYear)
        ->groupBy(DB::raw('YEAR(date_of_reservation)'), DB::raw('MONTH(date_of_reservation)'))
        ->orderBy(DB::raw('YEAR(date_of_reservation)'), DB::raw('MONTH(date_of_reservation)'))
        ->get();

    $yearlyReservations = [];
    $totalReservations = 0;

    foreach ($reservationsByYears as $reservation) {
        $year = $reservation->year;
        $month = $reservation->month;
        $reservationCount = $reservation->reservation_count;

        if (!isset($yearlyReservations[$year])) {
            $yearlyReservations[$year] = [
                'year' => $year,
                'months' => [],
                'reservation_count' => 0
            ];
        }

        $yearlyReservations[$year]['months'][$month] = $reservationCount;
        $yearlyReservations[$year]['reservation_count'] += $reservationCount;
        $totalReservations += $reservationCount;
    }

    // Formatage des résultats
    $result = [];
    foreach ($yearlyReservations as $yearData) {
        $result[] = [
            'year' => $yearData['year'],
            'months' => $yearData['months'],
            'reservation_count' => $yearData['reservation_count']
        ];
    }

    return response()->json([
        'message' => $result,
        'total_reservation_count' => $totalReservations
    ]);
}




                              /**
     * @OA\Get(
     *     path="/api/reservation/getReservationsByHousingId/{housingId}",
     *     summary="Liste et nombres des réservations par logement",
     * description="Liste et nombres des réservations par logement",
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
     *         description="number of reservation"
     *
     *     )
     * )
     */
public function getReservationsByHousingId($housingId)
{
    // Récupérer le logement
    $housing = Housing::find($housingId);
    if(!$housing ){
        return response()->json([
            'message' =>'housing not found'
        ]);
    }

    // Récupérer les réservations associées au logement
    $reservations = Reservation::where('housing_id', $housingId)->get();

    // Compter le nombre de réservations associées
    $reservationCount = $reservations->count();

    // Retourner les réservations et le nombre total
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
     *         description="number of reservation"
     *
     *     )
     * )
     */
function getUserReservations(User $user)
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
     *     summary="Détail d'une réservation",
     * description="Détail d'une réservation",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="idReservation",
     *         in="path",
     *         required=true,
     *         description="Get user ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="number of reservation"
     *
     *     )
     * )
     */
function showDetailOfReservation($idReservation){
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
     *     path="/api/reservation/HousingAvailableAtDate/{date}",
     *     summary="Liste des logements disponible à une date donnée",
     * description="Liste des logements disponible à une date donnée",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="date",
     *         in="path",
     *         required=true,
     *         description="date ",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="number of reservation"
     *
     *     )
     * )
     */
    public function HousingAvailableAtDate($date)
    {
        $availableHousings = Housing::whereDoesntHave('reservation', function ($query) use ($date) {
            $query->where('date_of_starting', '<=', $date)
                  ->where('date_of_end', '>=', $date);
        })->with('reservation')->get();

        $result = [];

        foreach ($availableHousings as $housing) {
            $dateTime = new DateTime($date);
            $time = $housing->time_before_reservation;
            $dateTime->modify('+' . $time . ' days');

            $reservations = Reservation::where('housing_id', $housing->id)
                ->where('date_of_starting', '>', $date)
                ->where('date_of_starting', '<', $dateTime->format('Y-m-d'))
                ->where('date_of_end', '>', $date)
                ->where('date_of_end', '<', $dateTime->format('Y-m-d'))
                ->exists();
    
            if (!$reservations) {
                $result[] = $housing->id;
            }
        }
    
        return response()->json(['data' => $result]);
    }
    
    


     /**
     * @OA\Get(
     *     path="/api/reservation/HousingAvailableBetweenDates/{dateDebut}/{dateFin}'",
     *     summary="Liste des logements disponible à une intervalle de temps donnée",
     * description="Liste des logements disponible à une ntervalle de temps donnée",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="dateDebut",
     *         in="path",
     *         required=true,
     *         description="date ",
     *         @OA\Schema(type="string")
     *     ),
     * 
     *   @OA\Parameter(
     *         name="dateFin",
     *         in="path",
     *         required=true,
     *         description="date ",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="number of reservation"
     *
     *     )
     * )
     */
    public function HousingAvailableBetweenDates($dateDebut, $dateFin)
    {

        if (strtotime($dateDebut) > strtotime($dateFin)) {
            return response()->json([
                'data' => 'Entrer un interval valide',
            ]);
        }

        $availableHousings = Housing::whereDoesntHave('reservation', function ($query) use ($dateDebut, $dateFin) {

            $query->where(function ($q) use ($dateDebut, $dateFin) {
            $q->where('date_of_starting', '<=', $dateDebut)
                ->where('date_of_end', '>=', $dateDebut);
        })->orWhere(function ($q) use ($dateDebut, $dateFin) {
            $q->where('date_of_starting', '<=', $dateFin)
                ->where('date_of_end', '>=', $dateFin);
        })->orWhere(function ($q) use ($dateDebut, $dateFin) {
            $q->where('date_of_starting', '>=', $dateDebut)
                ->where('date_of_end', '<=', $dateFin);
            });
        })->with('reservation')
        ->get();

        $result = [];

        foreach ($availableHousings as $housing) {
            $dateTime = new DateTime($dateFin);
            $time = $housing->time_before_reservation;
            $dateTime->modify('+' . $time . ' days');


            $reservation = Reservation::where('housing_id', $housing->id)->first();

            if(
                !(($reservation->date_of_starting < $dateTime &&
                $reservation->date_of_end > $dateTime) ||
                ($reservation->date_of_starting < $dateDebut
               && $reservation->date_of_end > $dateDebut)  ||
               ($reservation->date_of_starting <= $dateDebut
               && $reservation->date_of_end >= $dateTime)
               )
             ){
                $result[] = $housing->id;
            }
        }

        return response()->json(['data' => $result]);
    }



           /**
     * @OA\Get(
     *     path="/api/reservation/topTravelersWithMostReservations",
     *     summary="Top 10 des utilisateurs(voyageurs) avec le plus grand nombre de réservations",
     * description="Top 10 des utilisateurs(voyageurs) avec le plus grand nombre de réservations",
     *     tags={"Reservation"},
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
     *     path="/api/reservation/showDetailOfReservationForHote/{idReservation}",
     *     summary="Détail d'une réservation côté hote",
     * description="Détail d'une réservation côté hote",
     *     tags={"Reservation"},
     * security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="idReservation",
     *         in="path",
     *         required=true,
     *         description="Get user ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détail d'une réservation côté hote"
     *     )
     * )
     */
public function showDetailOfReservationForHote($idReservation){
    $reservation = Reservation::find($idReservation);
    if(!$reservation){
        return response()->json([
            'message' => 'Reservation not found'
        ], 404);
    }

    if (!(Auth::user()->id == $reservation->housing->user_id)) {
        return response()->json([
            'message' => 'Vous ne pouvez pas consulter les détails d une réservation qui ne vous concerne pas'
        ], 403);
    }

    return response()->json([
        'data' =>[
            'detail de la reservation' => $reservation->toArray(),
            'logement' => $reservation->housing->toArray(),
            'voyageur' => $reservation->user->toArray()
        ]
    ]);
}



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
     *     path="/api/reservation/getAllReservationCanceledByTravelerForAdmin",
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
}




