<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
use App\Models\Category;
use App\Models\Preference;
use App\Models\HousingType;
use App\Models\PropertyType;
use App\Models\Criteria;
use App\Models\Language;
use Exception;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class AdminHousingController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/logement/withoutvalidate",
 *     tags={"Housing"},
 *     summary="Liste des logements en attente d'être verifiés par l'administrateur",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Succès de la requête",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *         )
 *     )
 * )
 */
    public function indexHousingForValidationForadmin()
    {
        $listings = Housing::where('status', 'Unverified')->get();
    
        $data = $listings->map(function ($listing) {
            return [
                'id_housing' => $listing->id,
                'housing_type_name' => $listing->housingType->name,
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
    
            ];
        });
        return response()->json(['data' => $data]);
    }
/**
 * @OA\Get(
 *     path="/api/logement/withoutupdate",
 *     tags={"Housing"},
 *     summary="Liste des logements en attente de mise à jour par l'administrateur",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Succès de la requête",
 *         @OA\JsonContent(
 *             type="object",
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *         )
 *     )
 * )
 */
    public function indexHousingForUpdateForadmin()
    {
        $listings = Housing::where('is_updated', '1')->get();
    
        $data = $listings->map(function ($listing) {
            return [
                'id_housing' => $listing->id,
                'housing_type_name' => $listing->housingType->name,
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
    
            ];
        });
        return response()->json(['data' => $data]);
    }

    
