<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Housing;
use App\Models\housing_preference;
use App\Models\reduction;
use App\Models\promotion;
use App\Models\photo;
use App\Models\housing_price;
use Illuminate\Support\Facades\Validator;
class HousingController extends Controller
{
 
/**
 * @OA\Post(
 *     path="/api/add-housing",
 *     summary="Ajouter un logement",
 *     tags={"Logements"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données du logement à ajouter",
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="housing_type_id",
 *                     type="integer",
 *                     description="ID du type de logement"
 *                 ),
 *                 @OA\Property(
 *                     property="property_type_id",
 *                     type="integer",
 *                     description="ID du type de propriété"
 *                 ),
 *                 @OA\Property(
 *                     property="name",
 *                     type="string",
 *                     description="Nom du logement"
 *                 ),
 *                 @OA\Property(
 *                     property="description",
 *                     type="string",
 *                     description="Description du logement"
 *                 ),
 *                 @OA\Property(
 *                     property="number_of_bed",
 *                     type="integer",
 *                     description="Nombre de lits"
 *                 ),
 *                 @OA\Property(
 *                     property="number_of_traveller",
 *                     type="integer",
 *                     description="Nombre de voyageurs"
 *                 ),
 *                 @OA\Property(
 *                     property="sit_geo_lat",
 *                     type="string",
 *                     description="Latitude géographique"
 *                 ),
 *                 @OA\Property(
 *                     property="sit_geo_lng",
 *                     type="string",
 *                     description="Longitude géographique"
 *                 ),
 *                 @OA\Property(
 *                     property="country",
 *                     type="string",
 *                     description="Pays"
 *                 ),
 *                 @OA\Property(
 *                     property="address",
 *                     type="string",
 *                     description="Adresse"
 *                 ),
 *                 @OA\Property(
 *                     property="city",
 *                     type="string",
 *                     description="Ville"
 *                 ),
 *                 @OA\Property(
 *                     property="department",
 *                     type="string",
 *                     description="Département"
 *                 ),
 *                 @OA\Property(
 *                     property="is_camera",
 *                     type="boolean",
 *                     description="Indicateur de présence de caméra de surveillance"
 *                 ),
 *                 @OA\Property(
 *                     property="is_accepted_animal",
 *                     type="boolean",
 *                     description="Indicateur d'acceptation des animaux"
 *                 ),
 *                 @OA\Property(
 *                     property="is_animal_exist",
 *                     type="boolean",
 *                     description="Indicateur de présence d'animaux"
 *                 ),
 *                 @OA\Property(
 *                     property="is_disponible",
 *                     type="boolean",
 *                     description="Indicateur de disponibilité"
 *                 ),
 *                 @OA\Property(
 *                     property="interior_regulation",
 *                     type="string",
 *                     description="Règlement intérieur"
 *                 ),
 *                 @OA\Property(
 *                     property="telephone",
 *                     type="string",
 *                     description="Numéro de téléphone"
 *                 ),
 *                 @OA\Property(
 *                     property="code_pays",
 *                     type="string",
 *                     description="Code pays"
 *                 ),
 *                 @OA\Property(
 *                     property="status",
 *                     type="string",
 *                     description="Statut"
 *                 ),
 *                 @OA\Property(
 *                     property="arrived_independently",
 *                     type="boolean",
 *                     description="Indicateur d'arrivée indépendante"
 *                 ),
 *                 @OA\Property(
 *                     property="icone",
 *                     type="string",
 *                     description="Icône"
 *                 ),
 *                 @OA\Property(
 *                     property="is_instant_reservation",
 *                     type="boolean",
 *                     description="Indicateur de réservation instantanée"
 *                 ),
 *                 @OA\Property(
 *                     property="maximum_duration",
 *                     type="integer",
 *                     description="Durée maximale"
 *                 ),
 *                 @OA\Property(
 *                     property="minimum_duration",
 *                     type="integer",
 *                     description="Durée minimale"
 *                 ),
 *                 @OA\Property(
 *                     property="time_before_reservation",
 *                     type="integer",
 *                     description="Temps avant réservation"
 *                 ),
 *                 @OA\Property(
 *                     property="cancelation_condition",
 *                     type="string",
 *                     description="Condition d'annulation"
 *                 ),
 *                 @OA\Property(
 *                     property="departure_condition",
 *                     type="string",
 *                     description="Condition de départ"
 *                 ),
 *                 @OA\Property(
 *                     property="photos",
 *                     type="array",
 *                     @OA\Items(
 *                         type="file",
 *                         format="binary",
 *                         description="Photos du logement"
 *                     )
 *                 ),
 *                 @OA\Property(
 *                     property="profile_photo_id",
 *                     type="integer",
 *                     description="ID de la photo de profil du logement"
 *                 ),
 *                 @OA\Property(
 *                     property="preferences",
 *                     type="array",
 *                     @OA\Items(type="integer"),
 *                     description="Liste des préférences du logement"
 *                 ),
 *                 @OA\Property(
 *                     property="reductions",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(
 *                             property="night_number",
 *                             type="integer",
 *                             description="Nombre de nuitées"
 *                         ),
 *                         @OA\Property(
 *                             property="value",
 *                             type="integer",
 *                             description="Valeur de la réduction"
 *                         )
 *                     ),
 *                     description="Liste des réductions"
 *                 ),
 *                 @OA\Property(
 *                     property="promotions",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(
 *                             property="number_of_reservation",
 *                             type="integer",
 *                             description="Nombre de réservations"
 *                         ),
 *                         @OA\Property(
 *                             property="value",
 *                             type="integer",
 *                             description="Valeur de la promotion"
 *                         )
 *                     ),
 *                     description="Liste des promotions"
 *                 ),
 *                 @OA\Property(
 *                     property="categories",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(
 *                             property="category_id",
 *                             type="integer",
 *                             description="ID de la catégorie"
 *                         ),
 *                         @OA\Property(
 *                             property="number",
 *                             type="integer",
 *                             description="Nombre"
 *                         )
 *                     ),
 *                     description="Liste des catégories"
 *                 ),
 *                 @OA\Property(
 *                     property="prices",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(
 *                             property="price_with_cleaning_fees",
 *                             type="integer",
 *                             description="Prix avec frais de nettoyage"
 *                         ),
 *                         @OA\Property(
 *                             property="price_without_cleaning_fees",
 *                             type="integer",
 *                             description="Prix sans frais de nettoyage"
 *                         ),
 *                         @OA\Property(
 *                             property="type_stay_id",
 *                             type="integer",
 *                             description="ID du type de séjour"
 *                         )
 *                     ),
 *                     description="Liste des prix"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Logement ajouté avec succès"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Requête invalide"
 *     )
 * )
 */

