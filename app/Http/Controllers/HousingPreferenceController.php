<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Housing;
use App\Models\housing_preference;
use App\Models\Preference;
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
class HousingPreferenceController extends Controller
{

    /**
 * @OA\Get(
 *     path="/api/logement/{housingId}/preferences",
 *     tags={"Housing Preference"},
 *     summary="Récupérer les préférences associées à un logement donné",
 *     description="Récupère les préférences associées à un logement spécifié en fonction de l'ID du logement.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         description="L'ID du logement pour lequel récupérer les préférences",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Succès - Les préférences associées au logement ont été récupérées avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             properties={
 *                 @OA\Property(
 *                     property="preferences",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         properties={
 *                             @OA\Property(
 *                                 property="id",
 *                                 type="integer",
 *                                 description="L'ID de la préférence"
 *                             ),
 *                             @OA\Property(
 *                                 property="preference_id",
 *                                 type="integer",
 *                                 description="L'ID de la préférence de logement"
 *                             ),
 *                             @OA\Property(
 *                                 property="preference_name",
 *                                 type="string",
 *                                 description="Le nom de la préférence de logement"
 *                             ),
 *                         }
 *                     )
 *                 )
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé avec l'ID spécifié"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur - Impossible de récupérer les préférences associées au logement"
 *     )
 * )
 */

    public function housingPreference($housingId)
{
    $housingPreferences = housing_preference::where('housing_id', $housingId)->get();

    if ($housingPreferences->isEmpty()) {
        return response()->json(['message' => 'Aucune préférence associée à ce logement'], 404);
    }

    $preferences = [];
    
    foreach ($housingPreferences as $housingPreference) {
        $preference = Preference::find($housingPreference->preference_id);
        
        $preferences[] = [
            'id_housing_preference' => $housingPreference->id,
            'id_housing' => $housingPreference->housing_id,
            'preference_id' => $preference->id,
            'preference_name' => $preference->name,
            'icone' => $preference->icone,
            'is_verified' => $preference->is_verified,
        ];
    }

    return response()->json(['data' => $preferences], 200);
}

    /**
 * @OA\Delete(
 *     path="/api/logement/preference",
 *     tags={"Housing Preference"},
 *     summary="Retirer des préférences associées à un logement",
 *     description="Supprime l'association entre plusieurs préférences et un logement à partir des IDs des associations.",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"housingPreferenceIds"},
 *                 @OA\Property(
 *                     property="housingPreferenceIds",
 *                     type="array",
 *                     @OA\Items(type="integer"),
 *                     description="Tableau contenant les IDs des préférences du logement à supprimer"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Les préférences du logement ont été retirées avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Les préférences du logement ont été retirées avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Un ou plusieurs préférences du logement à retirer n'existent pas",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Un ou plusieurs préférences du logement à retirer n'existent pas")
 *         )
 *     )
 * )
 */
public function deletePreferenceHousing(Request $request)
{
    try {
        $request->validate([
            'housingPreferenceIds' => 'required|array',
            'housingPreferenceIds.*' => 'integer|exists:housing_preferences,id',
        ]);

        $housingPreferenceIds = $request->input('housingPreferenceIds');

        Housing_preference::whereIn('id', $housingPreferenceIds)->delete();

        return response()->json(['message' => 'Les préférences du logement ont été retirées avec succès'], 200);
    } catch (ValidationException $e) {
        return response()->json(['message' => 'Un ou plusieurs préférences du logement à retirer n\'existent pas'], 404);
    }
}

