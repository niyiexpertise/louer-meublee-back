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
     $housing->is_disponible = 1;
     $housing->interior_regulation = $request->input('interior_regulation');
     $housing->telephone = $request->input('telephone');
     $housing->code_pays = $request->input('code_pays');
     $housing->status ="Unverified";
     $housing->arrived_independently = $request->input('arrived_independently');
     $housing->is_instant_reservation = $request->input('is_instant_reservation');
     $housing->maximum_duration = $request->input('maximum_duration');
     $housing->minimum_duration = $request->input('minimum_duration');
     $housing->time_before_reservation = $request->input('time_before_reservation');
     $housing->cancelation_condition = $request->input('cancelation_condition');
     $housing->departure_instruction = $request->input('departure_instruction');
     $housing->user_id = $userId;
     $housing->surface = $request->input('surface');
     $housing->price = $request->input('price');
     $housing->is_updated=0;
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
     if ($request->has('preferences')) {
        foreach ($request->input('preferences') as $preference) {
            $housingPreference = new housing_preference();
            $housingPreference->housing_id = $housing->id;
            $housingPreference->preference_id = $preference;
            $housingPreference->is_verified = true;
            $housingPreference->save();
     }
    }
     if ($request->has('Hotecharges')) {
        foreach ($request->input('Hotecharges') as $charge) {
            $housingCharge = new Housing_charge();
            $housingCharge->housing_id = $housing->id;
            $housingCharge->charge_id = $charge;
            $housingCharge->is_mycharge= true;
            $housingCharge->save();
        }
     }
     if ($request->has('Travelercharges')) {
        foreach ($request->input('Travelercharges') as $charge) {
            $housingCharge = new Housing_charge();
            $housingCharge->housing_id = $housing->id;
            $housingCharge->charge_id = $charge;
            $housingCharge->is_mycharge= false;
            $housingCharge->save();
        }
     }
     if ($request->has('night_number')) {
        foreach ($request->input('night_number') as $index => $nightNumber) {
            $reduction = new reduction();
            $reduction->night_number = $nightNumber;
            $reduction->value = $request->input('value_night_number')[$index];
            $reduction->housing_id = $housing->id;
            $reduction->save();
        }
    }    
 
     foreach ($request->input('number_of_reservation') as $index => $numberOfReservation) {
         $promotion = new promotion();
         $promotion->number_of_reservation = $numberOfReservation;
         $promotion->value = $request->input('value_number_of_reservation')[$index];
         $promotion->housing_id = $housing->id;
         $promotion->save();
     }
     /**foreach ($request->input('price_with_cleaning_fees') as $index => $priceWithCleaningFees) {
        $housingPrice = new housing_price();
        $housingPrice->price_with_cleaning_fees = $priceWithCleaningFees;
        $housingPrice->price_without_cleaning_fees = $request->input('price_without_cleaning_fees')[$index];
        $housingPrice->type_stay_id = $request->input('type_stay_id')[$index];
        $housingPrice->housing_id = $housing->id;
        $housingPrice->save();
      }
       */
    if ($request->has('equipment_housing')) {
        foreach ($request->equipment_housing as $equipmentId) {
            $equipment = Equipment::find($equipmentId);   
            if ($equipment) {
                $housingEquipment = new Housing_equipment();
                $housingEquipment->equipment_id = $equipmentId;
                $housingEquipment->housing_id = $housing->id;
                $housingEquipment->is_verified = true;
                $housingEquipment->save();
            } else {
               
            }
        }
    }

    if ($request->has('new_equipment') && $request->has('new_equipment_category')) {
        $newEquipments = $request->input('new_equipment');
        $newEquipmentCategories = $request->input('new_equipment_category');

        foreach ($newEquipments as $index => $newEquipment) {
            $equipment = new Equipment();
            $equipment->name = $newEquipment;
            $equipment->is_verified=false;
            $equipment->save();


            $equipmentCategory = new Equipment_category();
            $equipmentCategory->equipment_id = $equipment->id;
            $equipmentCategory->category_id = $newEquipmentCategories[$index];
            $equipmentCategory->save();

            $housingEquipment = new Housing_equipment();
            $housingEquipment->equipment_id = $equipment->id;
            $housingEquipment->housing_id = $housing->id;
            $housingEquipment->is_verified = false;
            $housingEquipment->save();
        }
    }
    if ($request->has('category_id')) {
    foreach ($request->input('category_id') as $index => $categoryId) {
        $housingCategoryId = $housing->id;
        $photoCategoryKey = 'photo_categories' . $categoryId;
        $photoFiles = $request->file($photoCategoryKey);
        foreach ($photoFiles as $fileId) {
            $photoModel = new File();
            $photoName = uniqid() . '.' . $fileId->getClientOriginalExtension();
            $photoPath = $fileId->move(public_path('image/photo_category'), $photoName);
            $photoUrl = url('/image/photo_category/' . $photoName);
        
            $photoModel->path = $photoUrl;
            $photoModel->save();
            $housingCategoryFile = new Housing_category_file();
            $housingCategoryFile->housing_id = $housingCategoryId;
            $housingCategoryFile->category_id = $categoryId;
            $housingCategoryFile->file_id = $photoModel->id;
            $housingCategoryFile->number = $request->input('number_category')[$index];
            $housingCategoryFile->is_verified = true;
            $housingCategoryFile->save();
        }
      }
    }
    if($request->has('new_categories')&& $request->has('new_categories_numbers')) {
    $newCategories = $request->input('new_categories') ?? [];
    $newCategoriesNumbers = $request->input('new_categories_numbers') ?? [];

    foreach ($newCategories as $index => $newCategory) {
        $categoryName = $newCategory;
        $categoryNumber = $newCategoriesNumbers[$index];
        $photoCategoryKey = 'new_category_photos_' . $categoryName;
        $categoryPhotos = $request->file($photoCategoryKey) ?? [];

        $category = new Category();
        $category->name = $categoryName;
        $category->is_verified = false;
        $category->save();

        foreach ($categoryPhotos as $photoFile) {
            $photoName = uniqid() . '.' . $photoFile->getClientOriginalExtension();
            $photoPath = $photoFile->move(public_path('image/photo_category'), $photoName);
            $photoUrl = url('/image/photo_category/' . $photoName);

            $photo = new File();
            $photo->path = $photoUrl;
            $photo->save();

            $housingCategoryFile = new Housing_category_file();
            $housingCategoryFile->housing_id = $housing->id;
            $housingCategoryFile->category_id = $category->id;
            $housingCategoryFile->file_id = $photo->id;
            $housingCategoryFile->number = $categoryNumber;
            $housingCategoryFile->is_verified = false;
            $housingCategoryFile->save();
        }
      }
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
 /**
 * @OA\Get(
 *   path="/api/logement/index/ListeDesPhotosLogementAcceuil/{id}",
 *   tags={"Housing"},
 *   summary="Liste des photos d'un logement",
 *   description="Récupère la liste des photos associées à un logement spécifié par son ID.",
 * security={{"bearerAuth":{}}},
 *   @OA\Parameter(
 *     name="id",
 *     in="path",
 *     required=true,
 *     description="ID du logement",
 *     @OA\Schema(type="integer")
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Liste des photos du logement récupérée avec succès",
 *     @OA\JsonContent(
 *       @OA\Property(property="data", type="object",
 *         @OA\Property(property="id_housing", type="integer", example="1"),
 *         @OA\Property(property="photos_logement", type="array",
 *           @OA\Items(
 *             @OA\Property(property="id_photo", type="integer", example="1"),
 *             @OA\Property(property="path", type="string", example="http://exemple.com/photos/1"),
 *             @OA\Property(property="extension", type="string", example="jpg"),
 *             @OA\Property(property="is_couverture", type="boolean", example=true)
 *           )
 *         ),
 *         @OA\Property(property="categories", type="array",
 *           @OA\Items(
 *             @OA\Property(property="category_id", type="integer", example="1"),
 *             @OA\Property(property="category_name", type="string", example="Catégorie"),
 *             @OA\Property(property="photos_category", type="array",
 *               @OA\Items(
 *                 @OA\Property(property="file_id", type="integer", example="1"),
 *                 @OA\Property(property="path", type="string", example="http://exemple.com/photos/1")
 *               )
 *             )
 *           )
 *         )
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Le logement spécifié n'existe pas",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Le logement spécifié n'existe pas")
 *     )
 *   )
 * )
 */
public function ListeDesPhotosLogementAcceuil($id)
{
    $listing = Housing::with([
        'photos',
        'housingCategoryFiles.category',
    ])->find($id);

    $data = [
        'id_housing' => $listing->id,
        'photos_logement' => $listing->photos->map(function ($photo) {
            return [
                'id_photo' => $photo->id,
                'path' => $photo->path,
                'extension' => $photo->extension,
                'is_couverture' => $photo->is_couverture,
            ];
        }),
        'categories' => $listing->housingCategoryFiles->where('is_verified', 1)->groupBy('category.name')->map(function ($categoryFiles, $categoryName) {
            return [
                'category_id' => $categoryFiles->first()->category_id,
                'category_name' => $categoryFiles->first()->category->name,
                'number' => $categoryFiles->first()->number,
                'photos_category' => $categoryFiles->map(function ($categoryFile) {
                    return [
                        'file_id' => $categoryFile->file->id,
                        'path' => $categoryFile->file->path,
                    ];
                }),
            ];
        })->values(),
    ];

    return response()->json(['data' => $data],200);
}

 
     

 
 
 /**
 * @OA\Get(
 *   path="/api/logement/index/ListeDesLogementsAcceuil",
 *   tags={"Housing"},
 *   summary="Liste des logements pour l'accueil",
 *   description="Récupère la liste des logements disponibles et vérifiés pour l'accueil.c'est cette route qui vous envoit les logements à afficher sur le site ",
 * security={{"bearerAuth":{}}},
 *   @OA\Response(
 *     response=200,
 *     description="Liste des logements récupérée avec succès",
 *     @OA\JsonContent(
 *       @OA\Property(property="data", type="array",
 *         @OA\Items(
 *           @OA\Property(property="id_housing", type="integer", example="1"),
 *           @OA\Property(property="housing_type_id", type="integer", example="1"),
 *           @OA\Property(property="housing_type_name", type="string", example="Appartement"),
 *           @OA\Property(property="property_type_id", type="integer", example="1"),
 *           @OA\Property(property="property_type_name", type="string", example="Location"),
 *           @OA\Property(property="user_id", type="integer", example="1"),
 *           @OA\Property(property="name_housing", type="string", example="Bel appartement"),
 *           @OA\Property(property="description", type="string", example="Description de l'appartement"),
 *           @OA\Property(property="number_of_bed", type="integer", example="2"),
 *           @OA\Property(property="number_of_traveller", type="integer", example="4"),
 *           @OA\Property(property="sit_geo_lat", type="string", example="48.858844"),
 *           @OA\Property(property="sit_geo_lng", type="string", example="2.294351"),
 *           @OA\Property(property="country", type="string", example="France"),
 *           @OA\Property(property="address", type="string", example="10 Rue de Rivoli"),
 *           @OA\Property(property="city", type="string", example="Paris"),
 *           @OA\Property(property="department", type="string", example="Île-de-France"),
 *           @OA\Property(property="is_camera", type="boolean", example=false),
 *           @OA\Property(property="is_accepted_animal", type="boolean", example=true),
 *           @OA\Property(property="is_animal_exist", type="boolean", example=true),
 *           @OA\Property(property="is_disponible", type="boolean", example=true),
 *           @OA\Property(property="interior_regulation", type="string", example="Regulations..."),
 *           @OA\Property(property="telephone", type="string", example="0123456789"),
 *           @OA\Property(property="code_pays", type="string", example="FR"),
 *           @OA\Property(property="status", type="string", example="verified"),
 *           @OA\Property(property="arrived_independently", type="boolean", example=true),
 *           @OA\Property(property="is_instant_reservation", type="boolean", example=true),
 *           @OA\Property(property="maximum_duration", type="integer", example="30"),
 *           @OA\Property(property="minimum_duration", type="integer", example="1"),
 *           @OA\Property(property="time_before_reservation", type="integer", example="2"),
 *           @OA\Property(property="cancelation_condition", type="string", example="Conditions..."),
 *           @OA\Property(property="departure_instruction", type="string", example="Instructions..."),
 *           @OA\Property(property="is_deleted", type="boolean", example=false),
 *           @OA\Property(property="is_blocked", type="boolean", example=false),
 *           @OA\Property(property="photos_logement", type="array",
 *             @OA\Items(
 *               @OA\Property(property="id_photo", type="integer", example="1"),
 *               @OA\Property(property="path", type="string", example="http://exemple.com/photos/1"),
 *               @OA\Property(property="extension", type="string", example="jpg"),
 *               @OA\Property(property="is_couverture", type="boolean", example=true)
 *             )
 *           ),
 *           @OA\Property(property="user", type="object",
 *             @OA\Property(property="id", type="integer", example="1"),
 *             @OA\Property(property="lastname", type="string", example="Doe"),
 *             @OA\Property(property="firstname", type="string", example="John"),
 *             @OA\Property(property="telephone", type="string", example="0123456789"),
 *             @OA\Property(property="code_pays", type="string", example="FR"),
 *             @OA\Property(property="email", type="string", example="john@example.com"),
 *             @OA\Property(property="country", type="string", example="France"),
 *             @OA\Property(property="file_profil", type="string", example="http://exemple.com/photo_profil/1"),
 *             @OA\Property(property="city", type="string", example="Paris"),
 *             @OA\Property(property="address", type="string", example="10 Rue de Rivoli"),
 *             @OA\Property(property="sexe", type="string", example="male"),
 *             @OA\Property(property="postal_code", type="string", example="75001"),
 *             @OA\Property(property="is_admin", type="boolean", example=false),
 *             @OA\Property(property="is_traveller", type="boolean", example=true),
 *             @OA\Property(property="is_hote", type="boolean", example=false)
 *           )
 *         )
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Aucun logement disponible trouvé",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Aucun logement disponible trouvé")
 *     )
 *   )
 * )
 */

 public function ListeDesLogementsAcceuil()
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->get();
        $data = $this->formatListingsData($listings);

        return response()->json(['data' => $data], 200);
    }

