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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\user_partenaire;
use App\Jobs\SendRegistrationEmail;
use App\Models\MethodPayement;
use App\Services\FileService;

class ReservationController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService=null)
    {
        $this->fileService = $fileService;
    }


    private function canCreateReservation($housing_id, $new_start_date, $new_end_date, $number_of_domestical_animal) {
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

        if ($new_start->lt(Carbon::today())) {
            return ['is_allowed' => false, 'message' => 'La date de départ de la réservation doit être supérieure ou égale à la date d\'aujourd\'hui.'];
        }
        if ($new_start >= $new_end) {
            return ['is_allowed' => false, 'message' => 'La date de fin doit être postérieure à la date de début'];
        }


        $stay_duration = $new_end->diffInDays($new_start);

        if ($stay_duration < $housing->minimum_duration) {
            return ['is_allowed' => false, 'message' => "La durée minimale de séjour est de {$housing->minimum_duration} jours"];
        }


        $existing_traveler_reservation = Reservation::where('housing_id', $housing_id)->where('is_rejected_traveler', false)->where('is_rejected_hote', false)->where('date_of_starting',$new_start_date)->where('date_of_end',$new_end_date)->where('statut','payee')->exists();

        if($existing_traveler_reservation){
            return ['is_allowed' => false, 'message' => 'Vous avez déjà ajouté une demande de réservation sur ce logement avec la même période de départ et de fin'];
        }

        $existing_reservations = Reservation::where('housing_id', $housing_id)->where('is_rejected_traveler', false)->where('is_rejected_hote', false)->where('is_confirmed_hote',true)->where('statut','payee')->get();

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
   public function calculateTotalPrice($housingId, $startDate, $duration,$is_tranch)
{
    // Étape 1: Récupérer le logement
    $housing = Housing::find($housingId);

    if (!$housing) {
        return [
            'error' => true,
            'message' => 'Logement non trouvé'
        ];
    }

    // Étape 2: Calculer le montant du logement (prix par nuit * durée)
    $montantHousing = $housing->price * $duration;

    // Étape 3: Calculer les charges à ajouter
    $montantCharge = Housing_charge::where('housing_id', $housingId)
        ->where('is_mycharge', false) // Seuls les frais à la charge du voyageur
        ->sum('valeur');

    // Calculer le montant total (montant_housing + montant_charge)
    $montantTotal = $montantHousing + $montantCharge;

    // Étape 4: Appliquer les réductions en fonction du nombre de nuits
    $reductionValue = 0;
    $reduction = Reduction::where('housing_id', $housingId)
        ->where('night_number', '<=', $duration)
        ->orderBy('night_number', 'desc')
        ->where('is_encours',true)
        ->where('is_actif',true)
        ->first();

    if ($reduction) {
        $reductionValue =$montantHousing * ($reduction->value / 100);
    }

    // Étape 5: Appliquer la promotion si elle est active durant la période de réservation
    $promotionValue = 0;
    $promotion = Promotion::where('housing_id', $housingId)
        ->where('is_deleted', false)
        ->where('is_blocked', false)
        ->where('is_encours', true)
        ->where('is_actif',true)
        ->first();

    if ($promotion) {
        $promotionValue = $montantHousing* ($promotion->value / 100);
    }

    // Étape 6: Appliquer la réduction promo partenaire si applicable
    $promotionPartenaireValue = 0;
    $user = auth()->user();
    $userPartenaire = user_partenaire::where('id', $user->partenaire_id)->first();

    if ($userPartenaire) {
        $countReservationWithPromoInscription = Reservation::where('user_id', $user->id)
            ->where('valeur_reduction_code_promo', '>', 0)
            ->count();

        if ($countReservationWithPromoInscription < $userPartenaire->number_of_reservation) {
            $promotionPartenaireValue = $montantHousing* ($userPartenaire->reduction_traveler / 100);
        }
    }

    // Calculer le montant à payer après réduction, promotion et promotion partenaire
    $montantAPaye = $montantTotal - $reductionValue - $promotionValue - $promotionPartenaireValue;
    $required_paid_value =  $montantAPaye ;
    if ($is_tranch== 1) {
        $required_paid_value =  $montantAPaye / 2;

    }

    // Retourner les détails
    return [
        'error' => false,
        'montant_housing' => $montantHousing,
        'montant_charge' => $montantCharge,
        'montant_total' => $montantTotal,
        'reduction_value' => $reductionValue,
        'promotion_value' => $promotionValue,
        'promotion_partenaire_value' => $promotionPartenaireValue,
        'montant_a_paye' => $montantAPaye,
        'valeur_paye' =>   $required_paid_value

    ];
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
 *                 required={"housing_id", "date_of_starting", "date_of_end", "number_of_adult", "code_pays", "telephone_traveler", "heure_arrivee_max", "heure_arrivee_min", "is_tranche_paiement", "montant_total"},
 *                 @OA\Property(
 *                     property="housing_id",
 *                     type="integer",
 *                     description="ID du logement"
 *                 ),
 *                 @OA\Property(
 *                     property="date_of_starting",
 *                     type="string",
 *                     format="date",
 *                     description="Date de début de la réservation"
 *                 ),
 *                 @OA\Property(
 *                     property="date_of_end",
 *                     type="string",
 *                     format="date",
 *                     description="Date de fin de la réservation"
 *                 ),
 *                 @OA\Property(
 *                     property="number_of_adult",
 *                     type="integer",
 *                     description="Nombre d'adultes"
 *                 ),
 *                 @OA\Property(
 *                     property="number_of_child",
 *                     type="integer",
 *                     description="Nombre d'enfants"
 *                 ),
 *                 @OA\Property(
 *                     property="number_of_domestical_animal",
 *                     type="integer",
 *                     description="Nombre d'animaux domestiques"
 *                 ),
 *                 @OA\Property(
 *                     property="number_of_baby",
 *                     type="integer",
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
 *                     description="Code du pays"
 *                 ),
 *                 @OA\Property(
 *                     property="telephone_traveler",
 *                     type="integer",
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
 *                     description="Heure d'arrivée maximale"
 *                 ),
 *                 @OA\Property(
 *                     property="heure_arrivee_min",
 *                     type="string",
 *                     format="date-time",
 *                     description="Heure d'arrivée minimale"
 *                 ),
 *                 @OA\Property(
 *                     property="is_tranche_paiement",
 *                      type="integer",
 *                     description="Paiement en plusieurs tranches"
 *                 ),
 *                 @OA\Property(
 *                     property="montant_total",
 *                     type="number",
 *                     format="float",
 *                     description="Montant total"
 *                 ),
 *
 *                 @OA\Property(
 *                     property="valeur_reduction_hote",
 *                      type="integer",
 *                     description="valeur_reduction_hote"
 *                 ),
 *                 @OA\Property(
 *                     property="valeur_promotion_hote",
 *                      type="integer",
 *                     description="valeur_promotion_hote"
 *                 ),
 *                @OA\Property(
 *                     property="valeur_reduction_code_promo",
 *                      type="integer",
 *                     description="valeur_reduction_code_promo"
 *                 ),
 *               @OA\Property(
 *                     property="valeur_reduction_staturp",
 *                      type="integer",
 *                     description="valeur_reduction_staturp"
 *                 ),
 *               @OA\Property(
 *                     property="montant_charge",
 *                      type="integer",
 *                     description="montant_charge"
 *                 ),
 *               @OA\Property(
 *                     property="montant_housing",
 *                      type="integer",
 *                     description="montant_housing"
 *                 ),
 *              @OA\Property(
 *                     property="montant_a_paye",
 *                      type="integer",
 *                     description="montant_a_paye"
 *                 ),
  *              @OA\Property(
 *                     property="valeur_payee",
 *                      type="integer",
 *                     description="'valeur_payee'"
 *                 ),
 *
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

//return $request;

    $validatedData = Validator::make($request->all(), [
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
        'photo' => 'nullable|file|mimes:jpg,jpeg,png',
        'heure_arrivee_max' => 'required',
        'heure_arrivee_min' => 'required',
        'is_tranche_paiement' => 'required',
        'montant_total' => 'required|numeric',
        'valeur_payee' => 'required|numeric',
        // 'payment_method' => 'required|string',
        // 'id_transaction' => 'required|string',
        // 'statut_paiement' => 'required',
        'valeur_reduction_hote' => 'nullable|numeric',
        'valeur_promotion_hote' => 'nullable|numeric',
        'valeur_reduction_code_promo' => 'nullable|numeric',
        'valeur_reduction_staturp' => 'nullable|numeric',
        'montant_charge' => 'required|numeric',
        'montant_housing' => 'required|numeric',
        'montant_a_paye' => 'required|numeric',
    ]);

       // $method_paiement = $this->findSimilarPaymentMethod($request->payment_method);

    $message = [];

    if ($validatedData->fails()) {
        $message[] = $validatedData->errors();
        return (new ServiceController())->apiResponse(505,[],$message);
    }

    $user_id = Auth::id();
    $validatedData = $validatedData->validated();

    $validation_result = $this->canCreateReservation($validatedData['housing_id'], $validatedData['date_of_starting'], $validatedData['date_of_end'], $validatedData['number_of_domestical_animal']);

    if (!$validation_result['is_allowed']) {
        return (new ServiceController())->apiResponse(404,[], $validation_result['message']);

    }

    if(Housing::whereId($request->housing_id)->first()->is_accepted_photo == true){
        if(!$request->photo){
            return (new ServiceController())->apiResponse(404, [], 'La photo est obligatoire');

        }
    }

    $calculatedPriceDetails = $this->calculateTotalPrice(
        $validatedData['housing_id'],
        $validatedData['date_of_starting'],
        Carbon::parse($validatedData['date_of_starting'])->diffInDays($validatedData['date_of_end']),$validatedData['is_tranche_paiement']
    );


    if ($calculatedPriceDetails['error']) {
        return (new ServiceController())->apiResponse(404, [], $calculatedPriceDetails['message']);
    }

    // Validation des montants calculés
    if ($validatedData['montant_total'] != $calculatedPriceDetails['montant_total']) {
        return (new ServiceController())->apiResponse(404, [], "Le montant total envoyé est incorrect. Calculé: " . $calculatedPriceDetails['montant_total']);
    }

    if ($validatedData['montant_housing'] != $calculatedPriceDetails['montant_housing']) {
        return (new ServiceController())->apiResponse(404, [], "Le montant du logement envoyé est incorrect. Calculé: " . $calculatedPriceDetails['montant_housing']);
    }

    if ($validatedData['montant_charge'] != $calculatedPriceDetails['montant_charge']) {
        return (new ServiceController())->apiResponse(404, [], "Les charges envoyées sont incorrectes. Calculées: " . $calculatedPriceDetails['montant_charge']);
    }

    if ($validatedData['valeur_reduction_hote'] != $calculatedPriceDetails['reduction_value']) {
        return (new ServiceController())->apiResponse(404, [], "La réduction hôte envoyée est incorrecte. Calculée: " . $calculatedPriceDetails['reduction_value']);
    }

    if ($validatedData['valeur_promotion_hote'] != $calculatedPriceDetails['promotion_value']) {
        return (new ServiceController())->apiResponse(404, [], "La promotion envoyée est incorrecte. Calculée: " . $calculatedPriceDetails['promotion_value']);
    }

    if ($validatedData['montant_a_paye'] != $calculatedPriceDetails['montant_a_paye']) {
        return (new ServiceController())->apiResponse(404, [], "Le montant à payer envoyé est incorrect. Calculé: " . $calculatedPriceDetails['montant_a_paye']);
    }

    if ($validatedData['valeur_payee'] != $calculatedPriceDetails['valeur_paye']) {
        return (new ServiceController())->apiResponse(404, [], "Lea valeur  à payer envoyé est incorrect. Calculé: " . $calculatedPriceDetails['valeur_paye']);
    }


    (new PromotionController())->actionRepetitif($validatedData['housing_id']);


    try {
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
            'valeur_payee' =>0,
            'is_confirmed_hote' => false,
            'is_integration' => false,
            'is_rejected_traveler' => false,
            'is_rejected_hote' => false,
            'user_id' => $user_id,
            'photo' => 'defaut',
            'valeur_reduction_hote' => $validatedData['valeur_reduction_hote'] ?? 0,
            'valeur_promotion_hote' => $validatedData['valeur_promotion_hote'] ?? 0,
            'valeur_reduction_code_promo' => $validatedData['valeur_reduction_code_promo'] ?? 0,
            'valeur_reduction_staturp' => $validatedData['valeur_reduction_staturp'] ?? 0,
            'montant_charge' => $validatedData['montant_charge'] ?? 0,
            'montant_housing' => $validatedData['montant_housing'] ?? 0,
            'montant_a_paye' => $validatedData['montant_a_paye'] ?? 0,
        ]);
        $identity_profil_url = '';

         if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $identity_profil_url = $this->fileService->uploadFiles($request->file('photo'), 'image/photo_reservation', 'extensionImage');;
            if ($identity_profil_url['fails']) {
                return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
            }

            $reservation->photo = $identity_profil_url['result'];
            $reservation->save();
            }



        if ($request->has('country')) {
            $paymentData['country'] = $request->input('country');
        }




        $data = ["reservation" => $reservation,
             "valeur_payee" =>$calculatedPriceDetails['valeur_paye']
             ];

            return (new ServiceController())->apiResponse(200,$data, 'Réservation éffectuée avec succès');

    } catch (\Exception $e) {
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
}


