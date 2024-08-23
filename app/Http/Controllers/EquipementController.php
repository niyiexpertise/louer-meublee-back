<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationEmail;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Notification;
use App\Models\Equipment_category;
use App\Models\Housing_equipment;
use App\Models\EquipmentCategory;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as DatabaseEloquentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmail;
use App\Mail\NotificationEmailwithoutfile;
use App\Services\FileService;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File as F ;
use Illuminate\Validation\ValidationException ;
use Illuminate\Validation\Rule;
class EquipementController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }
        /**
     * @OA\Get(
     *     path="/api/equipment/VerifiedBlocknotDelete",
     *     summary="Get all equipments (verified, blocked, not deleted)",
     *     tags={"Equipment"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of equipments with categories"
     *     )
     * )
     */
    public function VerifiedBlocknotDelete()
    {
        try{
                    $equipmentsWithCategories = [];
                    // $equipmentCategories = EquipmentCategory::where('is_deleted', false)->get();
                    $equipmentCategories = Equipment_category::with('equipment')
                    ->whereHas('equipment', function (DatabaseEloquentBuilder $query) {
                    $query->where('is_verified',true)->where('is_blocked', true)->where('is_deleted', false);
                    })
                    ->get();

                    foreach ($equipmentCategories as $equipmentCategory) {
                        $equipment = $equipmentCategory->equipment;
                        $category = $equipmentCategory->category;
                        $index = array_search($equipment->id, array_column($equipmentsWithCategories, 'id'));
                        if ($index === false) {
                            $equipmentsWithCategories[] = [
                                'id' => $equipment->id,
                                'name' => $equipment->name,
                                'icone' => $equipment->equipment->icone,
                                'is_deleted' => $equipment->is_deleted,
                                'is_blocked' => $equipment->is_blocked,
                                'is_verified' => $equipment->is_verified,
                                'updated_at' => $equipment->updated_at,
                                'created_at' => $equipment->created_at,
                                 'equipment_category' => $equipment->equipment_category(),
                                'categories' => [$category],
                            ];
                        } else {
                            $equipmentsWithCategories[$index]['categories'][] = $category;
                        }
                    }
                    return response()->json([
                        'data' => $equipmentsWithCategories
                    ]);

        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }

    }

        /**
     * @OA\Get(
     *     path="/api/equipment/VerifiednotBlocknotDelete",
     *     summary="Get all equipments (verified, not blocked, not deleted)",
     *     tags={"Equipment"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of equipments with categories"
     *     )
     * )
     */
    public function VerifiednotBlocknotDelete()
    {
        try{
                    $equipmentsWithCategories = [];
                    // $equipmentCategories = EquipmentCategory::where('is_deleted', false)->get();
                    $equipmentCategories = Equipment_category::with('equipment')
                    ->whereHas('equipment', function (DatabaseEloquentBuilder $query) {
                    $query->where('is_verified',true)->where('is_blocked', false)->where('is_deleted', false);
                    })
                    ->get();

                    foreach ($equipmentCategories as $equipment) {
                        $equipmentsWithCategories[] = [
                            'id' => $equipment->equipment->id,
                            'name' => $equipment->equipment->name,
                            'icone' => $equipment->equipment->icone,
                            'is_deleted' => $equipment->equipment->is_deleted,
                            'is_blocked' => $equipment->equipment->is_blocked,
                            'updated_at' => $equipment->equipment->updated_at,
                            'created_at' => $equipment->equipment->created_at,
                            'category' => $equipment->category,
                            'equipmentCategory' => Equipment_category::where([
                                'equipment_id' => $equipment->equipment->id,
                                'category_id' => $equipment->category->id
                            ])->get(),
                        ];
                    }
                    return response()->json([
                        'data' => $equipmentsWithCategories
                    ]);

        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }

            /**
     * @OA\Get(
     *     path="/api/equipment/VerifiednotBlockDelete",
     *     summary="Get all equipments (verified, not blocked,  deleted)",
     *     tags={"Equipment"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of equipments with categories"
     *     )
     * )
     */
    public function VerifiednotBlockDelete()
    {
        try{
                    $equipmentsWithCategories = [];
                    // $equipmentCategories = EquipmentCategory::where('is_deleted', false)->get();
                    $equipmentCategories = Equipment_category::with('equipment')
                    ->whereHas('equipment', function (DatabaseEloquentBuilder $query) {
                    $query->where('is_verified',true)->where('is_blocked', false)->where('is_deleted', true);
                    })
                    ->get();

                    foreach ($equipmentCategories as $equipmentCategory) {
                        $equipment = $equipmentCategory->equipment;
                        $category = $equipmentCategory->category;
                        $index = array_search($equipment->id, array_column($equipmentsWithCategories, 'id'));
                        if ($index === false) {
                            $equipmentsWithCategories[] = [
                                'id' => $equipment->id,
                                'name' => $equipment->name,
                                'is_deleted' => $equipment->is_deleted,
                                'is_blocked' => $equipment->is_blocked,
                                'icone' => $equipment->equipment->icone,
                                'is_verified' => $equipment->is_verified,
                                'updated_at' => $equipment->updated_at,
                                'created_at' => $equipment->created_at,
                                 'equipment_category' => $equipment->equipment_category(),
                                'categories' => [$category],
                            ];
                        } else {
                            $equipmentsWithCategories[$index]['categories'][] = $category;
                        }
                    }
                    return response()->json([
                        'data' => $equipmentsWithCategories
                    ]);

        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }

    }

       /**
     * @OA\Get(
     *     path="/api/equipment/indexUnverified",
     *     summary="Get all equipments unverified",
     *     tags={"Equipment"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of equipments with categories"
     *     )
     * )
     */
    public function indexUnverified()
    {
        try{
            $equipmentsWithCategories = [];
            $equipmentCategories = Equipment_category::with('equipment')
                ->whereHas('equipment', function ($query) {
                    $query->where('is_deleted', false)->where('is_verified', false);
                })
                ->get();

                foreach ($equipmentCategories as $equipment) {
                    $equipmentsWithCategories[] = [
                        'id' => $equipment->equipment->id,
                        'name' => $equipment->equipment->name,
                        'is_deleted' => $equipment->equipment->is_deleted,
                        'is_blocked' => $equipment->equipment->is_blocked,
                        'updated_at' => $equipment->equipment->updated_at,
                        'created_at' => $equipment->equipment->created_at,

                        'category' => $equipment->category,
                        'equipmentCategory' => Equipment_category::where([
                            'equipment_id' => $equipment->equipment->id,
                            'category_id' => $equipment->category->id
                        ])->get(),
                    ];
                }

            return response()->json([
                'data' => $equipmentsWithCategories
            ]);


        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }

    }








    /**
         * @OA\Post(
         *     path="/api/equipment/store",
         *     summary="Create a new equipment ",
         *     tags={"Equipment"},
         * security={{"bearerAuth": {}}},
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(property="name", type="string", example="climatiseur"),
 *         @OA\Property(property="category_id", type="string", example="5"),
 *         @OA\Property(
 *           property="icone",
 *           type="string",
 *           format="binary",
 *           description="Image de profil d'identité (JPEG, PNG, JPG, GIF, taille max : 2048)"
 *         ),
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
        public function store(Request $request)
        {
            try{
                $data = $request->validate([
                    'name' => 'required|max:255',
                    // 'icone' => 'image|mimes:jpeg,jpg,png,gif'
                ]);
                $existingequipment = Equipment::where('name', $request->name)->first();
          if ($existingequipment) {
            return response()->json(['error' => 'Le nom de l\'équipement existe déjà par défaut'], 400);
            }
            $equipment = Equipment::where('name', $request->name)->first();

            $existingcategory = Category::where('id',$request->category_id)
            ->exists();
            if (!$existingcategory) {
                return response()->json([
                    "message" =>"La catégorie associée à l'équipement n'existe pas ou a déjà été supprimée",
                ],200);
            }

                $equipment  = new Equipment();
                if ($request->hasFile('icone')) {
                    $icone_name = uniqid() . '.' . $request->file('icone')->getClientOriginalExtension();
                    $identity_profil_path = $request->file('icone')->move(public_path('image/iconeEquipment'), $icone_name);
                    $base_url = url('/');
                    $icone_url = $base_url . '/image/iconeEquipment/' . $icone_name;
                    $equipment->icone = $icone_url;
                    }

                $equipment->name = $request->name;
                $equipment->is_verified = true;
                $equipment->save();
                $equipment = Equipment::where('name', $request->name)->first();

                $equipment_category = new equipment_category();
                $equipment_category->equipment_id = $equipment->id;
                $equipment_category->category_id = $request->category_id;
                $equipment_category->save();
                return response()->json([
                    "message" =>"save successfully",
                    "equipment" => $equipment
                ],200);
                } catch(Exception $e) {
                      return response()->json(['error' => $e->getMessage()], 500);
                }

        }


        /**
     * @OA\Get(
     *     path="/api/equipment/show/{id}",
     *     summary="Get information about a specific equipment",
     *     tags={"Equipment"},
     * security={{"bearerAuth": {}}},
     *  @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the equipment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Information about the specified equipment ",
     *
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Equipment not found for the specified ",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Équipement introuvable .")
     *         )
     *     )
     * )
     */

        public function show($id)
    {
        try{
                $equipment = Equipment::find($id);
                // $equipment_category = EquipmentCategory::where('equipment_id',$id)->get();
                if (!$equipment) {
                    return response()->json(['error' => 'Équipement introuvable pour cette catégorie.'], 404);
                }


                return response()->json(['data' => $equipment], 200);
        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }

    }


    /**
     * @OA\Put(
     *     path="/api/equipment/updateName/{id}",
     *     summary="Update an equipment name by ID",
     *     tags={"Equipment"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the equipment to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description","category_id"},
     *             @OA\Property(property="name", type="string", example="New Equipment Name"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Equipment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Equipment updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Equipment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Equipment not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     */
    public function updateName(Request $request, string $id)
    {
        try{
                $equipment = Equipment::whereId($id)->first();

                if (!$equipment) {
                    return response()->json(['error' => 'Equipement non trouvé.'], 404);
                }

                $data = $request->validate([
                    'name' => [
                        'required',
                        'string',
                        Rule::unique('equipment')->ignore($id),
                    ],
                ]);

               Equipment::whereId($id)->update($data);

                return response()->json(['data' => 'nom de l\'équipement mis à jour avec succès.'], 200);
        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }

     /**
     * @OA\Put(
     *     path="/api/equipment/updateCategory/{id}",
     *     summary="Update an equipment category by ID",
     *     tags={"Equipment"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the equipment to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description","category_id"},
     *             @OA\Property(property="category_id", type="string", example=2),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Equipment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Equipment updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Equipment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Equipment not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     */
    public function updateCategory(Request $request, string $equipmentCategory)
    {
        try{
                $equipment = Equipment_category::find($equipmentCategory);

                if (!$equipment) {
                    return response()->json(['error' => 'Relation equipmentCategory non trouvé.'], 404);
                }

               Equipment_category::whereId($equipmentCategory)->update(['category_id' => $request->category_id]);


                return response()->json(['data' => 'nom de l\'équipement mis à jour avec succès.'], 200);
        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }

     /**
     * @OA\Post(
     *     path="/api/equipment/updateIcone/{id}",
     *     summary="Update an equipment icone by ID",
     *     tags={"Equipment"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the equipment to update",
     *         @OA\Schema(type="integer")
     *     ),
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(
 *           property="icone",
 *           type="string",
 *           format="binary",
 *           description="Image de profil d'identité (JPEG, PNG, JPG, GIF, taille max : 2048)"
 *         ),
 *       )
 *     )
 *   ),
     *     @OA\Response(
     *         response=200,
     *         description="Equipment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Equipment updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Equipment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Equipment not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     */
    public function updateIcone(Request $request, string $id)
    {

        try {
            $equipment = Equipment::find($id);

            if (!$equipment) {
                return response()->json(['error' => 'Equipement non trouvé.'], 404);
            }

            $oldProfilePhotoUrl = $equipment->icone;
            if ($oldProfilePhotoUrl) {
                $parsedUrl = parse_url($oldProfilePhotoUrl);
                $oldProfilePhotoPath = public_path($parsedUrl['path']);
                if (F::exists($oldProfilePhotoPath)) {
                    F::delete($oldProfilePhotoPath);
                }
            }

                if ($request->hasFile('icone')) {
                    $icone_name = uniqid() . '.' . $request->file('icone')->getClientOriginalExtension();
                    $icone_path = $request->file('icone')->move(public_path('image/iconeEquipment'), $icone_name);
                    $base_url = url('/');
                    $icone_url = $base_url . '/image/iconeEquipment/' . $icone_name;

                    Equipment::whereId($id)->update(['icone' => $icone_url]);

                    return response()->json(['data' => 'icône de l\'équipement mis à jour avec succès.'], 200);
                } else {
                // dd("h");
                return response()->json(['error' => 'Aucun fichier d\'icône trouvé dans la requête.'], 400);
            }
        } catch (QueryException $e) {
            return response()->json(['error' => 'Erreur de requête SQL: ' . $e->getMessage()], 500);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


        /**
     * @OA\Delete(
     *     path="/api/equipment/destroy/{id}",
     *     summary="Delete an equipment by ID",
     *     tags={"Equipment"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the equipment to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Equipment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Equipment deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Equipment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Equipment not found")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
{
    try {
        $equipment = Equipment::find($id);

        if (!$equipment) {
            return response()->json(['error' => 'Équipement non trouvé.'], 200);
        }

        $associatedHousing = Housing_equipment::where('equipment_id', $id)->count();

        if ($associatedHousing > 0) {
            return response()->json(['error' => "Suppression impossible car l'équipement est déjà associé à un logement."], 200);

        }

        $equipment->update(['is_deleted' => true]);

        return response()->json(['data' => 'Équipement supprimé avec succès.'], 200);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    /**
     * @OA\Put(
     *     path="/api/equipment/block/{id}",
     *     summary="Block an equipment",
     *     tags={"Equipment"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the equipment to block",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Equipment successfully blocked",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Equipment successfully blocked")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Equipment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Equipment not found")
     *         )
     *     )
     * )
     */
    public function block(string $id)
    {
        try{
                // Récupérer l'équipement à bloquer
                $equipment = Equipment::find($id);

                // Vérifier si l'équipement existe
                if (!$equipment) {
                    return response()->json(['error' => 'Équipement non trouvé.'], 404);
                }

                Equipment::whereId($id)->update(['is_blocked' => true]);

                // Retourner une réponse JSON pour indiquer que l'équipement a été bloqué avec succès
                return response()->json(['data' => 'Équipement bloqué avec succès.'], 200);
        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    /**
     * @OA\Put(
     *     path="/api/equipment/unblock/{id}",
     *     summary="Unblock an equipment",
     *     tags={"Equipment"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the equipment to unblock",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Equipment successfully unblocked",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Equipment successfully unblocked")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Equipment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Equipment not found")
     *         )
     *     )
     * )
     */
    public function unblock(string $id)
    {
        try{
                $equipment = Equipment::find($id);
                if (!$equipment) {
                    return response()->json(['error' => 'Équipement non trouvé.'], 404);
                }
                Equipment::whereId($id)->update(['is_blocked' => false]);

                return response()->json(['data' => 'Équipement débloqué avec succès.'], 200);
        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }


    }

    /**
     * @OA\Put(
     *     path="/api/equipment/makeVerified/{id}",
     *     summary="make verified an equipment",
     *     tags={"Equipment"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the equipment to verified",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Equipment successfully verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Equipment successfully verified")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Equipment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Equipment not found")
     *         )
     *     )
     * )
     */
    public function makeVerified(string $id)
{
    try {

        $equipment = Equipment::find($id);

        if (!$equipment) {
            return response()->json(['error' => 'Équipement non trouvé.'], 404);
        }

        if ($equipment->is_verified) {
            return response()->json(['data' => 'Équipement déjà vérifié.'], 200);
        }

        $equipment->update(['is_verified' => true]);

        $housingEquipment = Housing_equipment::where('equipment_id', $id)->first();

        if ($housingEquipment) {
            $housingEquipment->update(['is_verified' => true]);

            $mail = [
                'title' => "Validation du nouvel équipement ajouté au logement",
                'body' => "L'ajout de cet équipement : " . $equipment->name . " a été validé par l'administrateur.",
            ];

            dispatch( new SendRegistrationEmail($housingEquipment->housing->user->email, $mail['body'], $mail['title'], 2));
        }

        return response()->json(['data' => 'Équipement vérifié avec succès.'], 200);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

/**
 * @OA\Get(
 *     path="/api/equipment/all",
 *     summary="Get all unique equipments (not blocked, not deleted)",
 *     tags={"Equipment"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of all unique equipments without duplicates"
 *     )
 * )
 */
public function allEquipments()
{
    try {
        // Récupération des équipements non bloqués et non supprimés, avec des noms uniques
        $equipments = Equipment::where('is_blocked', false)
            ->where('is_deleted', false)
            ->get()
            ->unique('name');

        $equipmentList = $equipments->map(function($equipment) {
            return [
                'id' => $equipment->id,
                'name' => $equipment->name,
                'icone' => $equipment->icone ?? null,  // Valeur par défaut si icône est null
                
            ];
        })->values();  // Utilisation de values() pour réindexer le tableau (au cas où)

        // Retourne la réponse JSON avec le statut HTTP 200
        return response()->json([
            'data' => $equipmentList
        ], 200);

    } catch (Exception $e) {
        // Retourne une erreur 500 en cas d'exception
        return response()->json(['error' => $e->getMessage()], 500);
    }
}




}