  /**
 * @OA\Post(
 *     path="/api/logement/preference/storeUnexist/{housingId}",
 *     summary="Create a new preference unexist",
 *     tags={"Housing Preference"},
 * security={{"bearerAuth": {}}},
 * @OA\Parameter(
     *         name="housingId",
     *         in="path",
     *         required=true,
     *         description="ID of the housing",
     *         @OA\Schema(type="integer")
     *     ),
 *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="proche de la plage,etc")
     *         )
     *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Preference created successfully"
 *     ),
 *   @OA\Response(
 *         response=404,
 *         description="Preference not found"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Invalid credentials"
 *     )
 * )
 */
public function storeUnexist(Request $request,$housingId)
{
    try{
            $request->validate([
                'name' => 'required|max:255',
            ]);
            $existingpreference = Preference::where('name', $request->name)->first();
            if ($existingpreference) {
           return response()->json(['error' => 'Le nom de la préférence existe déjà par défaut']);
             }
            $preference = new Preference();
            $preference->name = $request->name;
            $preference->is_verified = false;
            $preference->save();
            $existingAssociation = housing_preference::where('preference_id', $preference->id)
                ->where('housing_id', $housingId)
                ->exists();
                if ($existingAssociation) {
                    return response()->json([
                        "message" =>"La préférence existe déjà et a été affecté au logement indiquée",
                    ],200);
                }
            $housingPreference = new housing_preference();
            $housingPreference->housing_id = $housingId;
            $housingPreference->preference_id = $preference->id;
            $housingPreference->is_verified= false;
            // return response()->json('ok');
            $housingPreference->save();
            return response()->json(['data' => 'Type de preference créé avec succès.', 'preference' => $preference], 201);
    } catch(Exception $e) {    
        return response()->json($e->getMessage());
    }

}