/**
 * @OA\Post(
 *     path="/api/reservation/payReservation/{reservationId}",
 *     summary="Payer une réservation",
 *     description="Permet de payer une réservation avec diverses méthodes de paiement, y compris le portefeuille.",
 *     operationId="payReservation",
 *     tags={"Reservation"},
 *      security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="reservationId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         ),
 *         description="ID de la réservation à payer",
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={
 *                 "valeur_payee", "payment_method", "id_transaction", "statut_paiement"
 *             },
 *             @OA\Property(property="payment_method", type="string", example="portefeuille"),
 *             @OA\Property(property="id_transaction", type="string", example="abc123"),
 *             @OA\Property(property="statut_paiement", type="string", example="payé"),
 *             @OA\Property(property="valeur_payee", type="number", format="float", nullable=true, example=130.00),
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Paiement fait avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="message", type="string", example="Paiement fait avec succès"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="reservation_id", type="integer", example=1),
 *                 @OA\Property(property="amount", type="number", format="float", example=150.00),
 *                 @OA\Property(property="payment_method", type="string", example="portefeuille"),
 *                 @OA\Property(property="id_transaction", type="string", example="abc123"),
 *                 @OA\Property(property="statut", type="string", example="payé"),
 *                 @OA\Property(property="is_confirmed", type="boolean", example=true),
 *                 @OA\Property(property="is_canceled", type="boolean", example=false),
 *             ),
 *         ),
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Réservation non trouvée",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=404),
 *             @OA\Property(property="message", type="string", example="Réservation non trouvée"),
 *             @OA\Property(property="data", type="object", nullable=true),
 *         ),
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=500),
 *             @OA\Property(property="message", type="string", example="Message d'erreur détaillé"),
 *             @OA\Property(property="data", type="object", nullable=true),
 *         ),
 *     ),
 *
 *     @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT"
 *     )
 * )
 */