/**
 * @OA\Get(
 *     path="/api/logement/withoutvalidation/show/{housing_id}",
 *     tags={"Housing"},
 *     summary="Liste des détails possibles d'un logement donné côté Admin",
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
public function showHousingDetailForValidationForadmin($id)
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
        'price' => $listing->price,
        'status' => $listing->status,
        'surface' => $listing->surface,
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

        'housing_preference' => [
            'nouveau' => $listing->housing_preference->filter(function ($preference) {
                return !$preference->preference->is_verified;
            })->map(function ($preference) {
                return [
                    'id' => $preference->id,
                    'preference_id' => $preference->preference_id,
                    'preference_name' => $preference->preference->name,
                    'valide' =>$preference->is_verified
                ];
            }),
            'defaut' => $listing->housing_preference->filter(function ($preference) {
                return $preference->preference->is_verified;
            })->map(function ($preference) {
                return [
                    'id' => $preference->id,
                    'preference_id' => $preference->preference_id,
                    'preference_name' => $preference->preference->name,
                    'valide' =>$preference->is_verified
                ];
            }),
        ],

        'reductions' => $listing->reductions,

        'promotions' => $listing->promotions,

        'categories' => [
            'nouveau' => $listing->housingCategoryFiles->filter(function ($categoryFile) {
                return !$categoryFile->category->is_verified;
            })->groupBy('category.name')->map(function ($categoryFiles) {
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
                    'valide' => $categoryFiles->first()->is_verified,
                ];
            })->values()->toArray(),

            'defaut' => $listing->housingCategoryFiles->filter(function ($categoryFile) {
                return $categoryFile->category->is_verified;
            })->groupBy('category.name')->map(function ($categoryFiles) {
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
                    'valide' => $categoryFiles->first()->is_verified,
                ];
            })->values()->toArray(),
        ],

        'equipments' => [
            'nouveau' => $listing->housingEquipments->filter(function ($equipment) {
                return !$equipment->equipment->is_verified;
            })->map(function ($housingEquipment) {
                return [
                    'equipment_id' => $housingEquipment->equipment_id,
                    'name' => $housingEquipment->equipment->name,
                    'valide' => $housingEquipment->is_verified,
                ];
            })->values()->toArray(),

            'defaut' => $listing->housingEquipments->filter(function ($equipment) {
                return $equipment->equipment->is_verified;
            })->map(function ($housingEquipment) {
                return [
                    'equipment_id' => $housingEquipment->equipment_id,
                    'name' => $housingEquipment->equipment->name,
                    'valide' => $housingEquipment->is_verified,
                ];
            })->values()->toArray(),
        ],
    ];

    return response()->json(['data' => $data]);
}


/**
 * @OA\Post(
 *     path="/api/logement/validate/one/{id}",
 *     tags={"Housing"},
 *     summary="Valider un logement spécifique en attente de verification",
 *     description="Valide un logement spécifié par son ID et met à jour son statut en 'verified'. Envoie également une notification à l'utilisateur propriétaire du logement.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID du logement à valider",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Succès - Statut du logement mis à jour avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Statut du logement mis à jour avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Non trouvé - L'ID du logement spécifié n'existe pas",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="L'ID du logement spécifié n'existe pas")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Erreur lors de la mise à jour du statut de l'annonce")
 *         )
 *     )
 * )
 */
 public function ValidateOneHousing($id)
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
/**
 * @OA\Post(
 *     path="/api/logement/validate/many/",
 *     tags={"Housing"},
 *     summary="Valider plusieurs logements",
 *     description="Valide plusieurs logements spécifiés par leurs IDs et met à jour leur statut en 'verified'. Envoie également une notification à chaque utilisateur propriétaire des logements validés.",
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"housing_ids"},
 *                 @OA\Property(
 *                     property="housing_ids",
 *                     type="array",
 *                     @OA\Items(
 *                         type="integer",
 *                         format="int64",
 *                         example=1
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Succès - Statut des logements mis à jour avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Statut des logements mis à jour avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Non trouvé - Certains IDs de logements n'existent pas dans la base de données",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Certains IDs de logements n'existent pas dans la base de données")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Erreur lors de la mise à jour du statut des logements")
 *         )
 *     )
 * )
 */
 public function ValidateManyHousing(Request $request)
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

 /**
 * @OA\Get(
 *     path="/api/logements/index/ListeDesLogementsValideBloque",
 *     tags={"Housing"},
 *     summary="Liste des logements déjà verifiés mais bloqués",
 *     description="Récupère la liste des logements qui sont déjà verifiés mais bloqués.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Succès - Liste des logements déjà verifiés mais bloqués",
 *         @OA\JsonContent(
 *           
 *         )
 *     )
 * )
 */

 public function ListeDesLogementsValideBloque()
 {
     $listings = Housing::where('status', 'verified')
     ->where('is_deleted', 0)
     ->where('is_blocked', 1)
     ->get();
 
     $data = $listings->map(function ($listing) {
         return [
             'id_housing' => $listing->id,
             'housing_type_name' => $listing->housingType->name,
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
             'price' => $listing->price,
             'status' => $listing->status,
             'surface' => $listing->surface,
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
 
         ];
     });
     return response()->json(['data' => $data],200);
 }
