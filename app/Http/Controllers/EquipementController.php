<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentCategory;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as DatabaseEloquentBuilder;
use Illuminate\Http\Request;
use Exception;

class EquipementController extends Controller
{
        /**
     * @OA\Get(
     *     path="/api/equipment/index",
     *     summary="Get all equipments with their categories",
     *     tags={"Equipment"},
     *     @OA\Response(
     *         response=200,
     *         description="List of equipments with categories"
     *     )
     * )
     */
    public function index()
    {
        try{
                    $equipmentsWithCategories = [];
                    // $equipmentCategories = EquipmentCategory::where('is_deleted', false)->get();
                    $equipmentCategories = EquipmentCategory::with('equipment')
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
         * @OA\Post(
         *     path="/api/equipment/store",
         *     summary="Create a new equipment ",
         *     tags={"Equipment"},
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"name", "description","category_id"},
         *             @OA\Property(property="name", type="string", example="palette,climatiseur"),
         *             @OA\Property(property="description", type="string", example="Description of the equipment"),
         *  @OA\Property(property="category_id", type="integer",example=1)
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
                    ]);
                    $equipment  = new Equipment();
                    $equipment->name = $request->name;
                    $equipment->description = $request->description;
                    $equipment->save();
                    $equipment_category = new EquipmentCategory();
                    $equipment_category->equipment_id = $equipment->id;
                    $equipment_category->category_id = $request->category_id;
                
                    $equipment_category->save();
                    return response()->json([
                        "message" =>"save successfully",
                        "equipment_category" =>$equipment_category,
                        "equipment" => $equipment
                    ],200);
            } catch(Exception $e) {    
                return response()->json($e);
            }

        }

        /**
     * @OA\Get(
     *     path="/api/equipment/show/{id}",
     *     summary="Get information about a specific equipment in a category",
     *     tags={"Equipment"},
     *  @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the equipment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Information about the specified equipment in the category",
     *         
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Equipment not found for the specified category",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Équipement introuvable pour cette catégorie.")
     *         )
     *     )
     * )
     */

        public function show($id)
    {
        try{
                $equipment = Equipment::find($id);
                // $equipment_category = EquipmentCategory::where('equipment_id',$id)->get();
                $equipment_category = $equipment->equipment_category()->get();
                if (!$equipment) {
                    return response()->json(['error' => 'Équipement introuvable pour cette catégorie.'], 404);
                }
                $equipmentD = [
                    'equipment' => $equipment,
                    'equipment_category' => $equipment_category
                ];

                return response()->json(['data' => $equipmentD], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }
    /**
     * @OA\Put(
     *     path="/api/equipment/update/{id}",
     *     summary="Update an equipment by ID",
     *     tags={"Equipment"},
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
     *             @OA\Property(property="description", type="string", example="Description of the updated equipment"),
     *             @OA\Property(property="category_id", type="integer",example=1)
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
    public function update(Request $request, string $id)
    {
        try{
                $equipment = Equipment::find($id);

                if (!$equipment) {
                    return response()->json(['error' => 'Equipement non trouvé.'], 404);
                }

                $validatedData = $request->validate([
                    'name' => 'required|string|unique:equipment,name,' . $equipment->id,
                    'description' => 'required|string',
                ]);

                $equipment->name = $validatedData['name'];
                $equipment->description = $validatedData['description'];

                $equipment->save();

                return response()->json(['data' => 'Équipement mis à jour avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }
    }


        /**
     * @OA\Delete(
     *     path="api/equipment/destroy/{id}",
     *     summary="Delete an equipment by ID",
     *     tags={"Equipment"},
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

                $equipment->is_deleted = true;

            
                $equipment->save();

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

                // Mettre à jour le champ is_blocked à true
                $equipment->is_blocked = true;

                // Enregistrer la modification dans la base de données
                $equipment->save();

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
                $equipment->is_blocked = false;

                $equipment->save();

                return response()->json(['data' => 'Équipement débloqué avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }


    }


}