   /**
 * @OA\Get(
 *     path="/api/logement/preference/ListHousingPreferenceInvalid/{housingId}",
 *     summary=" Liste des associations  logement preference  invalide ayant leur préférence déjà existante",
 *     tags={"Housing Preference"},
 * security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         required=true,
 *         description="ID of the housing",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of invalid housing preference",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="name", type="string"),
 *                     @OA\Property(property="is_deleted", type="boolean"),
 *                     @OA\Property(property="is_blocked", type="boolean"),
 *                     @OA\Property(property="is_verified", type="boolean"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time"),
 *                     @OA\Property(property="created_at", type="string", format="date-time"),
 *                     @OA\Property(property="icone", type="string")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Housing not found"
 *     )
 * )
 */
public function ListHousingPreferenceInvalid($housingId) {
    $housingPreferences = housing_preference::where('housing_id', $housingId)
                                            ->where('is_verified', false)
                                            ->get();
    $preferenceT = [];
    foreach ($housingPreferences as $preference) {
        if ($preference->preference->is_verified == true) {
            $preferenceT[] = [
                'id_housing_preference' => $preference->id,
                'id_preference' => $preference->preference->id,
                'id_housing' =>$housingId,
                'name' => $preference->preference->name,
                'is_deleted' => $preference->preference->is_deleted,
                'is_blocked' => $preference->preference->is_blocked,
                'is_verified' => $preference->is_verified,
                'updated_at' => $preference->preference->updated_at,
                'created_at' => $preference->preference->created_at,
                'icone' => $preference->preference->icone,
            ];
        }
    }

    return response()->json([
        "data" => $preferenceT
    ], 200);
}


/**
     * @OA\Post(
     *     path="/api/logement/preference/addPreferenceToHousing",
     *     summary="ajouter des préférences existantes à des logements",
     *     tags={"Housing Preference"},
     * security={{"bearerAuth": {}}},
* @OA\RequestBody(
*     required=true,
*     @OA\MediaType(
*       mediaType="application/json",
*       @OA\Schema(
*         type="object",
*         @OA\Property(property="housingId", type="string", example="2"),
*         @OA\Property(
*                     property="preferenceId",
*                     type="array",
*                     @OA\Items(type="integer"),
*                     description="Tableau contenant les IDs des préférences du logement à supprimer"
*                 )

*       )
*     )
*   ),
     *     @OA\Response(
     *         response=200,
     *         description="Preference  created successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     )
     * )
     */
    public function addPreferenceToHousing(Request $request){
        try{

            $e=[];
            $m=[];
             foreach ($request->input('preferenceId') as $preference) {
                    if (!Preference::find($preference)) {
                        return response()->json(['message' => 'un preference non trouvé'],404);
                    }
                $existingAssociation = housing_preference::where('housing_id',  $request->housingId)
                ->where('preference_id', $preference)
                ->exists();
                if ($existingAssociation) {
                    $e[] = [
                        Preference::find($preference)->name . ' existe déjà dans le logement',
                    ];
                }else{
                    $m[] = [
                        Preference::find($preference)->name . ' a ete avec succes au logement',
                    ];
                    $housingPreference = new housing_preference();
                    $housingPreference->housing_id = $request->housingId;
                    $housingPreference->preference_id = $preference;
                    $housingPreference->is_verified = false;
                    $housingPreference->save();
                }
            }

            $userId = Auth::id();
            $notification = new Notification([
                'name' => "Votre ajout de préférence(s) a été pris en compte. l'administrateur validera dans moin de 48h",
                'user_id' => $userId,
               ]);
               $notification->save();
             $adminUsers = User::where('is_admin', 1)->get();
                    foreach ($adminUsers as $adminUser) {
                        $notification = new Notification();
                        $notification->user_id = $adminUser->id;
                        $notification->name = "Un hôte  vient de faire un ajout de nouvelle(s) préférence(s) .Veuilez vous connecter pour valider";
                        $notification->save();
                    }
            
            return response()->json([
                "message" =>  empty($m) ? '  error' : $m,
                'error' => empty($e) ? ' no error' : $e
            ],200);
    } catch(Exception $e) {    
        return response()->json($e->getMessage(),);
    }
}


/**
* @OA\Post(
*     path="/api/logement/preference/makeVerifiedHousingPreference/{housingPreferenceId}",
*     summary="Valider les associations logement préférence invalide  et ayant leur préférence qui existe déjà",
*     tags={"Housing Preference"},
*security={{"bearerAuth":{}}},
*     @OA\Parameter(
*         name="housingPreferenceId",
*         in="path",
*         required=true,
*         description="ID of the housingPreference",
*         @OA\Schema(
*             type="integer"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Housing preference verified successfully",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(
*                 property="data",
*                 type="string",
*                 example="association equipement logement vérifié avec succès."
*             )
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Housing preference not found"
*     ),
*     @OA\Response(
*         response=400,
*         description="Housing preference already verified"
*     ),
*     @OA\Response(
*         response=500,
*         description="Internal server error"
*     )
* )
*/
public function makeVerifiedHousingPreference(string $housingPreferenceId)
{
try{
    $housingPreference = housing_preference::find($housingPreferenceId);
    if (!$housingPreference) {
        return response()->json(['error' => 'association preference logement  non trouvé.'], 404);
    }
    if ($housingPreference->is_verified == true) {
        return response()->json(['data' => 'association preference logement déjà vérifié.'], 200);
    }
    housing_preference::whereId($housingPreferenceId)->update(['is_verified' => true]);
    $notification = new Notification([
        'name' => "L'ajout de cette préférence : ".Preference::find($housingPreference->preference_id)->name." a été validé par l'administrateur",
        'user_id' =>$housingPreference->housing->user_id ,
       ]);
       $notification->save();
    return response()->json(['data' => 'association preference logement vérifié avec succès.'], 200);
} catch(Exception $e) {    
    return response()->json($e->getMessage(), 500);
}


}

/**
* @OA\Get(
*     path="/api/logement/preference/ListPreferenceForHousingInvalid/{housingId}",
*     summary="Liste des preferences inexistantes et  invalide pour un logement",
*     tags={"Housing Preference"},
*security={{"bearerAuth":{}}},
*     @OA\Parameter(
*         name="housingId",
*         in="path",
*         required=true,
*         description="ID of the housing",
*         @OA\Schema(
*             type="integer"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="List of invalid preferences",
*         @OA\JsonContent(
*             type="array",
*             @OA\Items()
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Housing not found"
*     )
* )
*/
public function ListPreferenceForHousingInvalid($housingId) {
$invalidPreferences = Housing::find($housingId)->housing_preference()->whereHas('preference', function ($query) {
    $query->where('is_verified', false);
})->get();
$preferenceT = [];
foreach ($invalidPreferences as $housingPreference) {
    $preference = $housingPreference->preference;
    $preferenceT[] = [
        'id_housing_preference' => $housingPreference->id,
                'id_preference' => $preference->id,
                'id_housing' =>$housingId,
                'name' => $preference->name,
                'is_deleted' => $preference->ps_deleted,
                'is_blocked' => $preference->is_blocked,
                'is_verified' => $preference->is_verified,
                'updated_at' => $preference->updated_at,
                'created_at' => $preference->created_at,
                'icone' => $preference->icone,
    ];
}

return response()->json([
    "data" => $preferenceT
], 200);
}

/**
* @OA\Get(
*     path="/api/logement/preference/getHousingPreferenceInvalid",
*     summary="Liste des association préférence logement invalides ayant leur préférence qui existe déjà",
*     tags={"Housing Preference"},
*security={{"bearerAuth":{}}},
*     @OA\Response(
*         response=200,
*         description="Liste des préférences logement invalides",
*         @OA\JsonContent(
*             type="array",
*             @OA\Items()
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Housing not found"
*     )
* )
*/
public function getHousingPreferenceInvalid(){
$preferences = Preference::where('is_verified', true)->get();

$data = [];
foreach($preferences as $preference){
    $housingPreferences = Housing_preference::where('preference_id', $preference->id)
    ->whereHas('housing',function($query){
        $query->where('is_verified',false);
    })
    ->with('housing')
    ->get();
    foreach ($housingPreferences as $housingPreference) {
        $housingId = $housingPreference->housing->id;
        $existingHousingIndex = null;

        foreach ($data as $index => $existingHousing) {
           if($existingHousing['housing_id'] === $housingId && $existingHousing['preference_id'] === $preference->id){
               $existingHousingIndex = $index;
               break;
           }
        }
        if($existingHousingIndex === null){
            $housingData = [
                'housing_preference_id' => $housingPreference->id,
                'preference_id' => $preference->id,
                'preference_name' => $preference->name,
                'housing_id' => $housingId,
                'housing_name' => $housingPreference->housing->name,
                'housing_description' => $housingPreference->housing->description,
                'is_verified' => $housingPreference->preference->is_verified,
                'created_at' => $housingPreference->preference->created_at,
                'updated_at' => $housingPreference->preference->updated_at,
                'user_id' => $housingPreference->housing->user->id,
                'user_firstname' => $housingPreference->housing->user->firstname,
                'user_lastname' => $housingPreference->housing->user->lastname
            ];
            $data[] = $housingData;
        }
    }
}
return response()->json(['data' => $data]);
}

