<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Housing;
use App\Models\housing_preference;
use App\Models\reduction;
use App\Models\promotion;
use App\Models\photo;
use App\Models\housing_price;
use App\Models\HousingCategory;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class HousingController extends Controller
{

 public function addHousing(Request $request)
 {
     $userId = Auth::id();
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
     $housing->cleaning_fees = $request->input('cleaning_fees');
     $housing->status ="Unverified";
     $housing->arrived_independently = $request->input('arrived_independently');
     $housing->is_instant_reservation = $request->input('is_instant_reservation');
     $housing->maximum_duration = $request->input('maximum_duration');
     $housing->minimum_duration = $request->input('minimum_duration');
     $housing->time_before_reservation = $request->input('time_before_reservation');
     $housing->cancelation_condition = $request->input('cancelation_condition');
     $housing->departure_instruction = $request->input('departure_instruction');
     $housing->user_id = $userId;
     $housing->save();
 
     if ($request->hasFile('photos')) {
         foreach ($request->file('photos') as $index => $photo) {
             $photoName = uniqid() . '.' . $photo->getClientOriginalExtension();
             $photoPath = $photo->move(public_path('image/photo_logement'), $photoName);
             $photoUrl = url('/image/photo_logement/' . $photoName);
             $type = $photo->getClientOriginalExtension();
             $photoModel = new photo();
             $photoModel->path = $photoUrl;
             $photoModel->extension = $type;
             if ($index == $request->input('profile_photo_id')) {
                 $photoModel->is_couverture = true;
             }
             $photoModel->housing_id = $housing->id;;
             $photoModel->save();
         }
     }
 
     foreach ($request->input('preferences') as $preference) {
         $housingPreference = new housing_preference();
         $housingPreference->housing_id = $housing->id;
         $housingPreference->preference_id = $preference;
         $housingPreference->save();
     }
 
     foreach ($request->input('night_number') as $index => $nightNumber) {
         $reduction = new reduction();
         $reduction->night_number = $nightNumber;
         $reduction->value = $request->input('value_night_number')[$index];
         $reduction->housing_id = $housing->id;
         $reduction->save();
     }
 
     foreach ($request->input('number_of_reservation') as $index => $numberOfReservation) {
         $promotion = new promotion();
         $promotion->number_of_reservation = $numberOfReservation;
         $promotion->value = $request->input('value_number_of_reservation')[$index];
         $promotion->housing_id = $housing->id;
         $promotion->save();
     }
 
     foreach ($request->input('category_id') as $index => $categoryId) {
         $housingCategory = new HousingCategory();
         $housingCategory->category_id = $categoryId;
         $housingCategory->number = $request->input('number_category')[$index];
         $housingCategory->housing_id = $housing->id;
         $housingCategory->save();
     }
 
     foreach ($request->input('price_with_cleaning_fees') as $index => $priceWithCleaningFees) {
         $housingPrice = new housing_price();
         $housingPrice->price_with_cleaning_fees = $priceWithCleaningFees;
         $housingPrice->price_without_cleaning_fees = $request->input('price_without_cleaning_fees')[$index];
         $housingPrice->type_stay_id = $request->input('type_stay_id')[$index];
         $housingPrice->housing_id = $housing->id;
         $housingPrice->save();
     }
     $notificationName="Félicitation!Vous venez d'ajouter un nouveau logement sur la plateforme.Le logement ne sera visible sur le site qu'aprés validation de l'administrateur";

     $notification = new Notification([
        'name' => $notificationName,
        'user_id' => $userId,
       ]);
     $adminUsers = User::where('is_admin', 1)->get();
            foreach ($adminUsers as $adminUser) {
                $notification = new Notification();
                $notification->user_id = $adminUser->id;
                $notification->name = "Un nouveau logement vient d'être ajouté sur le site par un hôte.";
                $notification->save();
            }
 
     return response()->json(['message' => 'Logement ajoute avec succes'], 201);
 }

 //Liste des logements en attente de validation par l'admin pour être présent sur le site
    public function indexHousingwithoutConfirmation()
   {
    $listings = Housing::with('user:id,firstname,lastname')
                        ->where('status', 'Unverified')
                        ->get();

    return response()->json(['message' => 'Liste des logements en attente de validation par l\'admin pour être présent sur le site','data' => $listings],200);
   }
 //Detail d'un  logement en attente de validation par l'admin pour être présent sur le site
   public function ShowHousingwithoutConfirmation($id)
 {
    $listing = Housing::with([
        'photos',
        'housing_preference.preference',
        'reductions',
        'promotions',
        'categories.category',
        'housingPrice.typeStay',
        'user',
        'housingType'
    ])->find($id);
    $data = [
        'id' => $listing->id,
        'housing_type_id' => $listing->housing_type_id,
        'housing_type_name' => $listing->housingType->name,
        'property_type_id' => $listing->property_type_id,
        'property_type_name' => $listing->propertyType->name,
        'user_id' => $listing->user_id,
        'name_housing' => $listing->name,
        'description' => $listing->description,
        'number_of_bed' => $listing->number_of_bed,
        'number_of_traveller' => $listing->number_of_traveller,
        'sit_geo_lat' => $listing->sit_geo_lat,
        'sit_geo_lng' => $listing->sit_geo_lng,
        'country' => $listing->country,
        'address' => $listing->address,
        'city' => $listing->city,
        'department' => $listing->department,
        'is_camera' => $listing->is_camera,
        'is_accepted_animal' => $listing->is_accepted_animal,
        'is_animal_exist' => $listing->is_animal_exist,
        'is_disponible' => $listing->is_disponible,
        'interior_regulation' => $listing->interior_regulation,
        'telephone' => $listing->telephone,
        'code_pays' => $listing->code_pays,
        'status' => $listing->status,
        'arrived_independently' => $listing->arrived_independently,
        'cleaning_fees' => $listing->cleaning_fees,
        'is_instant_reservation' => $listing->is_instant_reservation,
        'maximum_duration' => $listing->maximum_duration,
        'minimum_duration' => $listing->minimum_duration,
        'time_before_reservation' => $listing->time_before_reservation,
        'cancelation_condition' => $listing->cancelation_condition,
        'departure_instruction' => $listing->departure_instruction,
        'is_deleted' => $listing->is_deleted,
        'is_blocked' => $listing->is_blocked,

        'photos_logement' => $listing->photos->map(function ($photo) {
            return [
                'id' => $photo->id,
                'housing_id' => $photo->housing_id,
                'path' => $photo->path,
                'extension' => $photo->extension,
                'is_couverture' => $photo->is_couverture,
            ];
        }),
        'user' => [
            'id' => $listing->user->id,
            'lastname' => $listing->user->lastname,
            'firstname' => $listing->user->firstname,
            'password' => $listing->user->password,
            'telephone' => $listing->user->telephone,
            'code_pays' => $listing->user->code_pays,
            'email' => $listing->user->email,
            'country' => $listing->user->country,
            'file_profil' => $listing->user->file_profil,
            'city' => $listing->user->city,
            'address' => $listing->user->address,
            'sexe' => $listing->user->sexe,
            'postal_code' => $listing->user->postal_code,
            'is_admin' => $listing->user->is_admin,
            'is_traveller' => $listing->user->is_traveller,
            'is_hote' => $listing->user->is_hote,
        ],

        'housing_preference' => $listing->housing_preference->map(function ($preference) {
            return [
                'id' => $preference->id,
                'housing_id' => $preference->housing_id,
                'preference_id' => $preference->preference_id,
                'preference_name' => $preference->preference->name,
            ];
        }),

        'reductions' => $listing->reductions,

        'promotions' => $listing->promotions,

        'categories' => $listing->categories->map(function ($category) {
            return [
                'id_housing_category' => $category->id,
                'housing_id' => $category->housing_id,
                'category_id' => $category->category_id,
                'number' => $category->number,
                'category_name' => $category->category->name,
            ];
        }),

        'housing_price' => $listing->housingPrice->map(function ($price) {
            return [
                'id_housing_price' => $price->id,
                'price_with_cleaning_fees' => $price->price_with_cleaning_fees,
                'price_without_cleaning_fees' => $price->price_without_cleaning_fees,
                'housing_id' => $price->housing_id,
                'type_stay_id' => $price->type_stay_id,
                'type_stay_name' => $price->typeStay->name,
            ];
        })
    ];

    return response()->json(['data' => $data]);
}


 //Valider un logement donné en lui donnant le statut verified

 public function ValidateOneHousingwithoutConfirmation($id)
 {
     try {

         $housing = Housing::find($id);
 
         if (!$housing) {
             return response()->json(['message' => 'L\'ID du logement spécifié n\'existe pas'], 404);
         }
 
         $housing->status = 'verified';
         $housing->save();
 
         $notificationName = "Félicitations ! Votre logement a été validé et est maintenant visible sur la plateforme.";
         $notification = new Notification([
             'name' => $notificationName,
             'user_id' => $housing->user_id,
         ]);
         $notification->save();
 
         return response()->json(['message' => 'Statut du logement mis à jour avec succès'], 200);
     } catch (\Exception $e) {
         return response()->json(['message' => 'Erreur lors de la mise à jour du statut de l\'annonce'], 500);
     }
 }
 


 public function ValidateManyHousingwithoutConfirmation(Request $request)
 {
     try {
         $housingIds = $request->input('housing_ids');
         $existingHousingIds = Housing::whereIn('id', $housingIds)->pluck('id')->toArray();
 
         $missingIds = array_diff($housingIds, $existingHousingIds);
         if (!empty($missingIds)) {
             return response()->json(['message' => 'Certains IDs de logements n\'existent pas dans la base de données'], 404);
         }
 
         foreach ($housingIds as $id) {
             $housing = Housing::findOrFail($id);
             $housing->status = 'verified';
             $housing->save();
 
             $notificationName = "Félicitations ! Votre logement a été validé et est maintenant visible sur la plateforme.";
             $notification = new Notification([
                 'name' => $notificationName,
                 'user_id' => $housing->user_id,
             ]);
             $notification->save();
         }
 
         return response()->json(['message' => 'Statut des logements mis à jour avec succès'], 200);
     } catch (\Exception $e) {
         return response()->json(['message' => 'Erreur lors de la mise à jour du statut des logements'], 500);
     }
 }

 
 
}