 public function addHousing(Request $request)
{
    // Création du logement
    $housing = new Housing();
    $housing->housing_type_id = $request->input('housing_type_id');
    $housing->property_type_id = $request->input('property_type_id');
    $housing->name = $request->input('name');
    $housing->description = $request->input('description');
    $housing->number_of_bed = $request->input('number_of_bed');
    $housing->number_of_traveller = $request->input('number_of_traveller');
    $housing->sit_geo_lat = $request->input('sit_geo_lat');
    $housing->sit_geo_lng = $request->input('sit_geo_lng');
    $housing->country = $request->input('country');
    $housing->address = $request->input('address');
    $housing->city = $request->input('city');
    $housing->department = $request->input('department');
    $housing->is_camera = $request->input('is_camera');
    $housing->is_accepted_animal = $request->input('is_accepted_animal');
    $housing->is_animal_exist = $request->input('is_animal_exist');
    $housing->is_disponible = $request->input('is_disponible');
    $housing->interior_regulation = $request->input('interior_regulation');
    $housing->telephone = $request->input('telephone');
    $housing->code_pays = $request->input('code_pays');
    $housing->status ="Unverified";
    $housing->arrived_independently = $request->input('arrived_independently');
    $housing->icone = $request->input('icone');
    $housing->is_instant_reservation = $request->input('is_instant_reservation');
    $housing->maximum_duration = $request->input('maximum_duration');
    $housing->minimum_duration = $request->input('minimum_duration');
    $housing->time_before_reservation = $request->input('time_before_reservation');
    $housing->cancelation_condition = $request->input('cancelation_condition');
    $housing->departure_instruction = $request->input('departure_condition');
    $housing->user_id = 2;
    $housing->save();

    if ($request->hasFile('photos')) {
        foreach ($request->file('photos') as $index => $photo) {
            $photoName = uniqid() . '.' . $photo->getClientOriginalExtension();
            $photoPath = $photo->move(public_path('image/photo_logement'), $photoName);
            $photoUrl = url('/image/photo_logement/' . $photoName);
            $type = "jpg";
            $photoModel = new photo();
            $photoModel->path = $photoUrl;
            $photoModel->extension = $type;
            if ($index === $requestData['profile_photo_id']) {
                $photoModel->is_couverture = true;
            }

            $photoModel->save();
        }
    }
    foreach ($request->input('preferences') as $preference) {
        $housingPreference = new HousingPreference();
        $housingPreference->housing_id = $housing->id;
        $housingPreference->preference_id = $preference;
        $housingPreference->save();
    }

    foreach ($request->input('reductions') as $reductionData) {
        $reduction = new Reduction();
        $reduction->night_number = $reductionData['night_number'];
        $reduction->value = $reductionData['value'];
        $reduction->housing_id = $housing->id;
        $reduction->save();
    }

    // Enregistrement des promotions
    foreach ($request->input('promotions') as $promotionData) {
        $promotion = new Promotion();
        $promotion->number_of_reservation = $promotionData['number_of_reservation'];
        $promotion->value = $promotionData['value'];
        $promotion->housing_id = $housing->id;
        $promotion->save();
    }

   
    // Enregistrement des catégories
    foreach ($request->input('categories') as $categoryData) {
        $housingCategory = new HousingCategory();
        $housingCategory->category_id = $categoryData['category_id'];
        $housingCategory->number = $categoryData['number'];
        $housingCategory->housing_id = $housing->id;
        $housingCategory->save();
    }

    // Enregistrement des prix
    foreach ($request->input('prices') as $priceData) {
        $housingPrice = new HousingPrice();
        $housingPrice->price_with_cleaning_fees = $priceData['price_with_cleaning_fees'];
        $housingPrice->price_without_cleaning_fees = $priceData['price_without_cleaning_fees'];
        $housingPrice->type_stay_id = $priceData['type_stay_id'];
        $housingPrice->housing_id = $housing->id;
        $housingPrice->save();
    }

    return response()->json(['message' => 'Logement ajouté avec succès'], 201);
}

 
 
}
