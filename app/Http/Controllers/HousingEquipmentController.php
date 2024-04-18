<?php

namespace App\Http\Controllers;

use App\Models\Housing_equipment;
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
use App\Models\Housing_category_file;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;


class HousingEquipmentController extends Controller
{

/**
 * @OA\Get(
 *     path="/api/logement/{housingEquipmentId}/equipements",
 *     tags={"Housing Equipment"},
 *     summary="Récupérer les équipements associés à un logement donné",
 *     description="Récupère les équipements associés à un logement spécifié en fonction de Id housing_equipment.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="housingEquipmentId",
 *         in="path",
 *         description="L'ID du HousingEquipment pour lequel récupérer les équipements",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Succès - Les équipements associés au logement ont été récupérés avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             properties={
 *                 @OA\Property(
 *                     property="equipments",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         properties={
 *                           @OA\Property(
 *                                 property="id_housing",
 *                                 type="integer",
 *                                 description="L'ID du logement"
 *                             ),
 *                             @OA\Property(
 *                                 property="id_housing_equipment",
 *                                 type="integer",
 *                                 description="L'ID de HousingEquipment"
 *                             ),
 *                             @OA\Property(
 *                                 property="id_equipment",
 *                                 type="integer",
 *                                 description="L'ID de l'équipement"
 *                             ),
 *                             @OA\Property(
 *                                 property="name",
 *                                 type="string",
 *                                 description="Le nom de l'équipement"
 *                             ),
 *                             @OA\Property(
 *                                 property="is_verified",
 *                                 type="boolean",
 *                                 description="Indique si l'équipement est vérifié"
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
 *         description="Erreur interne du serveur - Impossible de récupérer les équipements associés au logement"
 *     )
 * )
 */
public function equipementsHousing($housingId)
{
    $housingEquipments = Housing_equipment::where('housing_id', $housingId)->get();

    if ($housingEquipments->isEmpty()) {
        return response()->json(['message' => 'Aucun équipement associé à ce logement'], 404);
    }

    $equipments = [];
    
    foreach ($housingEquipments as $housingEquipment) {
        $equipment = Equipment::find($housingEquipment->equipment_id);
        
        $equipments[] = [
            'id_housing' => $housingEquipment->housing_id,
            'id_housing_equipment' => $housingEquipment->id,
            'id_equipment' => $equipment->id,
            'name' => $equipment->name,
            'is_verified' => $equipment->is_verified,
        ];
    }

    return response()->json(['data' => $equipments], 200);
}
/**
 * @OA\Delete(
 *     path="/api/logement/equipement",
 *     tags={"Housing Equipment"},
 *     summary="Supprime des équipements associés à un logement",
 *     description="Supprime l'association entre plusieurs équipements et un logement à partir des IDs des associations.",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"housingEquipmentIds"},
 *                 @OA\Property(
 *                     property="housingEquipmentIds",
 *                     type="array",
 *                     @OA\Items(type="integer"),
 *                     description="Tableau contenant les IDs des équipements du logement à supprimer"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Les équipements du logement ont été retirés avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Les équipements du logement ont été retirés avec succès")
 *         )
 *     ),
 * )
 */
public function DeleteEquipementHousing(Request $request)
{
    try {
        $request->validate([
            'housingEquipmentIds' => 'required|array',
            'housingEquipmentIds.*' => 'integer|exists:housing_equipments,id',
        ]);

        $housingEquipmentIds = $request->input('housingEquipmentIds');

        Housing_equipment::whereIn('id', $housingEquipmentIds)->delete();

        return response()->json(['message' => 'Les équipements du logement ont été retirés avec succès'], 200);
    } catch (ValidationException $e) {
        return response()->json(['message' => 'Un ou plusieurs équipements du logement à retirer n\'existent pas'], 404);
    }
}


/**
         * @OA\Post(
         *     path="/api/logement/equipment/storeUnexist/{housingId}",
         *     summary="Create a new equipment what don't exist ",
         *     tags={"Housing Equipment"},
         * security={{"bearerAuth": {}}},
         *     @OA\Parameter(
     *         name="housingId",
     *         in="path",
     *         required=true,
     *         description="ID of the housing ",
     *         @OA\Schema(type="integer")
     *     ),
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(property="name", type="string", example="climatiseur"),
 *         @OA\Property(property="category_id", type="string", example="5"),
 *       )
 *     )
 *   ),
         *     @OA\Response(
         *         response=200,
         *         description="Equipment  created successfully"
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
                        // 'icone' => 'image|mimes:jpeg,jpg,png,gif'
                    ]);
                    $equipment  = new Equipment();
                    $equipment->name = $request->name;
                    $equipment->is_verified = false;
                    $equipment->save();
                    $equipment = Equipment::where('name', $request->name)->first();

                $existingAssociation = Equipment_category::where('equipment_id', $equipment->id)
                ->where('category_id', $request->category_id)
                ->exists();
                if ($existingAssociation) {
                    return response()->json([
                        "message" =>"L'equipement existe déjà et a été affecté à la catégorie indiquée",
                    ],200);
                }
                    $equipment_category = new equipment_category();
                    $equipment_category->equipment_id = $equipment->id;
                    $equipment_category->category_id = $request->category_id;
                    $equipment_category->save();
                    $housingEquipment = new Housing_equipment();
                    $housingEquipment->equipment_id = $equipment->id;
                    $housingEquipment->housing_id = $housingId;
                    $housingEquipment->save();
                    return response()->json([
                        "message" =>"save successfully",
                        "equipment" => $equipment
                    ],200);
            } catch(Exception $e) {
                return response()->json($e);
            }

        }

        /**
         * @OA\Post(
         *     path="/api/logement/equipment/addEquipmentToHousing",
         *     summary="add equipment to housing ",
         *     tags={"Housing Equipment"},
         * security={{"bearerAuth": {}}},
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="application/json",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(property="housingId", type="string", example="2"),
 *         @OA\Property(
 *                     property="equipmentId",
 *                     type="array",
 *                     @OA\Items(type="integer"),
 *                     description="Tableau contenant les IDs des équipements du logement à supprimer"
 *                 )

 *       )
 *     )
 *   ),
         *     @OA\Response(
         *         response=200,
         *         description="Equipment  created successfully"
         *     ),
         *     @OA\Response(
         *         response=401,
         *         description="Invalid credentials"
         *     )
         * )
         */
    public function addEquipmentToHousing(Request $request){
        try{

            
            $e=[];
            $m=[];
             foreach ($request->input('equipmentId') as $equipment) {
                $existingAssociation = housing_equipment::where('housing_id',  $request->housingId)
                ->where('equipment_id', $equipment)
                ->exists();
                if ($existingAssociation) {
                    $e[] = [
                        Equipment::find($equipment)->name . ' existe déjà dans le logement',
                    ];
                }else{
                    $m[] = [
                        Equipment::find($equipment)->name . ' a ete avec succes au logement',
                    ];
                    $housingEquipment = new housing_equipment();
                    $housingEquipment->housing_id = $request->housingId;
                    $housingEquipment->equipment_id = $equipment;
                    $housingEquipment->save();
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
