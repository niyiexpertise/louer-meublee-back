<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Equipment_category;
use App\Models\EquipmentCategory;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as DatabaseEloquentBuilder;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\QueryException;

class EquipementController extends Controller
{


    /**
     * @OA\Get(
     *     path="/api/equipment/indexAdmin",
     *     summary="Get all equipments for admin (what is not destroyed only)",
     *     tags={"Equipment"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of equipments with categories"
     *     )
     * )
     */
    public function indexAdmin()
    {
        try{
                    $equipmentsWithCategories = [];
                    // $equipmentCategories = EquipmentCategory::where('is_deleted', false)->get();
                    $equipmentCategories = Equipment_category::with('equipment')
                    ->whereHas('equipment', function (DatabaseEloquentBuilder $query) {
                    $query->where('is_deleted', false);
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
                            'description' => $equipment->equipment->description,
                            'category' => $equipment->category
                        ];
                    }
                    return response()->json([
                        'data' => $equipmentsWithCategories
                    ]);
    
        } catch(Exception $e) {    
            return response()->json($e);
        }
        
    }

            /**
     * @OA\Get(
     *     path="/api/equipment/indexCustomer",
     *     summary="Get all equipments for customer (what is not destroyed and blocked)",
     *     tags={"Equipment"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of equipments"
     *     )
     * )
     */
    public function indexCustomer()
    {
            try{
                $equipmentsWithCategories = [];
                // $equipmentCategories = EquipmentCategory::where('is_deleted', false)->get();
                $equipmentCategories = Equipment_category::with('equipment')
                ->whereHas('equipment', function (DatabaseEloquentBuilder $query) {
                $query->where('is_deleted', false)->where('is_blocked', false);
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
                        'description' => $equipment->equipment->description,
                        'category' => $equipment->category
                    ];
                }
                return response()->json([
                    'data' => $equipmentsWithCategories
                ]);

    } catch(Exception $e) {
        return response()->json($e);
    }
    }





    /**
         * @OA\Post(
         *     path="/api/equipment/store",
         *     summary="Create a new equipment ",
         *     tags={"Equipment"},
         * security={{"bearerAuth": {}}},
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"name", "description","category_id"},
         *             @OA\Property(property="name", type="string", example="palette,climatiseur"),
         *             @OA\Property(property="category_id", type="integer", example="1,2"),
         *         )
         *     ),
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
                        'name' => 'required|unique:equipment|max:255',
                        'icone' => 'image|mimes:jpeg,jpg,png,gif'
                    ]);

                    if ($request->hasFile('icone')) {
                        $icone_name = uniqid() . '.' . $request->file('icone')->getClientOriginalExtension();
                        $identity_profil_path = $request->file('icone')->move(public_path('image/icone'), $icone_name);
                        $base_url = url('/');
                        $icone_url = $base_url . '/image/icone/' . $icone_name;
                        }
                    
                    $equipment  = new Equipment();
                    $equipment->name = $request->name;
                    $equipment->category_id = $request->category_id;
                    $equipment->icone = $icone_url;
                    $equipment->save();
                    $equipment_category = new Equipment_category();
                    $equipment_category->equipment_id = $equipment->id;
                    $equipment_category->category_id = $request->category_id;
                    $equipment_category->save();
                    return response()->json([
                        "message" =>"save successfully",
                        "equipment" => $equipment
                    ],200);
            } catch(Exception $e) {    
                return response()->json($e);
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
            return response()->json($e);
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
                $equipment = Equipment::find($id);

                if (!$equipment) {
                    return response()->json(['error' => 'Equipement non trouvé.'], 404);
                }

                $data = $request->validate([
                    'name' => 'required|string|unique:equipment,name,',
                ]);

               Equipment::whereId($id)->update($data);

                return response()->json(['data' => 'nom de l\'équipement mis à jour avec succès.'], 200);
        } catch(Exception $e) {
            return response()->json($e);
        }
    }

    //  /**
    //  * @OA\Put(
    //  *     path="/api/equipment/updateIcone/{id}",
    //  *     summary="Update an equipment icone by ID",
    //  *     tags={"Equipment"},
    //  * security={{"bearerAuth": {}}},
    //  *     @OA\Parameter(
    //  *         name="id",
    //  *         in="path",
    //  *         required=true,
    //  *         description="ID of the equipment to update",
    //  *         @OA\Schema(type="integer")
    //  *     ),
    //  *     @OA\RequestBody(
    //  *         required=true,
    //  *         @OA\JsonContent(
    //  *             required={"name", "description","category_id"},
    //  *             @OA\Property(property="name", type="string", example="New Equipment Name"),
    //  *             @OA\Property(property="description", type="string", example="Description of the updated equipment"),
    //  *         )
    //  *     ),
    //  *     @OA\Response(
    //  *         response=200,
    //  *         description="Equipment updated successfully",
    //  *         @OA\JsonContent(
    //  *             @OA\Property(property="data", type="string", example="Equipment updated successfully")
    //  *         )
    //  *     ),
    //  *     @OA\Response(
    //  *         response=404,
    //  *         description="Equipment not found",
    //  *         @OA\JsonContent(
    //  *             @OA\Property(property="error", type="string", example="Equipment not found")
    //  *         )
    //  *     ),
    //  *     @OA\Response(
    //  *         response=422,
    //  *         description="Validation error",
    //  *         @OA\JsonContent(
    //  *             @OA\Property(property="error", type="string", example="The given data was invalid.")
    //  *         )
    //  *     )
    //  * )
    //  */
    public function updateIcone(Request $request, string $id)
    {
        
        try {
            $equipment = Equipment::find($id);
        
            if (!$equipment) {
                return response()->json(['error' => 'Equipement non trouvé.'], 404);
            }
            
            // $request->validate([
            //     'icone' => 'image|mimes:jpeg,jpg,png,gif'
            // ]);

            dd( $request->file());
            if ($request->hasFile('icone')) {
                $icone_name = uniqid() . '.' . $request->file('icone')->getClientOriginalExtension();
                $icone_path = $request->file('icone')->move(public_path('image/icone'), $icone_name);
                $base_url = url('/');
                $icone_url = $base_url . '/image/icone/' . $icone_name;
        
                Equipment::whereId($id)->update(['icone' => $icone_url]);
        
                return response()->json(['data' => 'icône de l\'équipement mis à jour avec succès.'], 200);
            } else {
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
        try{
                $equipment = Equipment::find($id);

                if (!$equipment) {
                    return response()->json(['error' => 'Équipement non trouvé.'], 404);
                }

                Equipment::whereId($id)->update(['is_deleted' => true]);

                return response()->json(['data' => 'Équipement supprimé avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
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
            return response()->json($e);
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
            return response()->json($e);
        }


    }


}