/**
 * @OA\Get(
 *     path="/api/logement/ShowDetailLogementAcceuil/{housing_id}",
 *     tags={"Housing"},
 *     summary="Liste des détails possibles d'un logement donné côté acceuil",
 *     description="Récupère les détails d'un logement spécifié par son ID, y compris les informations sur le propriétaire, les photos, les équipements, les préférences, les réductions, les promotions, les catégories et les prix.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="housing_id",
 *         in="path",
 *         description="ID du logement à afficher",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Détails du logement récupérés avec succès",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Le logement spécifié n'existe pas",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Le logement spécifié n'existe pas")
 *         )
 *     )
 * )
 */

    public function ShowDetailLogementAcceuil($id)
 {
     $listing = Housing::with([
         'photos',
         'housing_preference.preference',
         'reductions',
         'promotions',
         'housingCategoryFiles.category',
         'housingPrice.typeStay',
         'user',
         'housingType'
     ])->find($id);
     $hoteCharge_id = [];
     $travelerCharge_id = [];
     $housingCharges = Housing_charge::where('housing_id', $id)->get();
     if ($housingCharges->isEmpty()) {
         return response()->json(['message' => 'Aucune charge associé à ce logement'], 404);
     }
     
     foreach ($housingCharges as $housingCharge) {
         $charge = Charge::find($housingCharge->charge_id);
         if ($housingCharge->is_mycharge == true) {
             $hoteCharge_id[] = [
                 'id_housing_charge' => $housingCharge->id,
                 'housing_id' => $housingCharge->housing_id,
                 'id_charge' => $charge->id,
                 'charge_name' => $charge->name,
                 'is_mycharge' => $housingCharge->is_mycharge
             ];
         }else{
             $travelerCharge_id[] = [
                 'id_housing_charge' => $housingCharge->id,
                 'housing_id' => $housingCharge->housing_id,
                 'id_charge' => $charge->id,
                 'charge_name' => $charge->name,
                 'is_mycharge' => $housingCharge->is_mycharge
             ];
         }
     }
     $data = [
         'id_housing' => $listing->id,
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
         'surface' => $listing->surface,
         'price' => $listing->price,
         'status' => $listing->status,
         'arrived_independently' => $listing->arrived_independently,
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
                 'id_photo' => $photo->id,
                 'path' => $photo->path,
                 'extension' => $photo->extension,
                 'is_couverture' => $photo->is_couverture,
             ];
         }),
         'user' => [
             'id' => $listing->user->id,
             'lastname' => $listing->user->lastname,
             'firstname' => $listing->user->firstname,
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
 
         'housing_preference' => $listing->housing_preference->filter(function ($preference) {
            return $preference->is_verified;
            })->map(function ($housingpreference) {
            return [
                 'id' => $housingpreference->id,
                 'preference_id' => $housingpreference->preference_id,
                 'preference_name' => $housingpreference->preference->name,
            ];
          }),
 
         'reductions' => $listing->reductions,
 
         'promotions' => $listing->promotions,
 
         'categories' => $listing->housingCategoryFiles->where('is_verified', 1)->groupBy('category.name')->map(function ($categoryFiles, $categoryName) {
            return [
                'category_id' => $categoryFiles->first()->category_id,
                'category_name' => $categoryFiles->first()->category->name,
                'number' => $categoryFiles->first()->number,
                'photos_category' => $categoryFiles->map(function ($categoryFile) {
                    return [
                        'file_id' => $categoryFile->file->id,
                        'path' => $categoryFile->file->path,
                    ];
                }),
            ];
        })->values(),
 
         'equipments' => $listing->housingEquipments->filter(function ($equipment) {
            return  $equipment->is_verified;
            })->map(function ($housingEquipment) {
            return [
                'equipment_id' => $housingEquipment->equipment_id,
                'name' => $housingEquipment->equipment->name,
            ];
        }),
        'charges' => [
            'charge_hote' => $hoteCharge_id,
            'charge_traveler' => $travelerCharge_id
        ]
        
     ];
 
     return response()->json(['data' => $data]);
 }

