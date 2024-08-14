<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationEmail;
use App\Models\Housing_equipment;
use Illuminate\Http\Request;
use App\Models\Housing;
use App\Models\housing_preference;
use App\Models\reduction;
use App\Models\promotion;
use App\Models\Category;
use App\Models\photo;
use App\Models\housing_price;
use App\Models\File;
use App\Models\Notification;
use App\Models\User;
use App\Models\Equipment;
use App\Models\Equipment_category;
use App\Models\Equipment_equipment;
use App\Models\Housing_category_file;
use App\Models\Housing_equipment_file;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmail;
use App\Mail\NotificationEmailwithoutfile;
use App\Models\User_right;
use App\Models\Right;
use Illuminate\Support\Facades\DB;

class HousingEquipmentController extends Controller
{

/**
 * @OA\Get(
 *     path="/api/logement/{housingId}/equipements",
 *     tags={"Housing Equipment"},
 *     summary="Récupérer les équipements associés à un logement donné",
 *     description="Récupère les équipements associés à un logement spécifié en fonction de housing_id.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         description="L'ID du Housingpour lequel récupérer les équipements",
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

    $equipments_by_category = $housingEquipments
        ->groupBy('category_id')
        ->map(function ($equipments_in_category, $category_id) {
            $category = Category::find($category_id);
            return [
                'category_id' => $category_id,
                'category_name' => $category ? $category->name : 'Sans catégorie',
                'equipments' => $equipments_in_category->map(function ($housingEquipment) {
                    $equipment = Equipment::find($housingEquipment->equipment_id);
                    return [
                        'id_housing' => $housingEquipment->housing_id,
                        'id_housing_equipment' => $housingEquipment->id,
                        'id_equipment' => $equipment->id,
                        'name' => $equipment ? $equipment->name : 'Inconnu',
                        'is_verified' => $equipment ? $equipment->is_verified : false,
                    ];
                })->toArray(),
            ];
        });

    return response()->json(['data' => $equipments_by_category->values()->toArray()], 200);
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
                    $existingequipment = Equipment::where('name', $request->name)->first();
             if ($existingequipment) {
            return response()->json(['error' => 'Le nom de l\'équipement existe déjà par défaut'], 400);
              }
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
                    $equipment_category = new Equipment_category();
                    $equipment_category->equipment_id = $equipment->id;
                    $equipment_category->category_id = $request->category_id;
                    $equipment_category->save();
                    $existingequipment = Equipment::where('name', $request->name)->first();
                    $existingAssociation = Housing_equipment::where('equipment_id', $equipment->id)
                    ->where('housing_id', $housingId)
                    ->exists();
                    if ($existingAssociation) {
                        return response()->json([
                            "message" =>"L'equipement existe déjà et a été affecté à au logement indiquée",
                        ],200);
                    }
                    $housingEquipment = new Housing_equipment();
                    $housingEquipment->equipment_id = $equipment->id;
                    $housingEquipment->category_id = $request->category_id;
                    $housingEquipment->housing_id = $housingId;
                    $housingEquipment->is_verified = false;
                    $housingEquipment->save();
                    $userId = Auth::id();
                   
                    $mailhote = [
                        "title" => "Notification ajout d'un équipement à un logement",
                        "body" => "L'enregistrement de ce nouvel  équipement a été pris en compte.l'administrateur validera dans moin de 48h."
                     ];

                    dispatch( new SendRegistrationEmail(Auth::user()->email, $mailhote['body'], $mailhote['title'], 2));
                
                     $right = Right::where('name','admin')->first();
                     $adminUsers = User_right::where('right_id', $right->id)->get();
                     foreach ($adminUsers as $adminUser) {
                     $mail = [
                        'title' => "Notification sur l'ajout d'un nouveau équipement",
                        'body' => "Un hôte  vient d'enregistrer un nouvel équipement'.Veuilez vous connecter pour valider.",
                    ];

                     dispatch( new SendRegistrationEmail($adminUser->user->email, $mail['body'], $mail['title'], 2));
                       }

                    return response()->json([
                        "message" =>"save successfully",
                        "equipment" => $equipment
                    ],200);
            } catch(Exception $e) {
                return response()->json($e->getMessage());
            }

        }


        /**
         * @OA\Post(
         *     path="/api/logement/equipment/addEquipmentToHousing",
         *     summary="ajouter des équipements existants à un logement donné ",
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
 *                 ),
 * *         @OA\Property(
 *                     property="categoryId",
 *                     type="array",
 *                     @OA\Items(type="integer"),
 *                     description="Tableau contenant les IDs des categories des équipements du logement à supprimer"
 *                 ),
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
        public function addEquipmentToHousing(Request $request)
        {
            try {

                if (count($request->input('equipmentId')) !== count($request->input('categoryId'))) {
                    return response()->json(['message' => 'Les équipements et les catégories doivent avoir le même nombre d\'éléments.'], 400);
                }
        
                $equipmentIds = $request->input('equipmentId');
                $categoryIds = $request->input('categoryId');
                
                $m = [];
                $e = [];
        
                foreach ($equipmentIds as $index => $equipmentId) {
                    $categoryId = $categoryIds[$index];
        
                    if (!Equipment::find($equipmentId)) {
                        return response()->json(['message' => 'L\'équipement avec ID ' . $equipmentId . ' n\'a pas été trouvé.'], 404);
                    }
        
                    $existingAssociation = housing_equipment::where('housing_id', $request->housingId)
                        ->where('equipment_id', $equipmentId)
                        ->where('category_id', $categoryId)
                        ->exists();
                    
                    if ($existingAssociation) {
                        $e[] = Equipment::find($equipmentId)->name . ' existe déjà dans la catégorie ' . Category::find($categoryId)->name;
                    } else {
                        $m[] = Equipment::find($equipmentId)->name . ' a été ajouté avec succès au logement.';
        
                        $housingEquipment = new housing_equipment();
                        $housingEquipment->housing_id = $request->housingId;
                        $housingEquipment->equipment_id = $equipmentId;
                        $housingEquipment->category_id = $categoryId;
                        $housingEquipment->is_verified = false;
                        $housingEquipment->save();
                    }
                }
        
                $userId = Auth::id();
                    
                    $mailhote = [
                        "title" => "Notification ajout d'un équipement à un logement",
                        "body" => "L'enregistrement de ce nouvel  équipement a été pris en compte.l'administrateur validera dans moin de 48h."
                     ];

                    dispatch( new SendRegistrationEmail(Auth::user()->email, $mailhote['body'], $mailhote['title'], 2));
                
                     $right = Right::where('name','admin')->first();
                     $adminUsers = User_right::where('right_id', $right->id)->get();
                     foreach ($adminUsers as $adminUser) {
                    
                
                     $mail = [
                        'title' => "Notification sur l'ajout d'un nouveau équipement",
                        'body' => "Un hôte  vient d'enregistrer un nouvel équipement'.Veuilez vous connecter pour valider.",
                    ];
                     dispatch( new SendRegistrationEmail($adminUser->user->email, $mail['body'], $mail['title'], 2));
                       }
        
                return response()->json([
                    'message' => empty($m) ? 'Erreur' : $m,
                    'error' => empty($e) ? 'Pas d\'erreur' : $e,
                ], 200);
                
            } catch (Exception $ex) {
                return response()->json(['message' => 'Erreur interne du serveur'], 500);
            }
        }
        


/**
 * @OA\Get(
 *     path="/api/logement/equipment/ListHousingEquipmentInvalid/{housingId}",
 *     summary="Liste des associations équipements logement invalides ayant leur équipement qui existe déjà",
 *     tags={"Housing Equipment"},
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
 *         description="List of invalid housing equipment",
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
public function ListHousingEquipmentInvalid($housingId)
{
    // Récupérer tous les équipements qui ne sont pas vérifiés pour un logement donné
    $housingEquipments = Housing_equipment::where('housing_id', $housingId)
                                          ->where('is_verified', false)
                                          ->get();
    $equipmentT = [];
    foreach ($housingEquipments as $housingEquipment) {

        $equipment = $housingEquipment->equipment;

        if ($equipment->is_verified == true) {
            $category = Category::find($housingEquipment->category_id);

            $equipmentT[] = [
                'id_housing_equipment' => $housingEquipment->id,
                'housing_id' => $housingId,
                'equipment_id' => $equipment->id,
                'name' => $equipment->name,
                'category_id' => $category->id,
                'category_name' => $category->name,
                'is_deleted' => $equipment->is_deleted,
                'is_blocked' => $equipment->is_blocked,
                'equipement_is_verified' => $equipment->is_verified,
                'updated_at' => $equipment->updated_at,
                'created_at' => $equipment->created_at,
                'icone' => $equipment->icone,
                'housing_equipment_is_verified' => $housingEquipment->is_verified,
            ];
        }
    }

    return response()->json([
        'data' => $equipmentT,
    ], 200);
}



/**
 * @OA\Post(
 *     path="/api/logement/equipment/makeVerifiedHousingEquipment/{housingEquipmentId}",
 *     summary="Valider une association équipement logement et dont l'équipement existe déjà",
 *     tags={"Housing Equipment"},
 * security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="housingEquipmentId",
 *         in="path",
 *         required=true,
 *         description="ID of the housing equipment association",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Housing equipment verified successfully",
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
 *         description="Housing equipment not found"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Housing equipment already verified"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error"
 *     )
 * )
 */
public function makeVerifiedHousingEquipment(string $id)
{
    try{
        $housingEquipment = Housing_equipment::find($id);
        if (!$housingEquipment) {
            return response()->json(['error' => 'association equipement logement  non trouvé.'], 404);
        }
        if ($housingEquipment->is_verified == true) {
            return response()->json(['data' => 'association equipement logement déjà vérifié.'], 200);
        }
        Housing_equipment::whereId($id)->update(['is_verified' => true]);

              $mail = [
                'title' => "Notification sur la validation du nouveau équipement ajouté au logement",
                'body' => "L'ajout de cet équipement : ".Equipment::find($housingEquipment->equipment_id)->name." a été validé par l'administrateur",
            ];
            
            dispatch( new SendRegistrationEmail($housingEquipment->housing->user->email, $mail['body'], $mail['title'], 2));

        return response()->json(['data' => 'association equipement logement vérifié avec succès.'], 200);
    } catch(Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
    }


}

/**
 * @OA\Get(
 *     path="/api/logement/equipment/ListEquipmentForHousingInvalid/{housingId}",
 *     summary="Liste des équipements inexistants et invalides pour un logement donné",
 *     tags={"Housing Equipment"},
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
 *         description="List of invalid equipment",
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


 public function ListEquipmentForHousingInvalid($housingId)
 {
     // Récupérer les équipements non vérifiés associés à un logement
     $invalidEquipments = Housing::find($housingId)
                ->housing_equipment()
                ->whereHas('equipment', function ($query) {
                    $query->where('is_verified', false);
                                 })
                ->get();
 
     $equipmentT = [];
     foreach ($invalidEquipments as $housingEquipment) {

         $equipment = $housingEquipment->equipment;
         
         $category = Category::find($housingEquipment->category_id);
         
         $equipmentT[] = [
             'equipment_id' => $equipment->id,
             'housing_id' => $housingId,
             'name' => $equipment->name,
             'user_detail' => $housingEquipment->housing->user,
             'is_verified' => $equipment->is_verified,
             'is_deleted' => $equipment->is_deleted,
             'is_blocked' => $equipment->is_blocked,
             'created_at' => $equipment->created_at,
             'updated_at' => $equipment->updated_at,
             'category_id' =>$category->id,
             'category_name' => $category->name
         ];
     }
 
     return response()->json([
         "data" => $equipmentT,
     ], 200);
 }
 


/**
 * @OA\Get(
 *     path="/api/logement/equipment/getHousingEquipmentInvalid",
 *     summary="Liste des associations equipement logement invalides avec les équipements existant par défaut",
 *     tags={"Housing Equipment"},
 * security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des associations equipement logement invalides avec les équipements existant par défaut",
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
public function getHousingEquipmentInvalid()
{

    $equipments = Equipment::where('is_verified', true)->get();

    $data = [];

    foreach ($equipments as $equipment) {

        $housingEquipments = Housing_equipment::where('equipment_id', $equipment->id)
            ->whereHas('housing', function ($query) {
                $query->where('is_verified', false);
            })
            ->with(['housing', 'category']) 
            ->get();

        foreach ($housingEquipments as $housingEquipment) {

            $housingId = $housingEquipment->housing->id;
            $existingHousingIndex = null;

            foreach ($data as $index => $existingHousing) {
                if ($existingHousing['housing_id'] === $housingId &&
                    $existingHousing['equipment_id'] === $equipment->id) {
                    $existingHousingIndex = $index;
                    break;
                }
            }

            if ($existingHousingIndex === null) {
                $category = Category::find($housingEquipment->category_id);
                
                $housingData = [
                    'housing_equipment_id' => $housingEquipment->id,
                    'equipment_id' => $equipment->id,
                    'equipment_name' => $equipment->name,
                    'housing_id' => $housingId,
                    'housing_name' => $housingEquipment->housing->name,
                    'housing_description' => $housingEquipment->housing->description,
                    'is_verified' => $housingEquipment->equipment->is_verified,
                    'created_at' => $housingEquipment->equipment->created_at,
                    'updated_at' => $housingEquipment->equipment->updated_at,
                    'user_detail' => $housingEquipment->housing->user,
                    'user_firstname' => $housingEquipment->housing->user->firstname,
                    'user_lastname' => $housingEquipment->housing->user->lastname,
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                ];
                $data[] = $housingData;
            }
        }
    }

    return response()->json(['data' => $data]);
}



             /**
 * @OA\Get(
 *     path="/api/logement/equipment/getUnexistEquipmentInvalidForHousing",
 *     summary="Liste des équipements inexistants non valide",
 *     tags={"Housing Equipment"},
 * security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des équipements inexistants non valide",
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
public function getUnexistEquipmentInvalidForHousing()
{
    $equipments = Equipment::where('is_verified', false)->get();

    $data = [];

    foreach ($equipments as $equipment) {
        $housingEquipments = Housing_equipment::where('equipment_id', $equipment->id)
            ->whereHas('housing', function ($query) {
                $query->where('is_verified', false);
            })
            ->with(['housing', 'category'])
            ->get();

        foreach ($housingEquipments as $housingEquipment) {
            $housingId = $housingEquipment->housing->id;
            $existingHousingIndex = null;

            foreach ($data as $index => $existingHousing) {
                if ($existingHousing['housing_id'] === $housingId &&
                    $existingHousing['equipment_id'] === $equipment->id) {
                    $existingHousingIndex = $index;
                    break;
                }
            }

            if ($existingHousingIndex === null) {
                $category = Category::find($housingEquipment->category_id);
                
                $housingData = [
                    'housing_equipment_id' => $housingEquipment->id,
                    'equipment_id' => $equipment->id,
                    'equipment_name' => $equipment->name,
                    'housing_id' => $housingId,
                    'housing_name' => $housingEquipment->housing->name,
                    'housing_description' => $housingEquipment->housing->description,
                    'is_verified' => $housingEquipment->is_verified,
                    'user_detail' =>$housingEquipment->housing->user,
                    'category_id' => $category->id,
                    'category_name' =>$category->name, 
                ];
                $data[] = $housingData;
            }
        }
    }

    return response()->json(['data' => $data]);
}

}