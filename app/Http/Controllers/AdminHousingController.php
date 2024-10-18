<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Housing;
use App\Models\Charge;
use App\Models\housing_preference;
use App\Models\reduction;
use App\Models\Housing_charge;
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
use App\Models\Note;
use App\Models\Reservation;
use App\Models\Review_reservation;
use App\Http\Controllers\HousingController;
use App\Jobs\SendRegistrationEmail;
use Exception;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmail;
use App\Mail\NotificationEmailwithoutfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
        $listings = Housing::where('status', 'Unverified')->where('is_finished', 1)->get();

        foreach ($listings as $listing){
            (new PromotionController())->actionRepetitif($listing->id);
        }
        $data = $listings->map(function ($listing) {
            return [
                'id_housing' => $listing->id ?? null,
                'housing_type_name' => $listing->housingType->name ?? null,
                'property_type_name' => $listing->propertyType->name ?? null,
                'user_id' => $listing->user_id ?? null,
                'name_housing' => $listing->name ?? null,
                'description' => $listing->description ?? null,
                'number_of_bed' => $listing->number_of_bed ?? null,
                'number_of_traveller' => $listing->number_of_traveller ?? null,
                'sit_geo_lat' => $listing->sit_geo_lat ?? null,
                'sit_geo_lng' => $listing->sit_geo_lng ?? null,
                'country' => $listing->country ?? null,
                'address' => $listing->address ?? null,
                'city' => $listing->city ?? null,
                'department' => $listing->department ?? null,
                'is_camera' => $listing->is_camera ?? null,
                'is_accepted_animal' => $listing->is_accepted_animal ?? null,
                'is_animal_exist' => $listing->is_animal_exist ?? null,
                'is_disponible' => $listing->is_disponible ?? null,
                'interior_regulation' => $listing->interior_regulation ?? null,
                'telephone' => $listing->telephone ?? null,
                'code_pays' => $listing->code_pays ?? null,
                'status' => $listing->status ?? null,
                'arrived_independently' => $listing->arrived_independently ?? null,
                'is_instant_reservation' => $listing->is_instant_reservation ?? null,
                'minimum_duration' => $listing->minimum_duration ?? null,
                'time_before_reservation' => $listing->time_before_reservation ?? null,
                'cancelation_condition' => $listing->cancelation_condition ?? null,
                'departure_instruction' => $listing->departure_instruction ?? null,
                'is_deleted' => $listing->is_deleted ?? null,
                'is_blocked' => $listing->is_blocked ?? null,
                'is_actif' => $listing->is_actif ?? null,
                'is_destroy' => $listing->is_destroy ?? null,
            
                'photos_logement' => $listing->photos->map(function ($photo) {
                    return [
                        'id_photo' => $photo->id ?? null,
                        'path' => $photo->path ?? null,
                        'extension' => $photo->extension ?? null,
                        'is_couverture' => $photo->is_couverture ?? null,
                    ];
                }) ?? null,
            
                'user' => [
                    'id' => $listing->user->id ?? null,
                    'lastname' => $listing->user->lastname ?? null,
                    'firstname' => $listing->user->firstname ?? null,
                    'telephone' => $listing->user->telephone ?? null,
                    'code_pays' => $listing->user->code_pays ?? null,
                    'email' => $listing->user->email ?? null,
                    'country' => $listing->user->country ?? null,
                    'file_profil' => $listing->user->file_profil ?? null,
                    'city' => $listing->user->city ?? null,
                    'address' => $listing->user->address ?? null,
                    'sexe' => $listing->user->sexe ?? null,
                    'postal_code' => $listing->user->postal_code ?? null,
                    'is_admin' => $listing->user->is_admin ?? null,
                    'is_traveller' => $listing->user->is_traveller ?? null,
                    'is_hote' => $listing->user->is_hote ?? null,
                ] ?? null,
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
        foreach ($listings as $listing){
            (new PromotionController())->actionRepetitif($listing->id);
        }
    
        $data = $listings->map(function ($listing) {
            return [
                'id_housing' => $listing->id ?? null,
                'housing_type_name' => $listing->housingType->name ?? null,
                'housing_type_icone' => $listing->housingType->icone ?? null,
                'property_type_name' => $listing->propertyType->name ?? null,
                'property_type_icone' => $listing->propertyType->icone ?? null,
                'user_id' => $listing->user_id ?? null,
                'name_housing' => $listing->name ?? null,
                'description' => $listing->description ?? null,
                'number_of_bed' => $listing->number_of_bed ?? null,
                'number_of_traveller' => $listing->number_of_traveller ?? null,
                'sit_geo_lat' => $listing->sit_geo_lat ?? null,
                'sit_geo_lng' => $listing->sit_geo_lng ?? null,
                'country' => $listing->country ?? null,
                'address' => $listing->address ?? null,
                'city' => $listing->city ?? null,
                'department' => $listing->department ?? null,
                'is_camera' => $listing->is_camera ?? null,
                'is_accepted_animal' => $listing->is_accepted_animal ?? null,
                'is_animal_exist' => $listing->is_animal_exist ?? null,
                'is_disponible' => $listing->is_disponible ?? null,
                'interior_regulation' => $listing->interior_regulation ?? null,
                'telephone' => $listing->telephone ?? null,
                'code_pays' => $listing->code_pays ?? null,
                'status' => $listing->status ?? null,
                'arrived_independently' => $listing->arrived_independently ?? null,
                'is_instant_reservation' => $listing->is_instant_reservation ?? null,
                'minimum_duration' => $listing->minimum_duration ?? null,
                'time_before_reservation' => $listing->time_before_reservation ?? null,
                'cancelation_condition' => $listing->cancelation_condition ?? null,
                'departure_instruction' => $listing->departure_instruction ?? null,
                'is_deleted' => $listing->is_deleted ?? null,
                'is_blocked' => $listing->is_blocked ?? null,
                'is_actif' => $listing->is_actif ?? null,
                'is_destroy' => $listing->is_destroy ?? null,
                'is_accept_anulation' => $listing->is_accept_anulation ?? null,
                'delai_partiel_remboursement' => $listing->delai_partiel_remboursement ?? null,
                'delai_integral_remboursement' => $listing->delai_integral_remboursement ?? null,
                'valeur_integral_remboursement' => $listing->valeur_integral_remboursement ?? null,
                'valeur_partiel_remboursement' => $listing->valeur_partiel_remboursement ?? null,
            
                'photos_logement' => $listing->photos->map(function ($photo) {
                    return [
                        'id_photo' => $photo->id ?? null,
                        'path' => $photo->path ?? null,
                        'extension' => $photo->extension ?? null,
                        'is_couverture' => $photo->is_couverture ?? null,
                    ];
                }) ?? null,
            
                'user' => [
                    'id' => $listing->user->id ?? null,
                    'lastname' => $listing->user->lastname ?? null,
                    'firstname' => $listing->user->firstname ?? null,
                    'telephone' => $listing->user->telephone ?? null,
                    'code_pays' => $listing->user->code_pays ?? null,
                    'email' => $listing->user->email ?? null,
                    'country' => $listing->user->country ?? null,
                    'file_profil' => $listing->user->file_profil ?? null,
                    'city' => $listing->user->city ?? null,
                    'address' => $listing->user->address ?? null,
                    'sexe' => $listing->user->sexe ?? null,
                    'postal_code' => $listing->user->postal_code ?? null,
                    'is_admin' => $listing->user->is_admin ?? null,
                    'is_traveller' => $listing->user->is_traveller ?? null,
                    'is_hote' => $listing->user->is_hote ?? null,
                ] ?? null,
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

    (new PromotionController())->actionRepetitif($listing->id);
    $equipments_nouveau_by_category = $listing->housingEquipments
    ->where('is_verified', 0)
    ->groupBy('category.name')
    ->map(function ($categoryEquipment, $categoryName) {
        return [
            'category_id' => $categoryEquipment->first()->category_id ?? null,
            'category_name' => $categoryName ?? null,
            'equipments' => $categoryEquipment->map(function ($housingEquipment) {
                return [
                    'equipment_id' => $housingEquipment->equipment_id ?? null,
                    'name' => $housingEquipment->equipment->name ?? null,
                    'icone' => $housingEquipment->equipment->icone ?? null,
                    'valide' => $housingEquipment->is_verified ?? null,
                    'id_housing_equipment' =>  $housingEquipment->id ?? null,
                ];
            })->toArray() ?? null,
        ];
    }) ?? null;

$equipments_defaut_by_category = $listing->housingEquipments
    ->where('is_verified', 1)
    ->groupBy('category.name')
    ->map(function ($categoryEquipment, $categoryName) {
        return [
            'category_id' => $categoryEquipment->first()->category_id ?? null,
            'category_name' => $categoryName ?? null,
            'equipments' => $categoryEquipment->map(function ($housingEquipment) {
                return [
                    'equipment_id' => $housingEquipment->equipment_id ?? null,
                    'name' => $housingEquipment->equipment->name ?? null,
                    'icone' => $housingEquipment->equipment->icone ?? null,
                    'valide' => $housingEquipment->is_verified ?? null,
                    'id_housing_equipment' =>  $housingEquipment->id ?? null,
                ];
            })->toArray() ?? null,
        ];
    }) ?? null;

$hoteCharge_id = [];
$travelerCharge_id = [];
$totalHoteCharge = 0;
$totalTravelerCharge = 0;

$housingCharges = Housing_charge::where('housing_id', $id)->get();

if (!$housingCharges->isEmpty()) {
    foreach ($housingCharges as $housingCharge) {
        $charge = Charge::find($housingCharge->charge_id);
        $chargeData = [
            'id_housing_charge' => $housingCharge->id ?? null,
            'housing_id' => $housingCharge->housing_id ?? null,
            'id_charge' => $charge->id ?? null,
            'charge_name' => $charge->name ?? null,
            'is_mycharge' => $housingCharge->is_mycharge ?? null,
            'valeur_charge' => $housingCharge->valeur ?? null
        ];

        if ($housingCharge->is_mycharge) {
            $hoteCharge_id[] = $chargeData;
            $totalHoteCharge += $housingCharge->valeur ?? 0;
        } else {
            $travelerCharge_id[] = $chargeData;
            $totalTravelerCharge += $housingCharge->valeur ?? 0;
        }
    }
}

    

    

    $totalCharge = $totalHoteCharge + $totalTravelerCharge;

//    return $listing;
        /*
   if(!$listing->is_finished){
    return (new ServiceController())->apiResponse(404,[], "Vous ne pouvez pas voir les détails de ce logement car il n'est pas entièrement rempli");
   }
*/
   $data = [
    'id_housing' => $listing->id ?? null,
    'housing_type_id' => $listing->housing_type_id ?? null,
    'housing_type_name' => $listing->housingType->name ?? null,
    'property_type_id' => $listing->property_type_id ?? null,
    'property_type_name' => $listing->propertyType->name ?? null,
    'user_id' => $listing->user_id ?? null,
    'name_housing' => $listing->name ?? null,
    'description' => $listing->description ?? null,
    'number_of_bed' => $listing->number_of_bed ?? null,
    'number_of_traveller' => $listing->number_of_traveller ?? null,
    'sit_geo_lat' => $listing->sit_geo_lat ?? null,
    'sit_geo_lng' => $listing->sit_geo_lng ?? null,
    'country' => $listing->country ?? null,
    'address' => $listing->address ?? null,
    'city' => $listing->city ?? null,
    'department' => $listing->department ?? null,
    'is_camera' => $listing->is_camera ?? null,
    'is_accepted_animal' => $listing->is_accepted_animal ?? null,
    'is_animal_exist' => $listing->is_animal_exist ?? null,
    'is_disponible' => $listing->is_disponible ?? null,
    'interior_regulation' => $listing->interior_regulation ?? null,
    'interior_regulation_pdf' => $listing->interior_regulation_pdf ?? null,
    'telephone' => $listing->telephone ?? null,
    'code_pays' => $listing->code_pays ?? null,
    'price' => $listing->price ?? null,
    'status' => $listing->status ?? null,
    'surface' => $listing->surface ?? null,
    'arrived_independently' => $listing->arrived_independently ?? null,
    'is_instant_reservation' => $listing->is_instant_reservation ?? null,
    'minimum_duration' => $listing->minimum_duration ?? null,
    'time_before_reservation' => $listing->time_before_reservation ?? null,
    'cancelation_condition' => $listing->cancelation_condition ?? null,
    'departure_instruction' => $listing->departure_instruction ?? null,
    'is_accept_arm' => $listing->is_accept_arm ?? null,
    'is_accept_noise' => $listing->is_accept_noise ?? null,
    'is_accept_smoking' => $listing->is_accept_smoking ?? null,
    'is_accept_chill' => $listing->is_accept_smoking ?? null,
    'is_accept_alcool' => $listing->is_accept_alccol ?? null,
    'is_deleted' => $listing->is_deleted ?? null,
    'is_blocked' => $listing->is_blocked ?? null,
    'is_actif' => $listing->is_actif ?? null,
    'is_destroy' => $listing->is_destroy ?? null,
    'is_accept_anulation' => $listing->is_accept_anulation ?? null,
    'delai_partiel_remboursement' => $listing->delai_partiel_remboursement ?? null,
    'delai_integral_remboursement' => $listing->delai_integral_remboursement ?? null,
    'valeur_integral_remboursement' => $listing->valeur_integral_remboursement ?? null,
    'valeur_partiel_remboursement' => $listing->valeur_partiel_remboursement ?? null,

    'photos_logement' => $listing->photos->map(function ($photo) {
        return [
            'id_photo' => $photo->id ?? null,
            'path' => $photo->path ?? null,
            'extension' => $photo->extension ?? null,
            'is_couverture' => $photo->is_couverture ?? null,
            'is_verified' => $photo->is_verified ?? null,
        ];
    })->toArray() ?? [],

    'user' => [
        'id' => $listing->user->id ?? null,
        'lastname' => $listing->user->lastname ?? null,
        'firstname' => $listing->user->firstname ?? null,
        'telephone' => $listing->user->telephone ?? null,
        'code_pays' => $listing->user->code_pays ?? null,
        'email' => $listing->user->email ?? null,
        'country' => $listing->user->country ?? null,
        'file_profil' => $listing->user->file_profil ?? null,
        'city' => $listing->user->city ?? null,
        'address' => $listing->user->address ?? null,
        'sexe' => $listing->user->sexe ?? null,
        'postal_code' => $listing->user->postal_code ?? null,
    ],

    'housing_preference' => [
        'nouveau' => $listing->housing_preference->filter(function ($preference) {
            return !$preference->preference->is_verified ?? null;
        })->map(function ($preference) {
            return [
                'id' => $preference->id ?? null,
                'preference_id' => $preference->preference_id ?? null,
                'preference_name' => $preference->preference->name ?? null,
                'preference_icone' => $preference->preference->icone ?? null,
                'valide' => $preference->is_verified ?? null
            ];
        })->toArray() ?? [],

        'defaut' => $listing->housing_preference->filter(function ($preference) {
            return $preference->preference->is_verified ?? null;
        })->map(function ($preference) {
            return [
                'id' => $preference->id ?? null,
                'preference_id' => $preference->preference_id ?? null,
                'preference_name' => $preference->preference->name ?? null,
                'preference_icone' => $preference->preference->icone ?? null,
                'valide' => $preference->is_verified ?? null
            ];
        })->toArray() ?? [],
    ],

    'reductions' => $listing->reductions->toArray() ?? [],
    'promotions' => $listing->promotions->toArray() ?? [],

    'categories' => [
        'nouveau' => $listing->housingCategoryFiles->filter(function ($categoryFile) {
            return !$categoryFile->category->is_verified ?? null;
        })->groupBy('category.name')->map(function ($categoryFiles) {
            return [
                'category_id' => $categoryFiles->first()->category_id ?? null,
                'category_name' => $categoryFiles->first()->category->name ?? null,
                'number' => $categoryFiles->first()->number ?? null,
                'photos_category' => $categoryFiles->map(function ($categoryFile) {
                    return [
                        'file_id' => $categoryFile->file->id ?? null,
                        'path' => $categoryFile->file->path ?? null,
                    ];
                })->toArray() ?? [],
                'valide' => $categoryFiles->first()->is_verified ?? null,
            ];
        })->values()->toArray() ?? [],

        'defaut' => $listing->housingCategoryFiles->filter(function ($categoryFile) {
            return $categoryFile->category->is_verified ?? null;
        })->groupBy('category.name')->map(function ($categoryFiles) {
            return [
                'category_id' => $categoryFiles->first()->category_id ?? null,
                'category_name' => $categoryFiles->first()->category->name ?? null,
                'number' => $categoryFiles->first()->number ?? null,
                'photos_category' => $categoryFiles->map(function ($categoryFile) {
                    return [
                        'file_id' => $categoryFile->file->id ?? null,
                        'path' => $categoryFile->file->path ?? null,
                    ];
                })->toArray() ?? [],
                'valide' => $categoryFiles->first()->is_verified ?? null,
            ];
        })->values()->toArray() ?? [],
    ],

    'equipments' => [
        'nouveau' => $equipments_nouveau_by_category->values()->toArray() ?? [],
        'defaut' => $equipments_defaut_by_category->values()->toArray() ?? [],
    ],
    'charges' => [
        'hote_charges' => $hoteCharge_id ?? [],
        'total_hote_charge' => $totalHoteCharge ?? 0,
        'traveler_charges' => $travelerCharge_id ?? [],
        'total_traveler_charge' => $totalTravelerCharge ?? 0,
        'total_charge' => ($totalHoteCharge ?? 0) + ($totalTravelerCharge ?? 0),
    ],
];


    // return 1;


    return response()->json(['data' => $data]);
}


/**
 * @OA\Put(
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

         if ($housing->is_finished == 0) {
            return response()->json(['message' => 'Repends toi mon ami,tu gagnes quoi en faisant ça. Ne sais tu pas que le processus d\'ajout de ce logement n\'est pas encore finalisé'], 404);
        }
         (new PromotionController())->actionRepetitif($housing->id);
 
         $housing->status = 'verified';
         $housing->save();
 
         $notificationName = "Félicitations ! Votre logement a été validé et est maintenant visible sur la plateforme.";
        
         $mail = [
            'title' => "Confirmation de validation de d'un logement",
            'body' => "Félicitations ! Votre logement a été validé et est maintenant visible sur la plateforme."
           ];

           dispatch( new SendRegistrationEmail($housing->user->email, $mail['body'], $mail['title'], 2));
 
         return response()->json(['message' => 'Statut du logement mis à jour avec succès'], 200);
     } catch (\Exception $e) {
         return response()->json(['message' => 'Erreur lors de la mise à jour du statut de l\'annonce'], 500);
     }
 }
/**
 * @OA\Put(
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
         if(count($housingIds) ==0){
             return response()->json(['message' => 'Veuillez renseigner au moins un ID de logement'], 404);
         }
         $existingHousingIds = Housing::whereIn('id', $housingIds)->pluck('id')->toArray();
 
         $missingIds = array_diff($housingIds, $existingHousingIds);
         if (!empty($missingIds)) {
             return response()->json(['message' => 'Certains IDs de logements n\'existent pas dans la base de données'], 404);
         }
 
         foreach ($housingIds as $id) {
            (new PromotionController())->actionRepetitif($id);
             $housing = Housing::findOrFail($id);
             if ($housing->is_finished == 0) {
                return response()->json(['message' => 'Repends toi mon ami,tu gagnes quoi en faisant ça. Ne sais tu pas que le processus d\'ajout de ce logement n\'est pas encore finalisé'], 404);
            }
             $housing->status = 'verified';
             $housing->save();
 
             $notificationName = "Félicitations ! Votre logement a été validé et est maintenant visible sur la plateforme.";
             $notification = new Notification([
                 'name' => $notificationName,
                 'user_id' => $housing->user_id,
             ]);
             $notification->save();
             $mail = [
                'title' => "Confirmation d'ajout d'un logement",
                'body' => "Félicitations ! Votre logement a été validé et est maintenant visible sur la plateforme."
               ];
            
            dispatch( new SendRegistrationEmail($housing->user->email, $mail['body'], $mail['title'], 2));

         }
 
         return response()->json(['message' => 'Statut des logements mis à jour avec succès'], 200);
     } catch (\Exception $e) {
         return response()->json(['message' => 'Erreur lors de la mise à jour du statut des logements'], 500);
     }
 }

 /**
 * @OA\Get(
 *     path="/api/logement/index/ListeDesLogementsValideBloque",
 *     tags={"Housing"},
 *     summary="Liste des logements déjà verifiés mais bloqués par l'administrateur",
 *     description="Récupère la liste des logements qui sont déjà verifiés mais bloqués  par l'administrateur.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Succès - Liste des logements déjà verifiés mais bloqués  par l'administrateur",
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
     ->where('is_finished', 1)
     ->get();

     foreach ($listings as $listing){
        (new PromotionController())->actionRepetitif($listing->id);
    }

     $data = $listings->map(function ($listing) {
        return [
            'id_housing' => $listing->id ?? null,
            'housing_type_name' => $listing->housingType->name ?? null,
            'property_type_name' => $listing->propertyType->name ?? null,
            'user_id' => $listing->user_id ?? null,
            'name_housing' => $listing->name ?? null,
            'description' => $listing->description ?? null,
            'number_of_bed' => $listing->number_of_bed ?? null,
            'number_of_traveller' => $listing->number_of_traveller ?? null,
            'sit_geo_lat' => $listing->sit_geo_lat ?? null,
            'sit_geo_lng' => $listing->sit_geo_lng ?? null,
            'country' => $listing->country ?? null,
            'address' => $listing->address ?? null,
            'city' => $listing->city ?? null,
            'department' => $listing->department ?? null,
            'is_camera' => $listing->is_camera ?? null,
            'is_accepted_animal' => $listing->is_accepted_animal ?? null,
            'is_animal_exist' => $listing->is_animal_exist ?? null,
            'is_disponible' => $listing->is_disponible ?? null,
            'interior_regulation' => $listing->interior_regulation ?? null,
            'telephone' => $listing->telephone ?? null,
            'code_pays' => $listing->code_pays ?? null,
            'price' => $listing->price ?? null,
            'status' => $listing->status ?? null,
            'surface' => $listing->surface ?? null,
            'arrived_independently' => $listing->arrived_independently ?? null,
            'is_instant_reservation' => $listing->is_instant_reservation ?? null,
            'minimum_duration' => $listing->minimum_duration ?? null,
            'time_before_reservation' => $listing->time_before_reservation ?? null,
            'cancelation_condition' => $listing->cancelation_condition ?? null,
            'departure_instruction' => $listing->departure_instruction ?? null,
            'is_deleted' => $listing->is_deleted ?? null,
            'is_blocked' => $listing->is_blocked ?? null,
            'is_actif' => $listing->is_actif ?? null,
            'is_destroy' => $listing->is_destroy ?? null,
            'is_accept_anulation' => $listing->is_accept_anulation ?? null,
            'delai_partiel_remboursement' => $listing->delai_partiel_remboursement ?? null,
            'delai_integral_remboursement' => $listing->delai_integral_remboursement ?? null,
            'valeur_integral_remboursement' => $listing->valeur_integral_remboursement ?? null,
            'valeur_partiel_remboursement' => $listing->valeur_partiel_remboursement ?? null,
        
            'photos_logement' => $listing->photos->map(function ($photo) {
                return [
                    'id_photo' => $photo->id ?? null,
                    'path' => $photo->path ?? null,
                    'extension' => $photo->extension ?? null,
                    'is_couverture' => $photo->is_couverture ?? null,
                ];
            })->toArray(),
            'user' => [
                'id' => $listing->user->id ?? null,
                'lastname' => $listing->user->lastname ?? null,
                'firstname' => $listing->user->firstname ?? null,
                'telephone' => $listing->user->telephone ?? null,
                'code_pays' => $listing->user->code_pays ?? null,
                'email' => $listing->user->email ?? null,
                'country' => $listing->user->country ?? null,
                'file_profil' => $listing->user->file_profil ?? null,
                'city' => $listing->user->city ?? null,
                'address' => $listing->user->address ?? null,
                'sexe' => $listing->user->sexe ?? null,
                'postal_code' => $listing->user->postal_code ?? null,
                'is_admin' => $listing->user->is_admin ?? null,
                'is_traveller' => $listing->user->is_traveller ?? null,
                'is_hote' => $listing->user->is_hote ?? null,
            ],
        ];
        
     });
     return response()->json(['data' => $data],200);
 }
/**
 * @OA\Get(
 *     path="/api/logement/index/ListeDesLogementsValideDelete",
 *     tags={"Housing"},
 *     summary="Liste des logements déjà verifiés mais suprimés par l'administrateur",
 *     description="Récupère la liste des logements qui sont déjà verifiés mais supprimés par l'administrateur.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Succès - Liste des logements déjà verifiés mais supprimés  par l'administrateur",
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
     ->where('is_finished', 1)
     ->get();

     foreach ($listings as $listing){
        (new PromotionController())->actionRepetitif($listing->id);
    }
 
     $data = $listings->map(function ($listing) {
        return [
            'id_housing' => $listing->id ?? null,
            'housing_type_name' => $listing->housingType->name ?? null,
            'property_type_name' => $listing->propertyType->name ?? null,
            'user_id' => $listing->user_id ?? null,
            'name_housing' => $listing->name ?? null,
            'description' => $listing->description ?? null,
            'number_of_bed' => $listing->number_of_bed ?? null,
            'number_of_traveller' => $listing->number_of_traveller ?? null,
            'sit_geo_lat' => $listing->sit_geo_lat ?? null,
            'sit_geo_lng' => $listing->sit_geo_lng ?? null,
            'country' => $listing->country ?? null,
            'address' => $listing->address ?? null,
            'city' => $listing->city ?? null,
            'department' => $listing->department ?? null,
            'is_camera' => $listing->is_camera ?? null,
            'is_accepted_animal' => $listing->is_accepted_animal ?? null,
            'is_animal_exist' => $listing->is_animal_exist ?? null,
            'is_disponible' => $listing->is_disponible ?? null,
            'interior_regulation' => $listing->interior_regulation ?? null,
            'telephone' => $listing->telephone ?? null,
            'code_pays' => $listing->code_pays ?? null,
            'price' => $listing->price ?? null,
            'status' => $listing->status ?? null,
            'surface' => $listing->surface ?? null,
            'arrived_independently' => $listing->arrived_independently ?? null,
            'is_instant_reservation' => $listing->is_instant_reservation ?? null,
            'minimum_duration' => $listing->minimum_duration ?? null,
            'time_before_reservation' => $listing->time_before_reservation ?? null,
            'cancelation_condition' => $listing->cancelation_condition ?? null,
            'departure_instruction' => $listing->departure_instruction ?? null,
            'is_deleted' => $listing->is_deleted ?? null,
            'is_blocked' => $listing->is_blocked ?? null,
            'is_actif' => $listing->is_actif ?? null,
            'is_destroy' => $listing->is_destroy ?? null,
            'is_accept_anulation' => $listing->is_accept_anulation ?? null,
            'delai_partiel_remboursement' => $listing->delai_partiel_remboursement ?? null,
            'delai_integral_remboursement' => $listing->delai_integral_remboursement ?? null,
            'valeur_integral_remboursement' => $listing->valeur_integral_remboursement ?? null,
            'valeur_partiel_remboursement' => $listing->valeur_partiel_remboursement ?? null,
        
            'photos_logement' => $listing->photos->map(function ($photo) {
                return [
                    'id_photo' => $photo->id ?? null,
                    'path' => $photo->path ?? null,
                    'extension' => $photo->extension ?? null,
                    'is_couverture' => $photo->is_couverture ?? null,
                ];
            })->toArray(),
            'user' => [
                'id' => $listing->user->id ?? null,
                'lastname' => $listing->user->lastname ?? null,
                'firstname' => $listing->user->firstname ?? null,
                'telephone' => $listing->user->telephone ?? null,
                'code_pays' => $listing->user->code_pays ?? null,
                'email' => $listing->user->email ?? null,
                'country' => $listing->user->country ?? null,
                'file_profil' => $listing->user->file_profil ?? null,
                'city' => $listing->user->city ?? null,
                'address' => $listing->user->address ?? null,
                'sexe' => $listing->user->sexe ?? null,
                'postal_code' => $listing->user->postal_code ?? null,
                'is_admin' => $listing->user->is_admin ?? null,
                'is_traveller' => $listing->user->is_traveller ?? null,
                'is_hote' => $listing->user->is_hote ?? null,
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
    $totalListings = Housing::where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('status', "verified")
        ->where('is_finished', 1)
        ->count();

    // Nombre de logements en attente de validation
    $pendingListings = Housing::where('status', 'Unverified')
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

    $adminRole = DB::table('rights')->where('name', 'admin')->first();
    $travelerRole = DB::table('rights')->where('name', 'traveler')->first();
    $hostRole = DB::table('rights')->where('name', 'hote')->first();

    if (!$adminRole || !$travelerRole || !$hostRole) {
        return response()->json([
            'message' => 'Les rôles requis n\'ont pas été trouvés.'
        ], 404);
    }

    $totalAdminUsers = User::whereHas('user_right', function ($query) use ($adminRole) {
        $query->where('right_id', $adminRole->id);
    })->count();

    $totalTravelerUsers = User::whereHas('user_right', function ($query) use ($travelerRole) {
        $query->where('right_id', $travelerRole->id);
    })->count();

    $totalHostUsers = User::whereHas('user_right', function ($query) use ($hostRole) {
        $query->where('right_id', $hostRole->id);
    })->count();
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

        (new PromotionController())->actionRepetitif($id);

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
/**
 * @OA\Get(
 *     path="/api/logement/index/ListeDesLogementsValideDisable",
 *     tags={"Housing"},
 *     summary="Liste des logements déjà verifiés mais desactivés par l'hôte",
 *     description="Récupère la liste des logements qui sont déjà verifiés mais desactivés par l'hôte.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Succès - Liste des logements déjà verifiés mais desactivé par l'hôte",
 *         @OA\JsonContent(
 *           
 *         )
 *     )
 * )
 */
public function ListeDesLogementsValideDisable()
{
    $listings = Housing::where('status', 'verified')
    ->where('is_deleted', 0)
    ->where('is_blocked', 0)
    ->where('is_destroy', 0)
    ->where('is_actif', 0)
    ->where('is_finished', 1)
    ->get();

    foreach ($listings as $listing){
        (new PromotionController())->actionRepetitif($listing->id);
    }

    $data = $listings->map(function ($listing) {
        return [
            'id_housing' => $listing->id ?? null,
            'housing_type_name' => $listing->housingType->name ?? null,
            'property_type_name' => $listing->propertyType->name ?? null,
            'user_id' => $listing->user_id ?? null,
            'name_housing' => $listing->name ?? null,
            'description' => $listing->description ?? null,
            'number_of_bed' => $listing->number_of_bed ?? null,
            'number_of_traveller' => $listing->number_of_traveller ?? null,
            'sit_geo_lat' => $listing->sit_geo_lat ?? null,
            'sit_geo_lng' => $listing->sit_geo_lng ?? null,
            'country' => $listing->country ?? null,
            'address' => $listing->address ?? null,
            'city' => $listing->city ?? null,
            'department' => $listing->department ?? null,
            'is_camera' => $listing->is_camera ?? null,
            'is_accepted_animal' => $listing->is_accepted_animal ?? null,
            'is_animal_exist' => $listing->is_animal_exist ?? null,
            'is_disponible' => $listing->is_disponible ?? null,
            'interior_regulation' => $listing->interior_regulation ?? null,
            'telephone' => $listing->telephone ?? null,
            'code_pays' => $listing->code_pays ?? null,
            'price' => $listing->price ?? null,
            'status' => $listing->status ?? null,
            'surface' => $listing->surface ?? null,
            'arrived_independently' => $listing->arrived_independently ?? null,
            'is_instant_reservation' => $listing->is_instant_reservation ?? null,
            'minimum_duration' => $listing->minimum_duration ?? null,
            'time_before_reservation' => $listing->time_before_reservation ?? null,
            'cancelation_condition' => $listing->cancelation_condition ?? null,
            'departure_instruction' => $listing->departure_instruction ?? null,
            'is_deleted' => $listing->is_deleted ?? null,
            'is_blocked' => $listing->is_blocked ?? null,
            'is_actif' => $listing->is_actif ?? null,
            'is_destroy' => $listing->is_destroy ?? null,
            'is_accept_anulation' => $listing->is_accept_anulation ?? null,
            'delai_partiel_remboursement' => $listing->delai_partiel_remboursement ?? null,
            'delai_integral_remboursement' => $listing->delai_integral_remboursement ?? null,
            'valeur_integral_remboursement' => $listing->valeur_integral_remboursement ?? null,
            'valeur_partiel_remboursement' => $listing->valeur_partiel_remboursement ?? null,
        
            'photos_logement' => $listing->photos->map(function ($photo) {
                return [
                    'id_photo' => $photo->id ?? null,
                    'path' => $photo->path ?? null,
                    'extension' => $photo->extension ?? null,
                    'is_couverture' => $photo->is_couverture ?? null,
                ];
            })->toArray(),
            'user' => [
                'id' => $listing->user->id ?? null,
                'lastname' => $listing->user->lastname ?? null,
                'firstname' => $listing->user->firstname ?? null,
                'telephone' => $listing->user->telephone ?? null,
                'code_pays' => $listing->user->code_pays ?? null,
                'email' => $listing->user->email ?? null,
                'country' => $listing->user->country ?? null,
                'file_profil' => $listing->user->file_profil ?? null,
                'city' => $listing->user->city ?? null,
                'address' => $listing->user->address ?? null,
                'sexe' => $listing->user->sexe ?? null,
                'postal_code' => $listing->user->postal_code ?? null,
                'is_admin' => $listing->user->is_admin ?? null,
                'is_traveller' => $listing->user->is_traveller ?? null,
                'is_hote' => $listing->user->is_hote ?? null,
            ],
        ];
        
    });
    return response()->json(['data' => $data],200);
}

 /**
         * @OA\Get(
         *     path="/api/logement/getHousingDestroyedByHote",
         *     summary="Liste des logements supprimés par les hotes",
         *     tags={"Housing"},
         * security={{"bearerAuth": {}}},
         *     @OA\Response(
         *         response=200,
         *         description="List of housing what be retrieve by hote"
         *
         *     )
         * )
         */
        public function getHousingDestroyedByHote(){
            try{
                $listings = Housing::where('is_destroy',true)->get();
                $data = [];
                foreach($listings as $listing){
                    (new PromotionController())->actionRepetitif($listing->id);
                    $data[] = [
                        'id_housing' => $listing->id ?? null,
                        'housing_type_name' => $listing->housingType->name ?? null,
                        'property_type_name' => $listing->propertyType->name ?? null,
                        'user_id' => $listing->user_id ?? null,
                        'name_housing' => $listing->name ?? null,
                        'description' => $listing->description ?? null,
                        'number_of_bed' => $listing->number_of_bed ?? null,
                        'number_of_traveller' => $listing->number_of_traveller ?? null,
                        'sit_geo_lat' => $listing->sit_geo_lat ?? null,
                        'sit_geo_lng' => $listing->sit_geo_lng ?? null,
                        'country' => $listing->country ?? null,
                        'address' => $listing->address ?? null,
                        'city' => $listing->city ?? null,
                        'department' => $listing->department ?? null,
                        'is_camera' => $listing->is_camera ?? null,
                        'is_accepted_animal' => $listing->is_accepted_animal ?? null,
                        'is_animal_exist' => $listing->is_animal_exist ?? null,
                        'is_disponible' => $listing->is_disponible ?? null,
                        'interior_regulation' => $listing->interior_regulation ?? null,
                        'telephone' => $listing->telephone ?? null,
                        'code_pays' => $listing->code_pays ?? null,
                        'price' => $listing->price ?? null,
                        'status' => $listing->status ?? null,
                        'surface' => $listing->surface ?? null,
                        'arrived_independently' => $listing->arrived_independently ?? null,
                        'is_instant_reservation' => $listing->is_instant_reservation ?? null,
                        'minimum_duration' => $listing->minimum_duration ?? null,
                        'time_before_reservation' => $listing->time_before_reservation ?? null,
                        'cancelation_condition' => $listing->cancelation_condition ?? null,
                        'departure_instruction' => $listing->departure_instruction ?? null,
                        'is_deleted' => $listing->is_deleted ?? null,
                        'is_blocked' => $listing->is_blocked ?? null,
                        'is_actif' => $listing->is_actif ?? null,
                        'is_destroy' => $listing->is_destroy ?? null,
                        'is_accept_anulation' => $listing->is_accept_anulation ?? null,
                        'delai_partiel_remboursement' => $listing->delai_partiel_remboursement ?? null,
                        'delai_integral_remboursement' => $listing->delai_integral_remboursement ?? null,
                        'valeur_integral_remboursement' => $listing->valeur_integral_remboursement ?? null,
                        'valeur_partiel_remboursement' => $listing->valeur_partiel_remboursement ?? null,
                    
                        'user_detail' => $listing->user ?? null,
                    ];
                    
                }
                return response()->json(['data' => $data], 200);
            } catch(Exception $e) {
                return response()->json($e->getMessage());
            }
          
        }
    
  /**
     * @OA\Get(
     *     path="/api/logement/country_with_many_housing",
     *     summary="Top 10 des pays avec le plus grand nombre de logement sur la plateforme",
     * description="Top 10 des pays avec le plus grand nombre de logement sur la plateforme",
     *     tags={"Administration"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Top 10 des pays avec le plus grand nombre de logement sur la plateforme"
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
     *     path="/api/logement/hote_with_many_housing",
     *     summary="Top 10 des utilisateurs(hotes) avec le plus grand nombre de logement",
     * description="Top 10 des utilisateurs(hotes) avec le plus grand nombre de logement",
     *     tags={"Administration"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Top 10 des utilisateurs(hotes) avec le plus grand nombre de logement"
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
     *     path="/api/logement/getTop10HousingByAverageNotes",
     *     summary="Top 10 des logements avec le plus grand nombre de note de manière générale(moyenne des notes des critère qui sera considéré)",
     * description="Top 10 des logements avec le plus grand nombre de note de manière générale(moyenne des notes des critère qui sera considéré)",
     *     tags={"Administration"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="To 10 des logements avec le plus grand nombre de note de manière générale"
     *
     *     )
     * )
     */
public function getTop10HousingByAverageNotes() {
    $housingAverages = Housing::with('reservation.notes.criteria')
        ->get()
        ->map(function ($housing) {
            $criteriaAverages = [];
            $totalAverage = 0;
            $numCriteria = 0;

            $reservations = $housing->reservation;
            
            foreach ($reservations as $reservation) {
                foreach ($reservation->notes as $note) {
                    if (!isset($criteriaAverages[$note->criteria_id])) {
                        $criteriaAverages[$note->criteria_id] = ['sum' => 0, 'count' => 0];
                    }
                    $criteriaAverages[$note->criteria_id]['sum'] += $note->note;
                    $criteriaAverages[$note->criteria_id]['count']++;
                }
            }

            foreach ($criteriaAverages as $criteriaId => $data) {
                $average = $data['sum'] / $data['count'];
                $criteriaAverages[$criteriaId]['average'] = $average;
                $totalAverage += $average;
                $numCriteria++;
            }
             if($numCriteria==0)
             {
                $overallAverage =0;
             }else{
                $overallAverage = $totalAverage / $numCriteria;
             }
            
            return [
                'housing_id' => $housing->id,
                'housing_name' => $housing->name,
                'overall_average' => $overallAverage,
            ];
        });

    $top10Housing = $housingAverages->sortByDesc('overall_average')->take(10);

    return response()->json(['data' => $top10Housing], 200);
}


   /**
 * @OA\Get(
 *     path="/api/logement/HousingHoteInProgressForAdmin",
 *  security={{"bearerAuth": {}}},
 *     tags={"Housing"},
 *     summary="Liste des logements non rempli complètement par les hôtes",
 *     description="Récupère la liste des logements des logements non rempli complètement par les hôtes.",
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements non rempli complètement par les hôtes",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Liste des logements non rempli complètement par les hôtes"
 *     )
 * )
 */
public function HousingHoteInProgressForAdmin(){
    $listings = Housing::where('is_finished', 0)
        ->get();
        $h = new HousingController();
        $data = $h->formatListingsData($listings);
        return response()->json(['data' => $data,'nombre'=>$data->count()],200);
}


}