/**
 * @OA\Get(
 *     path="/api/logement/filterby/typehousing/{housingTypeId}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par type de logement",
 *     description="Récupère la liste des logements filtrée par le type de logement spécifié.",
 *     operationId="listingsFilteredByHousingType",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="housingTypeId",
 *         in="path",
 *         description="ID du type de logement",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le type spécifié"
 *     )
 * )
 */

 public function  ListeDesLogementsAcceuilFilterByTypehousing($id)
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('housing_type_id', $id)
        ->where('is_updated', 0)
        ->get();
    
        $data = $this->formatListingsData($listings);

        return response()->json(['data' => $data],200);
    }

 /**
 * @OA\Get(
 *     path="/api/logement/filterby/typeproperty/{PropertyTypeId}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par type de proprieté",
 *     description="Récupère la liste des logements filtrée par le type de propriété spécifié.",
 *     operationId="listingsFilteredByPropertyType",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="PropertyTypeId",
 *         in="path",
 *         description="ID du type de propriété",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le type spécifié"
 *     )
 * )
 */
    public function  ListeDesLogementsAcceuilFilterByTypeproperty($id)
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('property_type_id', $id)
        ->get();

        $data = $this->formatListingsData($listings);

        return response()->json(['data' => $data],200);
    }
/**
 * @OA\Get(
 *     path="/api/logement/Disponible",
 *     tags={"Housing"},
 *     summary="Liste des logements disponibles",
 *     description="Récupère la liste des logements disponibles à la location.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements disponibles",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement disponible trouvé"
 *     )
 * )
 */

    public function  ListeDesLogementsDisponible()
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_disponible', 1)
        ->get();
    
        $data = $this->formatListingsData($listings);

        return response()->json(['data' => $data],200);
    }
