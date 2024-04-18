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
 *     path="/api/preference/storeUnexist/{housingId}",
 *     summary="Create a new preference",
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
                'name' => 'required|unique:preferences|max:255',
            ]);
            $preference = new Preference();
            $preference->name = $request->name;
            $preference->is_verified = false;
            $preference->save();
            $housingPreference = new housing_preference();
            $housingPreference->housing_id = $housingId;
            $housingPreference->preference_id = $preference->id;
            // return response()->json('ok');
            $housingPreference->save();
            return response()->json(['data' => 'Type de preference créé avec succès.', 'preference' => $preference], 201);
    } catch(Exception $e) {    
        return response()->json($e);
    }

}

   /**
         * @OA\Post(
         *     path="/api/preference/addPreferenceToHousing",
         *     summary="add preference to housing ",
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
                        $housingPreference->save();
                    }
                }
              
                return response()->json([
                    "message" =>  empty($m) ? '  error' : $m,
                    'error' => empty($e) ? ' no error' : $e
                ],200);
        } catch(Exception $e) {    
            return response()->json($e);
        }
    }

   

}