/**
 * @OA\Get(
 *     path="/api/logements/index/ListeDesLogementsValideDelete",
 *     tags={"Housing"},
 *     summary="Liste des logements déjà verifiés mais suprimés",
 *     description="Récupère la liste des logements qui sont déjà verifiés mais supprimés.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Succès - Liste des logements déjà verifiés mais supprimés",
 *         @OA\JsonContent(
 *           
 *         )
 *     )
 * )
 */
 public function ListeDesLogementsValideDelete()
 {
     $listings = Housing::where('status', 'verified')
     ->where('is_deleted', 1)
     ->where('is_blocked', 0)
     ->get();
 
     $data = $listings->map(function ($listing) {
         return [
             'id_housing' => $listing->id,
             'housing_type_name' => $listing->housingType->name,
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
             'price' => $listing->price,
            'status' => $listing->status,
             'surface' => $listing->surface,
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
 
         ];
     });
     return response()->json(['data' => $data],200);
 }

 /**
 * @OA\Get(
 *     path="/api/logement/admin/statistique",
 *     tags={"Administration"},
 *     summary="Statistiques en matière de nombre ",
 *     description="Récupère les statistiques importantes en matière de nombre pour l'administrateur.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Succès - Statistiques de l'administrateur",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Erreur lors de la récupération des statistiques de l'administrateur")
 *         )
 *     )
 * )
 */
 public function getAdminStatistics()
{
    // Nombre total de logements non supprimés, non bloqués et vérifiés
    $totalListings = Housing::where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('status', "verified")
        ->count();

    // Nombre de logements en attente de validation
    $pendingListings = Housing::where('status', 'Unverified')
        ->count();

    // Nombre total de logements non disponibles
    $unavailableListings = Housing::where('is_disponible', false)
        ->count();

    // Nombre total de logements disponibles
    $availableListings = Housing::where('is_disponible', true)
        ->count();

    // Nombre total de catégories
    $totalCategories = Category::count();

    // Nombre total d'équipements non vérifiés
    $unverifiedEquipments = Equipment::where('is_verified', false)
        ->count();

    // Nombre total d'équipements vérifiés
    $verifiedEquipments = Equipment::where('is_verified', true)
        ->count();

    // Nombre total de préférences
    $totalPreferences = Preference::count();

    // Nombre total d'utilisateurs
    $totalUsers = User::count();

    // Nombre total d'utilisateurs is_traveler
    $totalTravelerUsers = User::where('is_traveller', true)
        ->count();

    // Nombre total d'utilisateurs is_hote
    $totalHostUsers = User::where('is_hote', true)
        ->count();

    // Nombre total d'utilisateurs is_admin
    $totalAdminUsers = User::where('is_admin', true)
        ->count();

    // Nombre total de types de logements
    $totalHousingTypes = HousingType::count();

    // Nombre total de types de propriété
    $totalPropertyTypes = PropertyType::count();

    // Nombre total de critères
    $totalCriteria = Criteria::count();

    // Nombre total de langues
    $totalLanguages = Language::count();

    // Nombre total de rôles
    $totalRoles = Role::count();

    // Nombre total de permissions
    $totalPermissions = Permission::count();

    return response()->json([
        'housing_verified' => $totalListings,
        'housing_unverified' => $pendingListings,
        'unavailable_housing' => $unavailableListings,
        'available_housing' => $availableListings,
        'total_categories' => $totalCategories,
        'unverified_equipments' => $unverifiedEquipments,
        'verified_equipments' => $verifiedEquipments,
        'total_preferences' => $totalPreferences,
        'total_users' => $totalUsers,
        'total_traveler_users' => $totalTravelerUsers,
        'total_host_users' => $totalHostUsers,
        'total_admin_users' => $totalAdminUsers,
        'total_housing_types' => $totalHousingTypes,
        'total_property_types' => $totalPropertyTypes,
        'total_criteria' => $totalCriteria,
        'total_languages' => $totalLanguages,
        'total_roles' => $totalRoles,
        'total_permissions' => $totalPermissions,
    ]);
}

/**
 * @OA\Post(
 *     path="/api/logement/update/one/{id}",
 *     tags={"Housing"},
 *     summary="Valider un logement spécifique en attente de mise à jour",
 *     description="Valide un logement spécifique par son ID et met à jour son is_updated en 0. Envoie également une notification à l'utilisateur propriétaire du logement.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID du logement à valider",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Succès - logement mis à jour avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="du logement mis à jour avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Non trouvé - L'ID du logement spécifié n'existe pas",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="L'ID du logement spécifié n'existe pas")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Erreur lors de la mise à jour du statut de l'annonce")
 *         )
 *     )
 * )
 */
public function UpdateOneHousing($id)
{
    try {

        $housing = Housing::find($id);

        if (!$housing) {
            return response()->json(['message' => 'L\'ID du logement spécifié n\'existe pas'], 404);
        }

        $housing->is_updated = 1;
        $housing->save();

        $notificationName = "Félicitations ! La demande de mise à jour de votre logement a été validé avec succès.";
        $notification = new Notification([
            'name' => $notificationName,
            'user_id' => $housing->user_id,
        ]);
        $notification->save();

        return response()->json(['message' => ' logement mis à jour avec succès'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Erreur lors de la mise à jour'], 500);
    }
}

}