/**
 * @OA\Get(
 *     path="/api/logement/NonDisponible",
 *     tags={"Housing"},
 *     summary="Liste des logements non disponibles",
 *     description="Récupère la liste des logements non disponibles .",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements non disponibles",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement non disponible trouvé"
 *     )
 * )
 */
    public function  ListeDesLogementsNonDisponible()
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_disponible', 0)
        ->get();
    
        $data = $this->formatListingsData($listings);
        
        return response()->json(['data' => $data],200);
    }


 
 /**
 * @OA\Get(
 *     path="/api/logement/filterby/country/{country}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par Pays",
 *     description="Récupère la liste des logements filtrée par le paysspécifié.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="country",
 *         in="path",
 *         description="Nom du pays",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée par pays",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le type spécifié"
 *     )
 * )
 */
    public function  ListeDesLogementsFilterByCountry($country)
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('country', $country)
        ->get();
    
        $data = $this->formatListingsData($listings);
        return response()->json(['data' => $data],200);
      }

/**
 * @OA\Get(
 *     path="/api/logement/filterby/preference/{preference_id}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par preference",
 *     description="Récupère la liste des logements filtrée par le id preference spécifié.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="preference_id",
 *         in="path",
 *         description="Id du preference",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée par préférence",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le type spécifié"
 *     )
 * )
 */
