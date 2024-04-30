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
use App\Models\Reservation;
use App\Models\Payement;
use App\Models\Portfeuille;
use App\Models\Portfeuille_transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmailwithoutfile;
use DateTime;
use App\Mail\NotificationEmail;


class ReservationController extends Controller
{

    private function canCreateReservation($housing_id, $new_start_date, $new_end_date, $number_of_domestical_animal, $valeur_paye, $montant_total, $id_transaction, $is_tranche_paiement,$payementmethode) {
        $housing = Housing::where('id', $housing_id)
            ->where('is_deleted', 0)
            ->where('is_blocked', 0)
            ->where('is_updated', 0)
            ->where('is_actif', 1)
            ->where('is_destroy', 0)
            ->first();
    
        if (!$housing) {
            return ['is_allowed' => false, 'message' => 'Logement non trouvé ou a été supprimé ou désactivé par l\'hôte'];
        }
    
        if (!$housing->is_accepted_animal && $number_of_domestical_animal > 0) {
            return ['is_allowed' => false, 'message' => 'Le logement n\'accepte pas les animaux domestiques'];
        }
        $new_start = Carbon::parse($new_start_date);
        $new_end = Carbon::parse($new_end_date);
        if ($new_start >= $new_end) {
            return ['is_allowed' => false, 'message' => 'La date de fin doit être postérieure à la date de début'];
        }
    
    
        $stay_duration = $new_end->diffInDays($new_start);
    
        if ($stay_duration < $housing->minimum_duration) {
            return ['is_allowed' => false, 'message' => "La durée minimale de séjour est de {$housing->minimum_duration} jours"];
        }
    
        if ($valeur_paye > $montant_total) {

            return ['is_allowed' => false, 'message' => 'La valeur payee doit pas etre superieur au montant total'];
        }
        if (Payement::where('id_transaction', $id_transaction)->exists()) {
            return ['is_allowed' => false, 'message' => 'L\'ID de transaction doit être unique. Cette transaction existe déjà.'];

        }
        if ($payementmethode == "portfeuille") {
           

            $user_id = Auth::id();
            $portefeuille = Portfeuille::where('user_id', $user_id)->first();

            if (!$portefeuille) {
                return ['is_allowed' => false, 'message' => 'Portefeuille introuvable'];
            }
    
            if ($portefeuille->solde < $valeur_paye) {
                return ['is_allowed' => false, 'message' => 'Solde insuffisant dans le portefeuille pour pouvoir réserver'];
            }
        }
        
        if ($is_tranche_paiement == 1) {
            $required_paid_value = $montant_total / 2;
            
            if ($valeur_paye < $required_paid_value) {
                return ['is_allowed' => false, 'message' => "Pour le paiement par tranche, la valeur payée doit être au moins la moitié du montant total"];
            }
        } else {
            if ($valeur_paye < $montant_total) {
                return ['is_allowed' => false, 'message' => "Pour le paiement complet, la valeur payée doit être égale au montant total"];
            }
        }
        $existing_reservations = Reservation::where('housing_id', $housing_id)->get();
    
        foreach ($existing_reservations as $reservation) {
            $time_before_reservation = $housing->time_before_reservation;
    
            $existing_end = Carbon::parse($reservation->date_of_end);
    
            $minimum_start_date = $existing_end->copy()->addDays($time_before_reservation);
            $existing_start = Carbon::parse($reservation->date_of_starting);
            if ($new_start <= $existing_end && $new_end >= $existing_start) {
                return ['is_allowed' => false, 'message' => 'La nouvelle réservation chevauche une réservation existante'];
            }
            if ($new_start < $minimum_start_date && $new_end >= $minimum_start_date) {
                return ['is_allowed' => false, 'message' => 'La nouvelle réservation commence trop tôt par rapport au délai requis'];
            }    
            
            
        }
    
        return ['is_allowed' => true, 'message' => 'Réservation autorisée'];
    }
    
/**
 * @OA\Post(
 *     path="/api/reservation/store",
 *     summary="Créer une réservation avec paiement",
 *     description="Crée une réservation pour un logement avec un paiement associé. Accepte un fichier image pour la photo des voyageurs voulant réserver la réservation.",
 *     tags={"Reservation"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"housing_id", "date_of_starting", "date_of_end", "number_of_adult", "code_pays", "telephone_traveler", "photo", "heure_arrivee_max", "heure_arrivee_min", "is_tranche_paiement", "montant_total", "valeur_payee", "payment_method", "id_transaction", "statut_paiement"},
 *                 @OA\Property(
 *                     property="housing_id",
 *                     type="integer",
 *                     example=1,
 *                     description="ID du logement"
 *                 ),
 *                 @OA\Property(
 *                     property="date_of_starting",
 *                     type="string",
 *                     format="date",
 *                     example="2024-05-01",
 *                     description="Date de début de la réservation"
 *                 ),
 *                 @OA\Property(
 *                     property="date_of_end",
 *                     type="string",
 *                     format="date",
 *                     example="2024-05-07",
 *                     description="Date de fin de la réservation"
 *                 ),
 *                 @OA\Property(
 *                     property="number_of_adult",
 *                     type="integer",
 *                     example=2,
 *                     description="Nombre d'adultes"
 *                 ),
 *                 @OA\Property(
 *                     property="number_of_child",
 *                     type="integer",
 *                     example=1,
 *                     description="Nombre d'enfants"
 *                 ),
 *                 @OA\Property(
 *                     property="number_of_domestical_animal",
 *                     type="integer",
 *                     example=0,
 *                     description="Nombre d'animaux domestiques"
 *                 ),
 *                 @OA\Property(
 *                     property="number_of_baby",
 *                     type="integer",
 *                     example=2,
 *                     description="Nombre de bébés"
 *                 ),
 *                 @OA\Property(
 *                     property="message_to_hote",
 *                     type="string",
 *                     example="Nous avons hâte de visiter votre logement!",
 *                     description="Message à l'hôte"
 *                 ),
 *                 @OA\Property(
 *                     property="code_pays",
 *                     type="string",
 *                     example="FRA",
 *                     description="Code du pays"
 *                 ),
 *                 @OA\Property(
 *                     property="telephone_traveler",
 *                     type="integer",
 *                     example=123456789,
 *                     description="Numéro de téléphone du voyageur"
 *                 ),
 *                 @OA\Property(
 *                     property="photo",
 *                     type="string",
 *                     format="binary",
 *                     description="Photo de la réservation"
 *                 ),
 *                 @OA\Property(
 *                     property="heure_arrivee_max",
 *                     type="string",
 *                     format="date-time",
 *                     example="18:00",
 *                     description="Heure d'arrivée maximale"
 *                 ),
 *                 @OA\Property(
 *                     property="heure_arrivee_min",
 *                     type="string",
 *                     format="date-time",
 *                     example="14:00",
 *                     description="Heure d'arrivée minimale"
 *                 ),
 *                 @OA\Property(
 *                     property="is_tranche_paiement",
 *                      type="integer",
 *                     example=1,
 *                     description="Paiement en plusieurs tranches"
 *                 ),
 *                 @OA\Property(
 *                     property="montant_total",
 *                     type="number",
 *                     format="float",
 *                     example=500,
 *                     description="Montant total"
 *                 ),
 *                 @OA\Property(
 *                     property="valeur_payee",
 *                     type="number",
 *                     format="float",
 *                     example=250,
 *                     description="Montant déjà payé"
 *                 ),
 *                 @OA\Property(
 *                     property="payment_method",
 *                     type="string",
 *                     example="Credit Card",
 *                     description="Méthode de paiement"
 *                 ),
 *                 @OA\Property(
 *                     property="id_transaction",
 *                     type="string",
 *                     example="TX123456789",
 *                     description="Identifiant de la transaction"
 *                 ),
 *                 @OA\Property(
 *                     property="statut_paiement",
 *                      type="integer",
 *                     example=1,
 *                     description="Statut du paiement"
 *                 ),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Réservation et paiement créés avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Réservation et paiement créés avec succès"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=409,
 *         description="Conflit de réservation ou problème de paiement",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Les dates de la réservation chevauchent celles d'une réservation existante"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Logement non trouvé ou indisponible",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Logement non trouvé ou indisponible"),
 *         )
 *     )
 * )
*/