public function payReservation(Request $request,$reservationId){
    try {

        $validatedData = Validator::make($request->all(), [
            'valeur_payee' => 'required|numeric',
            'payment_method' => 'required|string',
            'id_transaction' => 'required|string',
            'statut_paiement' => 'required',
            // 'montant_a_paye' => 'nullable|numeric',
        ]);

        $reservation= Reservation::whereId($reservationId)->first();

        if(!$reservation){
            return (new ServiceController())->apiResponse(404,[], 'Reservation non trouvé');
        }

        if($reservation->statut == 'payee'){
            return (new ServiceController())->apiResponse(404,[], 'Reservation déjà payée');
        }
        $method_paiement = $this->findSimilarPaymentMethod($request->payment_method);

        $portfeuille = $this->findSimilarPaymentMethod("portfeuille");


        if($request->statut_paiement !=1 || $request->statut_paiement !="1"){
            return (new ServiceController())->apiResponse(404, [], 'Statut paiement doit être un booléen');
        }

        if ($reservation->is_tranche_paiement == 1) {
            $required_paid_value = $reservation->montant_a_paye / 2;
            if ($request->valeur_payee < $required_paid_value && $method_paiement != $portfeuille) {
                return (new ServiceController())->apiResponse(404, [], "Pour le paiement par tranche, la valeur payée doit être au moins la moitié du montant à payer soit $required_paid_value FCFA");
            }
            if ($method_paiement == $portfeuille) {
                $montant = $required_paid_value;
            }else{
                $montant = $required_paid_value;
            }
        } else {
            if ($request->valeur_payee <  $reservation->montant_a_paye) {
                return (new ServiceController())->apiResponse(404, [], "Pour le paiement complet, la valeur payée doit être égale au montant à payer soit {$reservation->montant_a_paye} FCFA");
            }
            if ($method_paiement == $portfeuille) {
                $montant = $reservation->montant_a_paye;
            }else{
                $montant = $request->valeur_payee;
            }
        }


        if ($method_paiement == $portfeuille) {
            $user_id = Auth::id();
                $portefeuille = Portfeuille::where('user_id', $user_id)->first();

                if (!$portefeuille) {
                    return (new ServiceController())->apiResponse(404, [], 'Portefeuille introuvable');
                }

                if ($portefeuille->solde < $montant) {
                    return (new ServiceController())->apiResponse(404, [], 'Solde insuffisant dans le portefeuille pour pouvoir réserver');
                }
            }

            if ($method_paiement == $portfeuille) {

                }




        $existTransaction = Payement::where('id_transaction',$request->id_transaction)->exists();
        if ($existTransaction) {
            return (new ServiceController())->apiResponse(404, [], 'L\'id de la transaction existe déjà');
        }

        DB::beginTransaction();
        $payment = new Payement();
        $payment->reservation_id = $reservation->id;
        $payment->amount = $montant;
        $payment->payment_method = $method_paiement;
        $payment->id_transaction = $request->id_transaction;
        $payment->user_id = Auth::user()->id;
        $payment->statut = $request->statut_paiement;
        $payment->is_confirmed = true;
        $payment->is_canceled = false;


            $user_id = Auth::id();

            if ($method_paiement == $portfeuille) {
                $portefeuille = Portfeuille::where('user_id', $user_id)->first();
                $portefeuille->solde -= $montant;

                $portefeuilleTransaction = new Portfeuille_transaction();
                $portefeuilleTransaction->debit = false;
                $portefeuilleTransaction->credit = false;
                $portefeuilleTransaction->amount = $montant;
                $portefeuilleTransaction->motif = "Réservation effectuée avec portefeuille";
                $portefeuilleTransaction->operation_type = 'debit';
                $portefeuilleTransaction->reservation_id = $reservation->id;
                $portefeuilleTransaction->payment_method = $method_paiement;
                $portefeuilleTransaction->id_transaction = 0;
                $portefeuilleTransaction->portfeuille_id = $portefeuille->id;

                $reservation->statut = 'payee';
                $reservation->valeur_payee = $montant;

                $reservation->save();
                $portefeuilleTransaction->save();
                $portefeuille->save();

                $this->initialisePortefeuilleTransaction($portefeuilleTransaction->id);
            }else{
                if($request->statut_paiement ==1){
                    $portefeuilleTransaction = new Portfeuille_transaction();
                    $portefeuilleTransaction->credit = true;
                    $portefeuilleTransaction->debit = false;
                    $portefeuilleTransaction->amount =  $montant;
                    $portefeuilleTransaction->motif = "Réservation effectuée avec autre moyen que le portefeuille";
                    $portefeuilleTransaction->reservation_id = $reservation->id;
                    $portefeuilleTransaction->payment_method = $method_paiement;
                    $portefeuilleTransaction->id_transaction = $request->id_transaction;

                    $reservation->statut = 'payee';
                    $reservation->valeur_payee = $montant;
                    $reservation->save();
                    $portefeuilleTransaction->save();

                    $this->initialisePortefeuilleTransaction($portefeuilleTransaction->id);
                }
            }



        $payment->motif = $portefeuilleTransaction->motif??"Echec de payement survenu lors du payement de la réservation";

        $payment->save();

        DB::commit();



        if($request->statut_paiement ==1 || $method_paiement == $portfeuille){
            $mail_to_host = [
                'title' => 'Nouvelle Réservation',
                'body' => "Bonne nouvelle ! Votre logement a été réservé par un utilisateur. Détails de la réservation :\n" .
                    " Date de début : " . $reservation->date_of_starting . "\n" .
                    " Date de fin : " . $reservation->date_of_end . "\n"
            ];
            $mail_to_traveler = [
                'title' => 'Confirmation de Réservation',
                'body' => "Félicitations ! Vous avez réservé un logement. D'ici 24 heures, l'hôte confirmera ou rejettera la réservation. Dates de réservation : du " . $reservation->date_of_starting . " au " . $reservation->date_of_end . "."
            ];

            dispatch(new SendRegistrationEmail(auth()->user()->email, $mail_to_traveler['body'],$mail_to_traveler['title'], 2));

            dispatch(new SendRegistrationEmail(auth()->user()->email, $mail_to_host['body'],$mail_to_host['title'], 2));

            return (new ServiceController())->apiResponse(200,$payment, 'Paiement fait avec succès');
        }else{
            return (new ServiceController())->apiResponse(200, [], "Echec de paiement");
        }

} catch(\Exception $e) {
    DB::rollBack();
    return (new ServiceController())->apiResponse(500,[],$e->getMessage());
}
}