public function  ListeDesLogementsAcceuilFilterByPreference($preferenceId)
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->whereHas('housing_preference', function ($query) use ($preferenceId) {
            $query->where('preference_id', $preferenceId);
        })
        ->get();
    
        $data = $this->formatListingsData($listings);
        return response()->json(['data' => $data],200);
    }
/**
 * @OA\Get(
 *     path="/api/logement/filterby/city/{city}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par ville",
 *     description="Récupère la liste des logements filtrée par villespécifié.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="city",
 *         in="path",
 *         description="Nom de la ville",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée par ville",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le type spécifié"
 *     )
 * )
 */
 public function  ListeDesLogementsFilterByCity($city)
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('city', $city)
        ->get();
    
        $data = $this->formatListingsData($listings);
        return response()->json(['data' => $data],200);
 }

/**
 * @OA\Get(
 *     path="/api/logement/filterby/departement/{departement}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par departement",
 *     description="Récupère la liste des logements filtrée par departement spécifié.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="departement",
 *         in="path",
 *         description="Nom du departement",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée par departement",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le type spécifié"
 *     )
 * )
 */
 public function  ListeDesLogementsFilterByDepartement($department)
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('department', $department)
        ->get();
        $data = $this->formatListingsData($listings);
        return response()->json(['data' => $data],200);
 }