    public function storeReservationWithPayment(Request $request)
    {
        $validatedData = $request->validate([
            'housing_id' => 'required',
            'date_of_starting' => 'required|date',
            'date_of_end' => 'required|date',
            'number_of_adult' => 'required|integer',
            'number_of_child' => 'required|integer',
            'number_of_domestical_animal' => 'required|integer',
            'number_of_baby' => 'required|integer',
            'message_to_hote' => 'nullable|string',
            'code_pays' => 'required|string',
            'telephone_traveler' => 'required|integer',
            'photo' => 'required|file|mimes:jpg,jpeg,png',
            'heure_arrivee_max' => 'required|date_format:H:i',
            'heure_arrivee_min' => 'required|date_format:H:i',
            'is_tranche_paiement' => 'required',
            'montant_total' => 'required|numeric',
            'valeur_payee' => 'required|numeric',
            'payment_method' => 'required|string',
            'id_transaction' => 'required|string',
            'statut_paiement' =>'required',
            'photo' => 'file|mimes:jpg,jpeg,png|max:2048',
        ]);
        $user_id=Auth::id();
    
    $validation_result =$this->canCreateReservation($validatedData['housing_id'], $validatedData['date_of_starting'],$validatedData['date_of_end'], $validatedData['number_of_domestical_animal'],$validatedData['valeur_payee'],$validatedData['montant_total'],$validatedData['id_transaction'],$validatedData['is_tranche_paiement'],$validatedData['payment_method']);
    if (!$validation_result['is_allowed']) {
        return response()->json(['message' => $validation_result['message']], 409);
    }    
    

        $reservation = Reservation::create([
            'housing_id' => $validatedData['housing_id'],
            'date_of_reservation' => now(),
            'date_of_starting' => $validatedData['date_of_starting'],
            'date_of_end' => $validatedData['date_of_end'],
            'number_of_adult' => $validatedData['number_of_adult'],
            'number_of_child' => $validatedData['number_of_child'],
            'number_of_domestical_animal' => $validatedData['number_of_domestical_animal'],
            'number_of_baby' => $validatedData['number_of_baby'],
            'message_to_hote' => $validatedData['message_to_hote'] ?? null,
            'code_pays' => $validatedData['code_pays'],
            'telephone_traveler' => $validatedData['telephone_traveler'],
            'heure_arrivee_max' => $validatedData['heure_arrivee_max'],
            'heure_arrivee_min' => $validatedData['heure_arrivee_min'],
            'is_tranche_paiement' => $validatedData['is_tranche_paiement'],
            'montant_total' => $validatedData['montant_total'],
            'valeur_payee' => $validatedData['valeur_payee'],
            'is_confirmed_hote' => false,
            'is_integration' => false,
            'is_rejected_traveler' => false,
            'is_rejected_hote' => false,
            'user_id'=> $user_id,
            'photo'=> 'defaut',
        ]);
    
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = uniqid() . '.' . $photo->getClientOriginalExtension();
            $photoPath = $photo->move(public_path('image/photo_reservation'), $photoName);
            $photoUrl = url('/image/photo_reservation/' . $photoName);
    
            $reservation->photo = $photoUrl;
            $reservation->save();
        }
    
