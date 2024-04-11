<?php

namespace App\Http\Controllers;

use App\Models\Category;
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
     *     summary="Get all equipments ",
     *     tags={"Equipment"},
     *     @OA\Response(
     *         response=200,
     *         description="List of equipments"
     *     )
     * )
     */
    public function index()
    {
        try{
                $equipments = Equipment::where('is_deleted',false)->get();
                return response()->json([
                    'data' => $equipments
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

                $data = $request->validate([
                    'name' => 'required|string|unique:equipment,name,',
                    'description' => 'required|string',
                ]);

               Equipment::whereId($id)->update($data);

                return response()->json(['data' => 'Équipement mis à jour avec succès.'], 200);
        } catch(Exception $e) {
            return response()->json($e);
        }
    }


        /**
     * @OA\Delete(
     *     path="/api/equipment/destroy/{id}",
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