/**
 * @OA\Get(
 *     path="/api/logement/filterby/nbtraveler/{nbtraveler}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par nbtraveler",
 *     description="Récupère la liste des logements filtrée par nbtraveler spécifié.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="nbtraveler",
 *         in="path",
 *         description="Nombre de voyageur",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée par nbtraveler",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le type spécifié"
 *     )
 * )
 */
 public function  ListeDesLogementsAcceuilFilterNbtravaller($nbtravaler)
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('number_of_traveller', $nbtravaler)
        ->get();
    
        $data = $this->formatListingsData($listings);

        return response()->json(['data' => $data],200);
    }

    /**
 * @OA\Get(
 *     path="/api/logement/filterby/nightpricemax/{price}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par prix de nuit(Maximum)",
 *     description="Récupère la liste des logements filtrée par un prix de nuit inférieur au montant spécifié.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="price",
 *         in="path",
 *         description="Prix de nuit maximum",
 *         required=true,
 *         @OA\Schema(
 *             type="number",
 *             format="float"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée par prix de nuit",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le prix spécifié"
 *     )
 * )
 */
public function getListingsByNightPriceMax($price)
{
    $listings = Housing::where('price', '<=', $price)
    ->where('status', 'verified')
    ->where('is_deleted', 0)
    ->where('is_blocked', 0)
    ->where('is_updated', 0)
    ->get();

    $data = $this->formatListingsData($listings);

    return response()->json(['data' => $data], 200);
}

   /**
 * @OA\Get(
 *     path="/api/logement/filterby/nightpricemin/{price}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par prix de nuit (Minimum)",
 *     description="Récupère la liste des logements filtrée par un prix de nuit supérieur au montant spécifié.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="price",
 *         in="path",
 *         description="Prix de nuit minimum",
 *         required=true,
 *         @OA\Schema(
 *             type="number",
 *             format="float"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrés par prix de nuit",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le prix spécifié"
 *     )
 * )
 */
public function getListingsByNightPriceMin($price)
{
    $listings = Housing::where('price', '>=', $price)
    ->where('status', 'verified')
    ->where('is_deleted', 0)
    ->where('is_blocked', 0)
    ->where('is_updated', 0)
    ->get();

    $data = $this->formatListingsData($listings);

    return response()->json(['data' => $data], 200);
}

 /**
 * @OA\Get(
 *     path="/api/logement/filterby/hote/{UserID}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par user",
 *     description="Récupère la liste des logements pour un User donné.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="UserID",
 *         in="path",
 *         description="Id de User Hote",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée par Id User Hote",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le type spécifié"
 *     )
 * )
 */
 public function ListeDesLogementsFilterByHote($userId)
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('user_id', $userId)
        ->get();
        $data = $this->formatListingsData($listings);
        return response()->json(['data' => $data,'nombre'=>$data->count()],200);
 }
 