        $paymentData = [
            'reservation_id' => $reservation->id,
            'amount' => $validatedData['valeur_payee'],
            'payment_method' => $validatedData['payment_method'],
            'id_transaction' => $validatedData['id_transaction'],
            'statut' => $validatedData['statut_paiement'],
            'is_confirmed' => false,
            'is_canceled' => false,
        ];
    
        if ($request->has('country')) {
            $paymentData['country'] = $request->input('country');
        }
    
        $payment = Payement::create($paymentData);
        if ($validatedData['payment_method'] == "portfeuille") {

        $portefeuille = Portfeuille::where('user_id', $user_id)->first();
        $portefeuille->solde -= $validatedData['valeur_payee'];

        $portefeuilleTransaction = new Portfeuille_transaction();
        $portefeuilleTransaction->debit = true;
        $portefeuilleTransaction->credit = false;
        $portefeuilleTransaction->amount = $validatedData['valeur_payee'];
        $portefeuilleTransaction->motif = "Réservation effectuée avec portefeuille";
        $portefeuilleTransaction->reservation_id = $reservation->id;
        $portefeuilleTransaction->payment_method = $validatedData['payment_method'];
        $portefeuilleTransaction->id_transaction = $validatedData['id_transaction'];
        $portefeuilleTransaction->portfeuille_id = $portefeuille->id;
        $portefeuilleTransaction->save();
        $portefeuille->save();
        }