  /**
* @OA\Get(
*     path="/api/logement/preference/getUnexistPreferenceInvalidForHousing",
*     summary="Liste des préférences inexistants non valide",
*     tags={"Housing Preference"},
*security={{"bearerAuth":{}}},
*     @OA\Response(
*         response=200,
*         description="Liste des préférences inexistants non valide",
*         @OA\JsonContent(
*             type="array",
*             @OA\Items()
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Housing not found"
*     )
* )
*/
public function getUnexistPreferenceInvalidForHousing(){
$preferences = Preference::where('is_verified', false)->get();

$data = [];
foreach($preferences as $preference){
    $housingPreferences = Housing_preference::where('preference_id', $preference->id)
    ->whereHas('housing',function($query){
        $query->where('is_verified',false);
    })
    ->with('housing')
    ->get();
    foreach ($housingPreferences as $housingPreference) {
        $housingId = $housingPreference->housing->id;
        $existingHousingIndex = null;

        foreach ($data as $index => $existingHousing) {
           if($existingHousing['housing_id'] === $housingId && $existingHousing['preference_id'] === $preference->id){
               $existingHousingIndex = $index;
               break;
           }
        }

        if($existingHousingIndex === null){
            $housingData = [
                'housing_preference_id' => $housingPreference->id,
                'preference_id' => $preference->id,
                'preference_name' => $preference->name,
                'housing_id' => $housingId,
                'housing_name' => $housingPreference->housing->name,
                'housing_description' => $housingPreference->housing->description,
                'is_verified' => $housingPreference->is_verified,
                'user_id' => $housingPreference->housing->user->id,
                'user_firstname' => $housingPreference->housing->user->firstname,
                'user_lastname' => $housingPreference->housing->user->lastname
            ];
            $data[] = $housingData;
        }
    }
}
return response()->json(['data' => $data]);
}

}