public function findSimilarPaymentMethod($inputMethod)
{
    // Récupère toutes les méthodes de paiement depuis la base de données
    $paymentMethods = DB::table('method_payements')->pluck('name');

    // Normalise l'entrée utilisateur
    $normalizedInput = strtolower(trim($inputMethod));

    // Initialisation de la variable pour la méthode la plus similaire
    $closestMatch = null;
    $highestSimilarity = 0;

    // Parcourt toutes les méthodes de paiement disponibles
    foreach ($paymentMethods as $method) {
        // Normalise la méthode de paiement actuelle
        $normalizedMethod = strtolower($method);

        // Calcule la similarité entre l'entrée et la méthode courante
        similar_text($normalizedInput, $normalizedMethod, $similarity);

        // Si la similarité est la plus élevée rencontrée jusqu'à présent, on la garde
        if ($similarity > $highestSimilarity) {
            $highestSimilarity = $similarity;
            $closestMatch = $method;
        }
    }

    // Si la similarité est suffisante, retourne la méthode correspondante, sinon retourne l'entrée d'origine
    return $highestSimilarity > 80 ? $closestMatch : $inputMethod;
}



    /**
     * @OA\Put(
     *     path="/api/reservation/hote_confirm_reservation/{idReservation}",
     *     summary="Confirmer la reservation d un voyageur sur un de ses biens",
     * description="Confirmer la reservation d un voyageur sur un de ses biens",
     *     tags={"Dashboard hote"},
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
            return (new ServiceController())->apiResponse(404,[], "Reservation non trouvée");

          }
          if (!($reservation->housing->user_id == Auth::user()->id)) {
            return (new ServiceController())->apiResponse(404,[], "Impossible de confirmer la réservation d un logement qui ne vous appartient pas");

          }
          if ($reservation->is_rejected_hote) {
            return (new ServiceController())->apiResponse(404,[], "Vous ne pouvez pas confirmer une réservation déjà rejetée");

          }
          if ($reservation->is_confirmed_hote) {
            return (new ServiceController())->apiResponse(404,[], "La reservation avait déjà été confirmée auparavant.");

          }
          $reservation->is_confirmed_hote = 1;
          $reservation->save();
          $mail_to_traveler = [
            "title" => "Confirmation  de  réservation",
            "body" => " Votre réservation concernant le logement '{$reservation->housing->name}'.   vient d'être confirmée par l'hôte"
                ];
        dispatch(new SendRegistrationEmail(auth()->user()->email, $mail_to_traveler['body'],$mail_to_traveler['title'], 2));

        //Mail::to($reservation->user->email)->send(new NotificationEmailwithoutfile($mail) );
        return (new ServiceController())->apiResponse(200,[], 'Confirmation de réservation éffectuée avec succès');

        } catch(Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());

        }

     }

               /**
     * @OA\Put(
     *     path="/api/reservation/hote_reject_reservation/{idReservation}",
     *     summary="Rejeter la reservation d un voyageur sur un de ses biens avec un motif à l'appui",
     * description="Rejeter la reservation d un voyageur sur un de ses biens avec un motif à l'appui",
     *     tags={"Dashboard hote"},
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

        $validatedData = Validator::make($request->all(), [
            'motif_rejet_hote' =>'required | string',
        ]);

        $message = [];

        if ($validatedData->fails()) {
            $message[] = $validatedData->errors();
            return (new ServiceController())->apiResponse(505,[],$message);
        }
           $reservation = Reservation::find($idReservation);
          if(!$reservation){
            return (new ServiceController())->apiResponse(404,[], "Reservation non trouvée");

          }
          if (!($reservation->housing->user_id == Auth::user()->id)) {
            return (new ServiceController())->apiResponse(404,[], "Impossible de rejeter la réservation d un logement qui ne vous appartient pas.");

          }
          if ($reservation->is_confirmed_hote) {
            return (new ServiceController())->apiResponse(404,[], "Vous ne pouvez pas rejeter une réservation que vous avez déjà confirmé. ");

          }

          if ($reservation->is_rejected_hote) {
            return (new ServiceController())->apiResponse(404,[], "Cette reservation avait déjà été rejetée . ");

          }
          $portfeuille = (new ReservationController())->findSimilarPaymentMethod("portfeuille");
          if(Reservation::whereId($idReservation)->update([
            'is_rejected_hote'=>1,
            'motif_rejet_hote'=>$request->motif_rejet_hote
          ])){

            DB::beginTransaction();

            $portfeuille =Portfeuille::where('user_id',$reservation->user_id)->first();
            $portfeuille->where('user_id',$reservation->user_id)->update(['solde'=>$reservation->user->portfeuille->solde + $reservation->valeur_payee]);
            $transaction = new portfeuille_transaction();
            $transaction->portfeuille_id = $portfeuille->id;
            $transaction->amount = $reservation->valeur_payee;
            $transaction->operation_type = 'credit';
            $transaction->debit = false;
            $transaction->credit =false;
            $transaction->reservation_id = $reservation->id;
            $transaction->payment_method = $portfeuille;
            $transaction->motif = "Remboursement suite à un rejet de la réservation par l'hôte";
            $transaction->save();
             $this->initialisePortefeuilleTransaction($transaction->id);
             DB::commit();

          }

          $mail = [
            "title" => "Rejet de votre réservation",
            "body" => " Votre réservation concernant le logement '{$reservation->housing->name}'. vient d'être rejetée  par l'hôte pour le motif suivant << $request->motif_rejet_hote>>.Votre portefeuille a été crédité de {$reservation->montant_total} FCFA.Nouveau solde:{$portfeuille->user->portfeuille->solde}FCFA "
            ];
        dispatch( new SendRegistrationEmail($reservation->user->email, $mail['body'], $mail['title'], 2));

        // Mail::to($reservation->user->email)->send(new NotificationEmailwithoutfile($mail) );

        $adminRole = DB::table('rights')->where('name', 'admin')->first();

         if (!$adminRole) {
             return (new ServiceController())->apiResponse(404, [], 'Le rôle d\'admin n\'a pas été trouvé.');
         }

         $adminUsers = User::whereHas('user_right', function ($query) use ($adminRole) {
             $query->where('right_id', $adminRole->id);
         })->get();

         foreach ($adminUsers as $adminUser) {

             $mail = [
                 'title' => "Rejet d'une réservation par l'hôte",
                 'body' => "Une reservation vient d\être annulé  par l hote pour le motif suivant << $request->motif_rejet_hote >>  et le logement appartient à {$reservation->housing->user->firstname} {$reservation->housing->user->lastname}."
             ];

            dispatch( new SendRegistrationEmail($adminUser->email, $mail['body'], $mail['title'], 2));
         }
         return (new ServiceController())->apiResponse(200,[], 'rejet de réservation éffectué avec succès');


          } catch(Exception $e) {
            DB::rollBack();
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());

          }
     }

     // Notification d'annulation d'une réservation par un voyageur
     public function notifyAnnulation(Request $request,$reservation_id,$mailtraveler,$mailhote,$montant_commission){
        $reservation = Reservation::find($reservation_id);

        dispatch( new SendRegistrationEmail($reservation->user->email, $mailtraveler['body'], $mailtraveler['title'], 2));


       //Mail::to($reservation->user->email)->send(new NotificationEmailwithoutfile($mailtraveler) );


       // Mail::to($reservation->housing->user->email)->send(new NotificationEmailwithoutfile($mailhote) );
        dispatch( new SendRegistrationEmail($reservation->housing->user->email, $mailhote['body'], $mailhote['title'], 2));

      $right = Right::where('name','admin')->first();
      $adminUsers = User_right::where('right_id', $right->id)->get();
        foreach ($adminUsers as $adminUser) {

            $mail = [
                "title" => "Annulation d'une réservation par un voyageur",
                "body" => "Une réservation vient d'être annulée par un client avec l'identifiant {$reservation->user->id}. Le motif de l'annulation est le suivant : « $request->motif_rejet_traveler ». Le logement appartient à {$reservation->housing->user->firstname} {$reservation->housing->user->lastname}, avec l'identifiant {$reservation->housing->user->id}. Vous recevez une commission de {$montant_commission} FCFA sur cette opération"
            ];

         // Mail::to($adminUser->user->email)->send(new NotificationEmailwithoutfile($mail) );
         dispatch( new SendRegistrationEmail($adminUser->user->email, $mail['body'], $mail['title'], 2));

        }
     }

              /**
     * @OA\Put(
     *     path="/api/reservation/traveler_reject_reservation/{idReservation}",
     *     summary="Annulation d une reservation par un voyageur avec le motif à l'appui",
     * description="Annulation d une reservation par un voyageur avec le motif à l'appui",
     *     tags={"Dashboard traveler"},
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
            $validatedData = Validator::make($request->all(), [
                'motif_rejet_traveler' =>'required | string',
            ]);

            $message = [];

            if ($validatedData->fails()) {
                $message[] = $validatedData->errors();
                return (new ServiceController())->apiResponse(505,[],$message);
            }


            $reservation = Reservation::find($idReservation);
          if(!$reservation){

            return (new ServiceController())->apiResponse(404,[], "Reservation non trouvée. ");

          }

          if (!($reservation->user_id == Auth::user()->id)) {
            return (new ServiceController())->apiResponse(404,[], "Impossible d annuler la réservation d un logement dont vous n avez pas fait la réservation. ");

          }



        if ($reservation->is_rejected_hote) {
            return (new ServiceController())->apiResponse(404,[], "Vous ne pouvez pas annuler une reservation déjà rejetée par l hote. ");


        }

        if ($reservation->is_rejected_traveler) {
            return (new ServiceController())->apiResponse(404,[], "Vous ne pouvez pas rejeter une reservation déjà rejetée par vous même. ");

         }

        if($reservation->housing->is_accept_anulation == false){
            return (new ServiceController())->apiResponse(404,[], "Ce logement n'accepte pas d'annulation après sa réservation. ");

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

        $reservation->is_rejected_traveler = 1;
        $reservation->motif_rejet_traveler = $request->motif_rejet_traveler;
        $reservation->save();



            $soldeTotal = Portfeuille_transaction::where('credit', true)->sum('amount')-Portfeuille_transaction::where('debit', true)->sum('amount');
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
              $transaction->credit =0;
              $transaction->reservation_id = $reservation->id;
              $transaction->payment_method = "portfeuille";
              $transaction->motif = "Remboursement intégral suite à l'annulation de la réservation par le client";
              $transaction->valeur_commission = 0;
              $transaction->montant_commission = 0;
              $transaction->montant_restant = $montantClient;
              $transaction->solde_commission = $soldeCommission  + 0;
              $$transaction->operation_type = 'credit';

              $transaction->save();

              $titre_partenaire="Message de Confirmation d'annulation de la reservation au partenaire";

              $this->handlePartnerLogic($transaction->id,false,$titre_partenaire);

              $mailtraveler = [
                'title' => 'Message de confirmation d\'annulation de la reservation au voyageur ',
                'body' => "Votre annulation a été prise en compte. Vous bénéficiez d'un remboursement intégral et votre portefeuille a été crédité de $montantClient FCFA. Solde actuel : $portefeuilleClient->solde FCFA."
               ];

               $mailhote = [
                'title' => "Mesage de confirmation d'nnulation d'une reservation à l'hôte",
                'body' => "La réservation d'un de vos biens a été annulée. L'annulation a entraîné un remboursement intégral au voyageur .Vous ne recevez donc rien à propos de cette opération de remboursement intégral."
               ];

              $this->notifyAnnulation($request, $reservation->id,$mailtraveler,$mailhote,$montant_commission);
              $data = [
                'durée en jours' => $totalDays,
                'durée  en heure' =>$diffEnHeure,
                'delai d annulation pour ne pas obtenir un  remboursement intégral  ' => $delai,
                'montant' => $reservation->valeur_payee,
                'montantHote' => $montantHote,
                'fraisLouerMeublee' =>$montant_commission,
                'montant retourné au client' => $montantClient,
                    ];


            return (new ServiceController())->apiResponse(200,$data, 'Reservation annulée avec succès');


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
           $transaction->credit =0;
           $transaction->reservation_id = $reservation->id;
           $transaction->payment_method = "portfeuille";
           $transaction->motif = "Remboursement suite à l\' annulation de la réservation par le client";
           $transaction->valeur_commission = 0;
           $transaction->montant_commission = 0;
           $transaction->montant_restant = $montantClient;
           $transaction->solde_commission = $soldeCommission  + 0;
           $$transaction->operation_type = 'credit';
           $transaction->save();
           $this->initialisePortefeuilleTransaction($transaction->id);


           $soldeTotal =  $soldeTotal  + $montantClient;

           $portefeuilleHote = Portfeuille::find($reservation->housing->user->portfeuille->id);
           $portefeuilleHote->update(['solde' => $portefeuilleHote->solde + $montantHote]);

           $transaction = new Portfeuille_transaction();
           $transaction->portfeuille_id = $portefeuilleHote->id;
           $transaction->amount = $montantWithoutClient;
           $transaction->debit = 0;
           $transaction->credit =0;
           $transaction->reservation_id = $reservation->id;
           $transaction->payment_method = "portfeuille";
           $transaction->motif = "Remboursement suite à l\' annulation de la réservation par le client";
           $transaction->valeur_commission = $reservation->housing->user->commission->valeur;
           $transaction->montant_commission = $montant_commission;
           $transaction->montant_restant = $montantHote;
           $transaction->solde_commission = $soldeCommission + $montant_commission;
           $$transaction->operation_type = 'credit';

           $transaction->save();
           $titre_partenaire="Message de Confirmation d'annulation d'une reservation au partenaire";

           $this->handlePartnerLogic($transaction->id,true,$titre_partenaire);


           $mailtraveler = [
            'title' => "Message de Confirmation d'annulation d'une reservation  au voyageur",
            'body' => "Votre annulation a été prise en compte. Vous bénéficiez d'un remboursement partiel et votre portefeuille a été crédité de $montantClient FCFA. Solde actuel : $portefeuilleClient->solde FCFA."
           ];

            $mailhote = [
            'title' => "Message de Confirmation d'annulation d'une reservation à l'hôte",
            'body' => "La réservation d'un de vos biens a été annulée. L'annulation a entraîné un remboursement partiel, et votre portefeuille a été crédité de {$montantHote} FCFA. Solde actuel : $portefeuilleHote->solde FCFA."
            ];

            $transaction=Portfeuille_transaction::find($transaction->id);
            $this->notifyAnnulation($request, $reservation->id,$mailtraveler,$mailhote,$transaction->montant_commission_admin);
            $data = [
                'durée en jours' => $totalDays,
                'durée  en heure' =>$diffEnHeure,
                'delai d annulation pour ne pas obtenir un  remboursement intégral  ' => $delai,
                'montant' => $reservation->valeur_payee,
                'montantHote' => $montantHote,
                'fraisLouerMeublee' =>$montant_commission,
                'montant retourné au client' => $montantClient,
                    ];


            return (new ServiceController())->apiResponse(200,$data, 'Reservation annulée avec succès');

         }else{

            $montantClient =  0;
            $montantWithoutClient = $reservation->valeur_payee - $montantClient;
            $montant_commission = ($montantWithoutClient * $reservation->housing->user->commission->valeur)/100;
            $montantHote = $montantWithoutClient - $montant_commission;

            $portefeuilleHote = Portfeuille::find($reservation->housing->user->portfeuille->id);
            $portefeuilleHote->update(['solde' => $portefeuilleHote->solde + $montantHote]);
            $transaction = new Portfeuille_transaction();
            $transaction->portfeuille_id = $portefeuilleHote->id;
            $transaction->amount = $montantWithoutClient;
            $transaction->debit = 0;
            $transaction->credit =0;
            $transaction->reservation_id = $reservation->id;
            $transaction->payment_method = "portfeuille";
            $transaction->motif = "Remboursement suite à l\' annulation de la réservation par le client(Le client ne reçoit rien)";
            $transaction->valeur_commission = $reservation->housing->user->commission->valeur;
            $transaction->montant_commission = $montant_commission;
            $transaction->montant_restant = $montantHote;
            $transaction->solde_commission = $soldeCommission  + $montant_commission;
            $$transaction->operation_type = 'credit';

            $transaction->save();
            $titre_partenaire="Message de Confirmation d'annulation d'une reservation au partenaire";

            $this->handlePartnerLogic($transaction->id,true,$titre_partenaire);
            $portefeuilleClient = Portfeuille::find($reservation->user->portfeuille->id);

            $mailtraveler = [
                'title' => 'Message de confirmation d\'annulation d\'une reservation au voyageur',
                'body' => "Votre annulation a été prise en compte. Cependant, il n'y a pas de remboursement, donc votre portefeuille n'a pas été crédité. Solde actuel : $portefeuilleClient->solde FCFA."
                       ];

            $mailhote = [
                'title' => "Message de confirmation d'annulation d'une reservation à l'hôte",
                'body' => "La réservation d'un de vos biens a été annulée. L'annulation a entraîné un remboursement partiel, et votre portefeuille a été crédité de {$montantHote} FCFA. Solde actuel : $portefeuilleHote->solde FCFA."
                       ];

            $transaction=Portfeuille_transaction::find($transaction->id);
            $this->notifyAnnulation($request, $reservation->id,$mailtraveler,$mailhote,$transaction->montant_commission_admin);

            $data = [
                'durée en jours' => $totalDays,
                'durée  en heure' =>$diffEnHeure,
                'montant' => $reservation->valeur_payee,
                'montantHote' => $montantHote,
                'fraisLouerMeublee' =>$montant_commission,
                'montant retourné au client' => $montantClient,
                    ];


            return (new ServiceController())->apiResponse(200,$data, 'Reservation annulée avec succès');
         }

     } catch(Exception $e) {
    return (new ServiceController())->apiResponse(500,[],$e->getMessage());

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

            return (new ServiceController())->apiResponse(404,[], "Logement non trouvé. ");

        }

        $reservations = Reservation::where('housing_id', $housingId)->get();

        $reservationCount = $reservations->count();

        $data = [
            'housing' => $housing,
            'reservations' => $reservations,
            'reservation_count' => $reservationCount,
                ];


        return (new ServiceController())->apiResponse(200,$data, 'Liste des reservations recupérées avec succès');
       }

/**
     * @OA\Get(
     *     path="/api/reservation/getDateOfReservationsByHousingId/{housingId}",
     *     summary="Liste des dates de réservations par logement",
     * description="Liste des dates de réservations par logement",
     *     tags={"Reservation"},
     *   @OA\Parameter(
     *         name="housingId",
     *         in="path",
     *         required=true,
     *         description="Get housing ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="date of reservation"
     *
     *     )
     * )
     */
    public function getDateOfReservationsByHousingId($housingId)
    {
        $housing = Housing::where('id', $housingId)->first();

        if (!$housing) {
            return (new ServiceController())->apiResponse(404, [], "Logement non trouvé.");
        }

        $reservations = Reservation::where('housing_id', $housingId)->where('is_rejected_traveler', false)->where('is_rejected_hote', false)->where('is_confirmed_hote',true)->where('statut','payee')->get();

        $data = [
            'reservations' => $reservations->map(function($reservation) use ($housing) {
                $existing_end = Carbon::parse($reservation->date_of_end);

                $minimum_start_date = $existing_end->copy()->addDays($housing->time_before_reservation);

                return [
                    'date_of_starting' => $reservation->date_of_starting,
                   'date_of_end' => $minimum_start_date->toDateString(),
                ];
            })
        ];

        return (new ServiceController())->apiResponse(200, $data, 'Liste des dates de réservations récupérées avec succès.');
    }


        /**
          * @OA\Get(
          *     path="/api/reservation/showDetailOfReservationForHote/{idReservation}",
          *     summary="Détail d'une réservation côté hote",
          * description="Détail d'une réservation côté hote",
          *     tags={"Dashboard hote"},
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

             return (new ServiceController())->apiResponse(404,[], "Reservation non trouvée. ");

         }

         if (!(Auth::user()->id == $reservation->housing->user_id)) {
              return (new ServiceController())->apiResponse(404,[], "Vous ne pouvez pas consulter les détails d' une réservation qui ne vous concerne pas. ");

         }

         $data = [
            'detail de la reservation' => $reservation->toArray(),
            'voyageur' => $reservation->user->toArray()
         ];


       return (new ServiceController())->apiResponse(200,$data, 'Detail de reservation recupéré avec succès');
     }

     /**
 * @OA\Post(
 *     path="/api/reservation/confirmIntegration",
 *     summary="Confirmer l'intégration après une réservation(c'est le voyageur qui confirme)",
 *   security={{"bearerAuth": {}}},
 *     description="Confirme l'intégration d'une réservation après vérification des conditions nécessaires.",
 *     tags={"Dashboard traveler"},
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
    DB::beginTransaction();

    try {
        $reservation = Reservation::find($request->input('reservation_id'));

        if (!$reservation) {
            return (new ServiceController())->apiResponse(404, [], "Réservation non trouvée.");
        }

        if (!$reservation->is_confirmed_hote) {
            return (new ServiceController())->apiResponse(404, [], "La réservation doit être confirmée par l'hôte.");
        }

        if ($reservation->is_rejected_traveler || $reservation->is_rejected_hote) {
            return (new ServiceController())->apiResponse(404, [], "La réservation a été rejetée, donc vous ne pouvez pas confirmer l'intégration.");
        }

        if ($reservation->montant_a_paye > $reservation->valeur_payee) {
            return (new ServiceController())->apiResponse(404, [], "Veuillez solder la deuxième tranche avant de confirmer l'intégration.");
        }

        if ($reservation->is_integration) {
            return (new ServiceController())->apiResponse(404, [], "L'intégration a déjà été confirmée auparavant.");
        }

        $housing = Housing::find($reservation->housing_id);

        if (!$housing) {
            return (new ServiceController())->apiResponse(404, [], "Logement non trouvé.");
        }

        $owner = User::find($housing->user_id);

        if (!$owner) {
            return (new ServiceController())->apiResponse(404, [], "Propriétaire non trouvé.");
        }

        $commission = Commission::where('user_id', $owner->id)->first();

        if (!$commission) {
            return (new ServiceController())->apiResponse(404, [], "Commission non trouvée pour ce propriétaire.");
        }

        if ($reservation->is_tranche_paiement) {
            $paiementEspece = Payement::where('reservation_id', $reservation->id)
                                        ->where('payment_method', 'espece')
                                        ->where('statut', 1)
                                        ->first();

            if ($paiementEspece) {
                $amount = $paiementEspece->amount;
                $rest=$reservation->valeur_payee- $paiementEspece->amount;
                 $message="Virement sur le compte de l'hôte de la première tranche(suite à la soustraction des commission) après confirmation de l'intégration .La deuxième tranche a été payé en espèce directement à l'hôte";


            }
        } else {
            $rest=$reservation->valeur_payee;
             $message="Virement sur le compte de l'hôte du montant complet(suite à la soustraction des commission) après confirmation de l'intégration.";
        }
        $rest = $rest??$reservation->valeur_payee;
        $message=  $message??"Virement sur le compte de l'hôte du montant après confirmation de l'intégration du voyageur.";

        $commission_percentage = $commission->valeur;
        $total_amount = $reservation->valeur_payee;

        $commission_amount = $total_amount * ($commission_percentage / 100);
        $remaining_amount = $rest - $commission_amount;

        $reservation->is_integration = true;
        $reservation->save();

        $previous_transactions = Portfeuille_transaction::all();
        $solde_total = Portfeuille_transaction::where('credit', true)->sum('amount')-Portfeuille_transaction::where('debit', true)->sum('amount');
        $solde_commission = $previous_transactions->sum('montant_commission');
        $solde_restant = $previous_transactions->sum('montant_restant');
        $solde_commission_admin = $previous_transactions->sum('montant_commission_admin');

        $new_solde_total = $solde_total + $total_amount;
        $new_solde_commission = $solde_commission + $commission_amount;
        $new_solde_restant = $solde_restant + $remaining_amount;
        $portefeuilleTransaction = new Portfeuille_transaction();
        $portefeuilleTransaction->debit = false;
        $portefeuilleTransaction->credit = false;
        $portefeuilleTransaction->amount = $rest;
        $portefeuilleTransaction->valeur_commission = $commission_percentage;
        $portefeuilleTransaction->montant_commission = $commission_amount;
        $portefeuilleTransaction->montant_restant = $remaining_amount;
        $portefeuilleTransaction->solde_total = $new_solde_total;
        $portefeuilleTransaction->solde_commission = $new_solde_commission;
        $portefeuilleTransaction->operation_type = 'credit';
        $portefeuilleTransaction->solde_restant = $new_solde_restant;
        $portefeuilleTransaction->motif =$message;
        $portefeuilleTransaction->reservation_id = $reservation->id;
        $portefeuilleTransaction->portfeuille_id = $owner->portfeuille->id;
        $portefeuilleTransaction->id_transaction = "0";
        $portefeuilleTransaction->payment_method = "portfeuille";

        $portefeuilleTransaction->save();
        $titre_partenaire="Virement de votre part en tant que partenaire suite à la confirmation de l'intégration d'un voyageur";


       $this->handlePartnerLogic($portefeuilleTransaction->id, true,$titre_partenaire);





        $portefeuille = Portfeuille::where('user_id', $owner->id)->first();

        if (!$portefeuille) {
            return (new ServiceController())->apiResponse(404, [], "Portefeuille du propriétaire non trouvé.");
        }

        $portefeuille->solde += $remaining_amount;
        $portefeuille->save();

         DB::commit();

        $transaction=Portfeuille_transaction::find($portefeuilleTransaction->id);


        $mail = [
            "title" => "Confirmation de l'intégration d'un voyageur",
            "body" => "Un voyageur vient de confirmer l'intégration dans votre logement intitulé {$reservation->housing->name}. Vous venez de recevoir un dépôt de {$remaining_amount} FCFA sur votre portefeuille. Nouveau solde: {$portefeuille->solde} FCFA"
        ];

        dispatch(new SendRegistrationEmail($reservation->housing->user->email, $mail['body'], $mail['title'], 2));


        $right = Right::where('name', 'admin')->first();
        $adminUsers = User_right::where('right_id', $right->id)->get();

        foreach ($adminUsers as $adminUser) {


            $mail = [
                "title" => " Confirmation de l'intégration d'un voyageur dans un logement",
                "body" => "Le client ayant fait une réservation pour le logement {$reservation->housing->name} vient de confirmer son intégration à ce logement. Vous bénéficiez d'une commission de {$transaction->montant_commission_admin} FCFA"
            ];
            dispatch(new SendRegistrationEmail($adminUser->user->email, $mail['body'], $mail['title'], 2));


        }


        return (new ServiceController())->apiResponse(200,[], 'Intégration confirmée avec succès');


    } catch (\Exception $e) {
        DB::rollBack();
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());

    }
}



// Mettre a jours le statut de la promotion en cours;voir si la date n'est pas dépassé ou bien le nombre de reservation n'est pas atteint
public function checkAndUpdateIsEncours($housingId)
{
    try {
        $currentDate = Carbon::now();

        $promotion = promotion::where('housing_id', $housingId)
                              ->where('is_encours', true)
                              ->where('date_debut', '<=', $currentDate)
                              ->where('date_fin', '>=', $currentDate)->first();

        if (!$promotion) {
            return response()->json(['error' => 'Aucune promotion en cours trouvée pour ce logement.'], 404);
        }

         $totalReservations = Reservation::where('housing_id', $housingId)
                                        ->where('date_of_reservation', '>=', $promotion->date_debut)
                                        ->where('date_of_reservation', '<=', $promotion->date_fin)
                                        ->count();

        // Condition 1: Nombre de réservations atteint ou dépassé
        if ($totalReservations >= $promotion->number_of_reservation) {

            $promotion->is_encours = false;
            $promotion->save();
            return response()->json(['message' => 'Promotion terminée car le nombre de réservations a été atteint.'], 200);
        }

        // Condition 2: Date de fin dépassée
        if ($currentDate->greaterThanOrEqualTo(Carbon::parse($promotion->date_fin))) {

            $promotion->is_encours = false;
            $promotion->save();
            return response()->json(['message' => 'Promotion terminée car la date de fin est passée.'], 200);
        }

        return response()->json(['message' => 'La promotion est encore en cours.'], 200);

    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


public function handlePartnerLogic($transactionId,$is_received=true,$titre="")
{
    $transaction = Portfeuille_transaction::find($transactionId);

    if (!$transaction) {
        return response()->json(['message' => 'Transaction non trouvée'], 404);
    }

    $reservation = Reservation::find($transaction->reservation_id);

    if (!$reservation) {
        return response()->json(['message' => 'Réservation non trouvée'], 404);
    }

        $soldeCredit = Portfeuille_transaction::where('credit', true)->sum('amount');
        $soldeDebit = Portfeuille_transaction::where('debit', true)->sum('amount');


    if ($reservation->valeur_reduction_code_promo != 0) {
        $email_partenaire = $reservation->user->Partenaire->user->email;
        $user_id_partenaire = $reservation->user->Partenaire->user->id;
        $commission_partenaire = $reservation->user->Partenaire->commission;
        $partenaire_id = $reservation->user->Partenaire->id;
        $ancien_solde_commission_partenaire = Portfeuille_transaction::all()->sum('montant_commission_partenaire');

        $commission_amount = $transaction->montant_commission;
        $montant_commission_partenaire = $commission_amount * ($commission_partenaire / 100);
        $montant_commission_admin=$commission_amount-$montant_commission_partenaire;
        // Mettre à jour la commission totale et le solde de la commission dans la transaction
        $transaction->montant_commission_partenaire = $montant_commission_partenaire;
        $transaction->solde_commission_partenaire= $ancien_solde_commission_partenaire+$montant_commission_partenaire;
        $transaction->valeur_commission_partenaire=$commission_partenaire;
        $transaction->partenaire_id=$partenaire_id;

        // mettre à jour le portefeuille du partenaire
        $portefeuille_partenaire = Portfeuille::where('user_id', $user_id_partenaire)->first();

        if (!$portefeuille_partenaire) {
            return response()->json(['message' => 'Portefeuille du partenaire non trouvé'], 404);
        }

        // Calculer les nouvelles valeurs pour le portefeuille du partenaire
        $portefeuille_partenaire->solde += $montant_commission_partenaire;
        $portefeuille_partenaire->save();

        //Remplissage de la part de l'admin
        $ancien_solde_commission_admin = Portfeuille_transaction::all()->sum('montant_commission_admin');
        $transaction->montant_commission_admin=$montant_commission_admin;
        $transaction->new_solde_admin=$ancien_solde_commission_admin+$montant_commission_admin;
        $transaction->valeur_commission_admin=100-$commission_partenaire;
        $transaction->solde_credit = $soldeCredit;
        $transaction->solde_debit = $soldeDebit;
        $transaction->save();

        if($is_received==true){

            $mail = [
                "title" =>"{$titre}",
                "body" => "Vous venez de recevoir un dépôt de {$montant_commission_partenaire} FCFA sur votre portefeuille. Nouveau solde: {$portefeuille_partenaire->solde} FCFA"
            ];

        }else{

            $mail = [
                "title" => "{$titre}",
                "body" => "L'annulation a entraîné un remboursement intégral au voyageur .Vous ne recevez donc rien à propos de cette opération de remboursement intégral."
            ];

        }
        dispatch(new SendRegistrationEmail($email_partenaire, $mail['body'], $mail['title'], 2));




    }else{
        $ancien_solde_commission_admin = Portfeuille_transaction::all()->sum('montant_commission_admin');
        $ancien_solde_commission_partenaire = Portfeuille_transaction::all()->sum('montant_commission_partenaire');

        $transaction->montant_commission_admin= $transaction->montant_commission;
        $transaction->new_solde_admin=$ancien_solde_commission_admin+$transaction->montant_commission;
        $transaction->valeur_commission_admin=100;
        $transaction->solde_commission_partenaire=$ancien_solde_commission_partenaire;
        $transaction->montant_commission_partenaire =0;

        $transaction->valeur_commission_partenaire=0;
        $transaction->solde_credit = $soldeCredit;
        $transaction->solde_debit = $soldeDebit;

         $transaction->save();
    }

}

    // public function initialisePortefeuilleTransaction($id)
    // {
    //     $transaction = Portfeuille_transaction::find($id);

    //     if (!$transaction) {
    //         return response()->json(['error' => 'Transaction non trouvée.'], 404);
    //     }

    //     // Calculer les totaux nécessaires
    //     $solde_commission = Portfeuille_transaction::sum('montant_commission');
    //     $solde_total = Portfeuille_transaction::where('credit', true)->sum('amount')-Portfeuille_transaction::where('debit', true)->sum('amount');
    //     $solde_commission_partenaire = Portfeuille_transaction::sum('montant_commission_partenaire');
    //     $solde_restant = Portfeuille_transaction::sum('montant_restant');
    //     $solde_commission_admin = Portfeuille_transaction::sum('montant_commission_admin');

    //     // Mettre à jour les colonnes spécifiques
    //     $transaction->valeur_commission = 0;
    //     $transaction->montant_commission = 0;
    //     $transaction->montant_restant = $transaction->amount;
    //     $transaction->valeur_commission_partenaire = 0;
    //     $transaction->montant_commission_partenaire = 0;
    //     $transaction->valeur_commission_admin = 0;
    //     $transaction->montant_commission_admin = 0;
    //     $transaction->new_solde_admin = $solde_commission_admin;
    //     $transaction->solde_total = $solde_total;
    //     $transaction->solde_restant = $solde_restant + $transaction->amount;
    //     $transaction->solde_commission = $solde_commission;
    //     $transaction->solde_commission_partenaire = $solde_commission_partenaire;

    //     // Sauvegarder les modifications
    //     $transaction->save();

    // }

    public function initialisePortefeuilleTransaction($id)
    {
        $transaction = Portfeuille_transaction::find($id);

        if (!$transaction) {
            return response()->json(['error' => 'Transaction non trouvée.'], 404);
        }

        // Calculer les soldes nécessaires
        $soldeCommission = Portfeuille_transaction::sum('montant_commission');
        $soldeRestant = Portfeuille_transaction::sum('montant_restant');
        $soldeCommissionPartenaire = Portfeuille_transaction::sum('montant_commission_partenaire');
        $soldeCommissionAdmin = Portfeuille_transaction::sum('montant_commission_admin');
        $soldeCredit = Portfeuille_transaction::where('credit', true)->sum('amount');
        $soldeDebit = Portfeuille_transaction::where('debit', true)->sum('amount');

        // Mettre à jour les colonnes spécifiques
        $transaction->valeur_commission = 0;
        $transaction->montant_commission = 0;
        $transaction->montant_restant = $transaction->amount;
        $transaction->valeur_commission_partenaire = 0;
        $transaction->montant_commission_partenaire = 0;
        $transaction->valeur_commission_admin = 0;
        $transaction->montant_commission_admin = 0;
        $transaction->solde_commission = $soldeCommission;
        $transaction->solde_commission_partenaire = $soldeCommissionPartenaire;
        $transaction->new_solde_admin = $soldeCommissionAdmin;
        $transaction->solde_credit = $soldeCredit;
        $transaction->solde_debit = $soldeDebit;

        // Sauvegarder les modifications
        $transaction->save();
    }
}