        $notificationName="Félicitation!Vous venez de faire une reservation.D'ici 24h,elle sera confirmée ou rejeté par l'hôte";
        $housing = Housing::where('id', $validatedData['housing_id'])
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->first();
        $notification = new Notification([
           'name' => $notificationName,
           'user_id' => $user_id,
          ]);
          $notification->save();

          $mail_to_traveler = [
            'title' => 'Confirmation de Réservation',
            'body' => "Félicitations ! Vous avez réservé un logement. D'ici 24 heures, l'hôte confirmera ou rejettera la réservation. Dates de réservation : du " . $reservation->date_of_starting . " au " . $reservation->date_of_end . "."
           ];
        

          $notificationName = "Bonne nouvelle! Votre logement a été reservé par un utilisateur.Accéder à la section de reservation pour confirmer la reservation";
             $notification = new Notification([
           'name' => $notificationName,
           'user_id' => $housing->user_id,
             ]);
          $notification->save();

          $mail_to_host = [
        'title' => 'Nouvelle Réservation',
        'body' => "Bonne nouvelle ! Votre logement a été réservé par un utilisateur. Détails de la réservation :\n" .
        " Date de début : " . $reservation->date_of_starting . "\n" .
        " Date de fin : " . $reservation->date_of_end . "\n" 
             ];
        
        

        Mail::to(auth()->user()->email)->send(new NotificationEmailwithoutfile($mail_to_traveler));
        Mail::to($housing->user->email)->send(new NotificationEmailwithoutfile($mail_to_host));

        return response()->json([
            'message' => 'Réservation et paiement créés avec succès',
            'reservation' => $reservation,
            'payment' => $payment,
        ], 201);
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
              $notification->name = 'Une reservation vient d\être rejetée par l hote pour le motif suivant << '.$request->motif_rejet_hote. ">>  et le logement appartient à ".$reservation->housing->user->firstname." ".$reservation->housing->user->lastname." il a pour identifiant ".$reservation->housing->user->id;
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

     // Notification