/**
 * @OA\Put(
 *     path="/api/logement/update/sensible/{id}",
 *     tags={"Housing"},
 *     summary="Modifier les informations sensibles d'un logement",
 *     description="Permet de mettre à jour les informations sensibles d'un logement existant à partir de son ID",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID du logement à mettre à jour",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="interior_regulation",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="telephone",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="code_pays",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="arrived_independently",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="cancelation_condition",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="departure_instruction",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="surface",
 *                     type="number"
 *                 ),
 *                 @OA\Property(
 *                     property="price",
 *                     type="number"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Les informations du logement ont été mises à jour avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Les informations du logement ont été mises à jour avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Le logement à mettre à jour n'existe pas",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Le logement à mettre à jour n'existe pas")
 *         )
 *     )
 * )
 */


public function updateSensibleHousing(Request $request, $id)
{
    $userId = Auth::id();
    $housing = Housing::find($id);
    if (!$housing) {
        return response()->json(['message' => 'Le logement spécifié n\'existe pas'], 404);
    }

    $validatedData = $request->validate([
        'interior_regulation' => 'required',
        'telephone' => 'required',
        'code_pays' => 'required',
        'arrived_independently' => 'required',
        'cancelation_condition' => 'required',
        'departure_instruction' => 'required',
        'surface'=> 'required',
        'price'=> 'required',
    ]);

    $housing->update($validatedData);

    $housing->is_updated = true;
    $housing->save();

    return response()->json(['message' => 'Logement mis à jour avec succès'], 200);
}


/**
 * @OA\Put(
 *     path="/api/logement/update/insensible/{id}",
 *     tags={"Housing"},
 *     summary="Modifier les informations insensibles d'un logement",
 *     description="Permet de modifier les informations insensibles d'un logement existant à partir de son ID",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID du logement à mettre à jour",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="name",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="description",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="number_of_bed",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="number_of_traveller",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="is_camera",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="is_accepted_animal",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="is_animal_exist",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="is_instant_reservation",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="maximum_duration",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="minimum_duration",
 *                     type="integer"
 *                 ),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Les informations du logement ont été mises à jour avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Les informations du logement ont été mises à jour avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Le logement à mettre à jour n'existe pas",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Le logement à mettre à jour n'existe pas")
 *         )
 *     )
 * )
 */
public function updateInsensibleHousing(Request $request, $id)
{
    $userId = Auth::id();
    $housing = Housing::find($id);
    if (!$housing) {
        return response()->json(['message' => 'Le logement spécifié n\'existe pas'], 404);
    }

    $validatedData = $request->validate([
        'name' => 'required|string',
        'description' => 'required|string',
        'number_of_bed' => 'required|integer',
        'number_of_traveller' => 'required|integer',
        'is_camera' => 'required|integer',
        'is_accepted_animal' => 'required|integer',
        'is_animal_exist' => 'required|integer',
        'is_instant_reservation' => 'required|integer',
        'maximum_duration' => 'required|integer',
        'minimum_duration' => 'required|integer',
    ]);

    $housing->update($validatedData);
    $housing->is_updated = false;
    $housing->save();

    return response()->json(['message' => 'Logement mis à jour avec succès'], 200);
}




private function formatListingsData($listings)
    {
        return $listings->map(function ($listing) {
            return [
                'id_housing' => $listing->id,
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
                'surface' => $listing->surface,
                'price' => $listing->price,
                'status' => $listing->status,
                'arrived_independently' => $listing->arrived_independently,
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
                        'id_photo' => $photo->id,
                        'path' => $photo->path,
                        'extension' => $photo->extension,
                        'is_couverture' => $photo->is_couverture,
                    ];
                }),
                'user' => [
                    'id' => $listing->user->id,
                    'lastname' => $listing->user->lastname,
                    'firstname' => $listing->user->firstname,
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
                'categories' => $listing->housingCategoryFiles->where('is_verified', 1)->groupBy('category.name')->map(function ($categoryFiles, $categoryName) {
                    return [
                        'category_id' => $categoryFiles->first()->category_id,
                        'category_name' => $categoryFiles->first()->category->name,
                        'number' => $categoryFiles->first()->number,
                        'photos_category' => $categoryFiles->map(function ($categoryFile) {
                            return [
                                'file_id' => $categoryFile->file->id,
                                'path' => $categoryFile->file->path,
                            ];
                        }),
                    ];
                })->values(),
                
            ];
        });
    }


}