     public function notifyAnnulation(Request $request,$reservation_id,$mailtraveler,$mailhote,$montant_commission){
        $reservation = Reservation::find($reservation_id);
        $portefeuilleClient = Portfeuille::find($reservation->user->portfeuille->id);
        $notification = new Notification();
        $notification->user_id = $reservation->user_id;
        $notification->name = $mailtraveler['body'];
        $notification->save();
        
       Mail::to($reservation->user->email)->send(new NotificationEmailwithoutfile($mailtraveler) );

        $portefeuilleHote = Portfeuille::find($reservation->housing->user->portfeuille->id);
        $notification = new Notification();
        $notification->user_id = $reservation->user_id;
        $notification->name = $mailhote['body'];
        $notification->save();
        
      Mail::to($reservation->housing->user->email)->send(new NotificationEmailwithoutfile($mailhote) );

        $adminUsers = User::where('is_admin', 1)->get();
        foreach ($adminUsers as $adminUser) {
            $notification = new Notification();
            $notification->user_id = $adminUser->id;
            $notification->name = 'Une reservation vient d\être annulé  par un client ayant pour id '.$reservation->user->id.'  pour le motif suivant << '.$request->motif_rejet_traveler. ">>  et le logement appartient à ".$reservation->housing->user->firstname.". ".$reservation->housing->user->lastname." il a pour identifiant ".$reservation->housing->user->id;
            $notification->save();

            $mail = [
                "title" => "Annulation d'une réservation par un voyageur",
                "body" => "Une réservation vient d'être annulée par un client avec l'identifiant {$reservation->user->id}. Le motif de l'annulation est le suivant : « $request->motif_rejet_traveler ». Le logement appartient à {$reservation->housing->user->firstname} {$reservation->housing->user->lastname}, avec l'identifiant {$reservation->housing->user->id}. Vous recevez une commission de {$montant_commission} FCFA sur cette opération"
            ];
            
      Mail::to($adminUser->email)->send(new NotificationEmailwithoutfile($mail) );
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
                'message' => 'Vous ne pouvez pas confirmer une reservation déjà rejetée par l hote'
            ]);
        }

        if ($reservation->is_rejected_traveler) {
            return response()->json([
                'message' => 'Vous ne pouvez pas rejeter une reservation déjà rejetée par vous même'
            ]);
        }

        $dateIntegration = $reservation->date_of_starting;

        $dateReservation = $reservation->date_of_reservation;

        $dateIntegration = new DateTime($reservation->date_of_starting);

        $dateReservation = new DateTime($reservation->date_of_reservation);

        $currentDate =  DateTime::createFromFormat('Y-m-d', date('Y-m-d'));

        $diff = $dateIntegration->diff($currentDate);
        $totalDays = $diff->days; 
        if ($diff->h > 0) {
            $totalDays += ($diff->h / 24); 
        }

        if ($diff->i > 0) {
            $totalDays += ($diff->i / (24 * 60)); 
        }

        $diffEnHeure = $totalDays * 24; 
             
        $dateIntegration = DateTime::createFromFormat('Y-m-d', $reservation->date_of_starting);

        $dateIntegration->modify('-' . $diff->days . ' days');


        Reservation::whereId($idReservation)->update([
            'is_rejected_traveler'=>0,
            'motif_rejet_traveler'=>$request->motif_rejet_traveler
          ]);


            $soldeTotal = Portfeuille_transaction::sum('amount');
            $soldeCommission = Portfeuille_transaction::sum('montant_commission');
            $soldeRestant = Portfeuille_transaction::sum('montant_restant');


        if ($diffEnHeure >= $reservation->housing->delai_integral_remboursement) {

            $delai = DateTime::createFromFormat('Y-m-d', $reservation->date_of_starting)->modify('-'.(intval($reservation->housing->delai_integral_remboursement/24 )).' days')->format('Y-m-d');

           $montantClient =  ($reservation->valeur_payee * $reservation->housing->valeur_integral_remboursement)/100;
           $montantWithoutClient = $reservation->valeur_payee - $montantClient;
           $montant_commission = ($montantWithoutClient * $reservation->housing->user->commission->valeur)/100;
           $montantHote = $montantWithoutClient - $montant_commission;

              $portefeuilleClient = Portfeuille::find($reservation->user->portfeuille->id);
              $portefeuilleClient->update(['solde' => $portefeuilleClient->solde + $montantClient]);
              $transaction = new Portfeuille_transaction();
              $transaction->portfeuille_id = $portefeuilleClient->id;
              $transaction->amount = $montantClient;
              $transaction->debit = 0;
              $transaction->credit =1;
              $transaction->reservation_id = $reservation->id;
              $transaction->payment_method = "portfeuille";
              $transaction->motif = "Remboursement suite à l\' annulation de la réservation par le client";
              $transaction->valeur_commission = 0;
              $transaction->montant_commission = 0;
              $transaction->montant_restant = 0;
              $transaction->solde_total = $soldeTotal  + $montantClient;
              $transaction->solde_commission = $soldeCommission  + 0;
              $transaction->solde_restant = $soldeRestant  + 0;
              $transaction->save();

              $soldeTotal =  $soldeTotal  + $montantClient;

              $portefeuilleHote = Portfeuille::find($reservation->housing->user->portfeuille->id);
              $portefeuilleHote->update(['solde' => $portefeuilleHote->solde + $montantHote]);
              $transaction = new Portfeuille_transaction();
              $transaction->portfeuille_id = $portefeuilleHote->id;
              $transaction->amount = $montantWithoutClient;
              $transaction->debit = 0;
              $transaction->credit =1;
              $transaction->reservation_id = $reservation->id;
              $transaction->payment_method = "portfeuille";
              $transaction->motif = "Remboursement suite à l\' annulation de la réservation par le client";
              $transaction->valeur_commission = $reservation->housing->user->commission->valeur;
              $transaction->montant_commission = $montant_commission;
              $transaction->montant_restant = $montantHote;
              $transaction->solde_total = $soldeTotal  + $montantWithoutClient;
              $transaction->solde_commission = $soldeCommission  + $montant_commission;
              $transaction->solde_restant = $soldeRestant + $montantHote;
              $transaction->save();

              $mailtraveler = [
                'title' => 'Confirmation d\'annulation',
                'body' => "Votre annulation a été prise en compte. Vous bénéficiez d'un remboursement intégral et votre portefeuille a été crédité de $montantClient FCFA. Solde actuel : $portefeuilleClient->solde FCFA."
               ];
            
               $mailhote = [
                'title' => "Annulation d'un logement",
                'body' => "La réservation d'un de vos biens a été annulée. L'annulation a entraîné un remboursement intégral, et votre portefeuille a été crédité de {$montantHote} FCFA. Solde actuel : $portefeuilleHote->solde FCFA."
               ];
            
              $this->notifyAnnulation($request, $reservation->id,$mailtraveler,$mailhote,$montant_commission);

              return response()->json([
                'message' => 'Reservation canceled successfully',
                'durée en jours' => $totalDays,
                'durée  en heure' =>$diffEnHeure,
                'delai d annulation pour ne pas obtenir un  remboursement intégral  ' => $delai,
                'montant' => $reservation->valeur_payee,
                'montantHote' => $montantHote,
                'fraisLouerMeublee' =>$montant_commission,
                'montant retourné au client' => $montantClient,
              
            ]);

        }else if( $diffEnHeure >= $reservation->housing->delai_partiel_remboursement ){


            $delai = DateTime::createFromFormat('Y-m-d', $reservation->date_of_starting)->modify('-'.(intval($reservation->housing->delai_partiel_remboursement /24)).' days')->format('Y-m-d');


           $montantClient =  ($reservation->valeur_payee * $reservation->housing->valeur_partiel_remboursement)/100;
           $montantWithoutClient = $reservation->valeur_payee - $montantClient;
           $montant_commission = ($montantWithoutClient * $reservation->housing->user->commission->valeur)/100;
           $montantHote = $montantWithoutClient - $montant_commission;         

           $portefeuilleClient = Portfeuille::find($reservation->user->portfeuille->id);
           $client_solde = $montantClient+$portefeuilleClient->solde;
           $portefeuilleClient->update(['solde' => $client_solde]);
           $transaction = new Portfeuille_transaction();
           $transaction->portfeuille_id = $portefeuilleClient->id;
           $transaction->amount = $montantClient;
           $transaction->debit = 0;
           $transaction->credit =1;
           $transaction->reservation_id = $reservation->id;
           $transaction->payment_method = "portfeuille";
           $transaction->motif = "Remboursement suite à l\' annulation de la réservation par le client";
           $transaction->valeur_commission = 0;
           $transaction->montant_commission = 0;
           $transaction->montant_restant = 0;
           $transaction->solde_total = $soldeTotal  + $montantClient;
           $transaction->solde_commission = $soldeCommission  + 0;
           $transaction->solde_restant = $soldeRestant + 0;
           $transaction->save();

           $soldeTotal =  $soldeTotal  + $montantClient;

           $portefeuilleHote = Portfeuille::find($reservation->housing->user->portfeuille->id);
           $portefeuilleHote->update(['solde' => $portefeuilleHote->solde + $montantHote]);

           $transaction = new Portfeuille_transaction();
           $transaction->portfeuille_id = $portefeuilleHote->id;
           $transaction->amount = $montantWithoutClient;
           $transaction->debit = 0;
           $transaction->credit =1;
           $transaction->reservation_id = $reservation->id;
           $transaction->payment_method = "portfeuille";
           $transaction->motif = "Remboursement suite à l\' annulation de la réservation par le client";
           $transaction->valeur_commission = $reservation->housing->user->commission->valeur;
           $transaction->montant_commission = $montant_commission;
           $transaction->montant_restant = $montantHote;
           $transaction->solde_total = $soldeTotal +  $montantWithoutClient ;
           $transaction->solde_commission = $soldeCommission + $montant_commission;
           $transaction->solde_restant = $soldeRestant + $montantHote;
           $transaction->save();
           $mailtraveler = [
            'title' => 'Confirmation d\'annulation',
            'body' => "Votre annulation a été prise en compte. Vous bénéficiez d'un remboursement partiel et votre portefeuille a été crédité de $montantClient FCFA. Solde actuel : $portefeuilleClient->solde FCFA."
           ];
        
            $mailhote = [
            'title' => "Annulation d'un logement",
            'body' => "La réservation d'un de vos biens a été annulée. L'annulation a entraîné un remboursement partiel, et votre portefeuille a été crédité de {$montantHote} FCFA. Solde actuel : $portefeuilleHote->solde FCFA."
            ];

           $this->notifyAnnulation($request, $reservation->id,$mailtraveler,$mailhote,$montant_commission);

           return response()->json([
            'message' => 'Reservation canceled successfully',
            'durée en jours' => $totalDays,
            'durée  en heure' =>$diffEnHeure,
            'delai d annulation pour ne pas obtenir un  remboursement partiel  ' =>  $delai,
            'montant' => $reservation->valeur_payee,
            'montantHote' => $montantHote,
            'fraisLouerMeublee' =>$montant_commission,
            'montant retourné au client' => $montantClient,
         
        ]);
         }else{

            $montantClient =  0;
            $montantWithoutClient = $reservation->valeur_payee - $montantClient;
            $montant_commission = ($montantWithoutClient * $reservation->housing->user->commission->valeur)/100;
            $montantHote = $montantWithoutClient - $montant_commission;

            $portefeuilleClient = Portfeuille::find($reservation->user->portfeuille->id);
            $portefeuilleClient->update(['solde' => $portefeuilleClient->solde + $montantClient]);
            $transaction = new Portfeuille_transaction();
            $transaction->portfeuille_id = $portefeuilleClient->id;
            $transaction->amount = $montantClient;
            $transaction->debit = 0;
            $transaction->credit =1;
            $transaction->reservation_id = $reservation->id;
            $transaction->payment_method = "portfeuille";
            $transaction->motif = "Remboursement suite à l\' annulation de la réservation par le client";
            $transaction->valeur_commission = 0;
            $transaction->montant_commission = 0;
            $transaction->montant_restant = 0;
            $transaction->solde_total = $soldeTotal  + $montantClient;
            $transaction->solde_commission = $soldeCommission  + 0;
            $transaction->solde_restant = $soldeRestant + 0;
            $transaction->save();

            $soldeTotal =  $soldeTotal  + $montantClient;

            $portefeuilleHote = Portfeuille::find($reservation->housing->user->portfeuille->id);
            $portefeuilleHote->update(['solde' => $portefeuilleHote->solde + $montantHote]);
            $transaction = new Portfeuille_transaction();
            $transaction->portfeuille_id = $portefeuilleHote->id;
            $transaction->amount = $montantWithoutClient;
            $transaction->debit = 0;
            $transaction->credit =1;
            $transaction->reservation_id = $reservation->id;
            $transaction->payment_method = "portfeuille";
            $transaction->motif = "Remboursement suite à l\' annulation de la réservation par le client";
            $transaction->valeur_commission = $reservation->housing->user->commission->valeur;
            $transaction->montant_commission = $montant_commission;
            $transaction->montant_restant = $montantHote;
            $transaction->solde_total = $soldeTotal  + $montantWithoutClient;
            $transaction->solde_commission = $soldeCommission  + $montant_commission;
            $transaction->solde_restant = $soldeRestant + $montantHote;
            $transaction->save();
            $mailtraveler = [
                'title' => 'Confirmation d\'annulation',
                'body' => "Votre annulation a été prise en compte. Cependant, il n'y a pas de remboursement, donc votre portefeuille n'a pas été crédité. Solde actuel : $portefeuilleClient->solde FCFA."
            ];
            
            $mailhote = [
                'title' => "Annulation d'un logement",
                'body' => "La réservation d'un de vos biens a été annulée. Cependant, cette annulation n'entraîne aucun remboursement, donc votre portefeuille n'a pas été crédité. Solde actuel : $portefeuilleHote->solde FCFA."
            ];
            

          $this->notifyAnnulation($request, $reservation->id,$mailtraveler,$mailhote,$montant_commission);
            

          return response()->json([
            'message' => 'Reservation canceled successfully',
            'durée en jours' => $totalDays,
            'durée  en heure' =>$diffEnHeure,
            'montant' => $reservation->valeur_payee,
            'montantHote' => $montantHote,
            'fraisLouerMeublee' =>$montant_commission,
            'montant retourné au client' => $montantClient,
         
        ]);
         }
     
  } catch(Exception $e) {
              return response()->json([
                  'error' => 'An error occurred',
                  'message' => $e->getMessage()
              ], 500);
          }
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
        $housing = Housing::find($housingId);
        if(!$housing ){
            return response()->json([
                'message' =>'housing not found'
            ]);
        }
    
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
                 'voyageur' => $reservation->user->toArray()
             ]
         ]);
     }

     /**
 * @OA\Post(
 *     path="/api/reservation/confirmIntegration",
 *     summary="Confirmer l'intégration après une réservation(c'est le voyageur qui confirme)",
 *   security={{"bearerAuth": {}}},
 *     description="Confirme l'intégration d'une réservation après vérification des conditions nécessaires.",
 *     tags={"Reservation"},
 *     @OA\RequestBody(
 *         description="Informations pour confirmer l'intégration",
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="reservation_id", type="integer", description="L'ID de la réservation à intégrer"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Intégration confirmée et montant crédité",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Intégration confirmée et montant crédité"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Problème avec la confirmation d'intégration",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="La réservation doit être confirmée par l'hôte"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Réservation ou autre élément non trouvé",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Réservation non trouvée"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Erreur interne du serveur"),
 *         ),
 *     ),
 * )
 */
public function confirmIntegration(Request $request)
{
    $reservation = Reservation::find($request->input('reservation_id'));

    if (!$reservation) {
        return response()->json(['message' => 'Réservation non trouvée'], 404);
    }
    if (!$reservation->is_confirmed_hote) {
        return response()->json(['message' => 'La réservation doit être confirmée par l\'hôte'], 400);
    }

    if ($reservation->is_rejected_traveler || $reservation->is_rejected_hote) {
        return response()->json(['message' => 'La réservation a été rejetée, Donc vous ne pouvez pas comnfirmer l\'intégration'], 400);
    }
    if ($reservation->montant_total > $reservation->valeur_payee) {
        return response()->json(['message' => 'Veuillez solder la deuxième tranche avant de confirmer l\'intégration'], 400);
    }

    if ($reservation->is_integration) {
        return response()->json(['message' => 'L\'intégration a déjà été confirmée'], 400);
    }

    $housing = Housing::find($reservation->housing_id);

    if (!$housing) {
        return response()->json(['message' => 'Logement non trouvé'], 404);
    }

    $owner = User::find($housing->user_id);

    if (!$owner) {
        return response()->json(['message' => 'Propriétaire non trouvé'], 404);
    }

    $commission = Commission::where('user_id', $owner->id)->first();

    if (!$commission) {
        return response()->json(['message' => 'Commission non trouvée pour ce proprietaire'], 404);
    }

    $commission_percentage = $commission->valeur; 
    $total_amount = $reservation->valeur_payee;

    $commission_amount = $total_amount * ($commission_percentage / 100);
    $remaining_amount = $total_amount - $commission_amount;

    $reservation->is_integration = true;
    $reservation->save();
    $previous_transactions = Portfeuille_transaction::all();

    $solde_total = $previous_transactions->sum('amount');
    $solde_commission = $previous_transactions->sum('montant_commission');
    $solde_restant = $previous_transactions->sum('montant_restant');

    $new_solde_total = $solde_total + $total_amount;
    $new_solde_commission = $solde_commission + $commission_amount;
    $new_solde_restant = $solde_restant + $remaining_amount;

    $portefeuilleTransaction = new Portfeuille_transaction();
    $portefeuilleTransaction->debit = false;
    $portefeuilleTransaction->credit = true;
    $portefeuilleTransaction->amount = $total_amount;
    $portefeuilleTransaction->valeur_commission = $commission_percentage;
    $portefeuilleTransaction->montant_commission =$commission_amount ;
    $portefeuilleTransaction->montant_restant=$remaining_amount;
    $portefeuilleTransaction->solde_total = $new_solde_total;
    $portefeuilleTransaction->solde_commission = $new_solde_commission;
    $portefeuilleTransaction->solde_restant = $new_solde_restant;
    $portefeuilleTransaction->motif = "Virement sur le compte de l'hôte après confirmation de l'intégration ";
    $portefeuilleTransaction->reservation_id = $reservation->id;
    $portefeuilleTransaction->portfeuille_id = $owner->portfeuille->id;
    $portefeuilleTransaction->id_transaction = "0";
    $portefeuilleTransaction->payment_method = "portfeuille";
    $portefeuilleTransaction->save();

    $portefeuille = Portfeuille::where('user_id', $owner->id)->first();

    if (!$portefeuille) {
        return response()->json(['message' => 'Portefeuille du propriétaire non trouvé'], 404);
    }

    $portefeuille->solde += $remaining_amount;
    $portefeuille->save();

    return response()->json(['message' => 'Intégration confirmée et montant crédité'], 200);
}

}



