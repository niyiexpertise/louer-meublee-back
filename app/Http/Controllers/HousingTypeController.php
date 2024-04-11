<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\HousingType;
use Exception;
/**
 * @OA\Info(
 *      title="Api de location des meubles",
 *      version="1.0.0",
 *      description="il s'agit de la documentation complète de chaque methode,route,etc",
 *      @OA\Contact(
 *          email="ayenaaurel15@gmail.com",
 *          email="zakiyoubababodi@gmail.com "
 *      )
 * )
 */
class HousingTypeController extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/housingtype/index",
     *     summary="Get all housing types",
     *     tags={"HousingType"},
     *     @OA\Response(
     *         response=200,
     *         description="List of housing types"
     *  
     *     )
     * )
     */
    public function index()
    {
        try{
                $housingTypes = HousingType::where('is_deleted', false)->get();

                return response()->json(['data' => $housingTypes], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }
    /**
 * @OA\Post(
 *     path="/api/housingtype/store",
 *     summary="Create a new housing type",
 *     tags={"HousingType"},
 *      @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "description"},
 *             @OA\Property(property="name", type="string", example="partagé, complet"),
 *             @OA\Property(property="description", type="string", example="Spacious apartment in the city cente")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Housing type created successfully"
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
                $validatedData = $request->validate([
                    'name' => 'required|unique:housing_types|max:255',
                    'description' => 'required|string',
                ]);

                $housingType = HousingType::create($validatedData);

                return response()->json(['data' => 'Type de type de logement  créé avec succès.', 'housingType' => $housingType], 201);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

     /**
     * @OA\Get(
     *     path="/api/housingtype/show/{id}",
     *     summary="Get a specific housing type by ID",
     *     tags={"HousingType"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the housing type",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Housing type details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Housing type not found"
     *     )
     * )
     */
    public function show($id)
    {
        try{
                $housingType = HousingType::find($id);

                if (!$housingType) {
                    return response()->json(['error' => 'Type de logement non trouvé.'], 404);
                }

                return response()->json(['data' => $housingType], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }
/**
     * @OA\Put(
     *     path="/api/housingtype/update/{id}",
     *     summary="Update a housing type by ID",
     *     tags={"HousingType"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the housing type",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "description"},
 *             @OA\Property(property="name", type="string", example="Apartment"),
 *             @OA\Property(property="description", type="string", example="Spacious apartment in the city center")
 *         )
 *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Housing type updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Housing type not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try{
            $housingType = HousingType::find($id);

            if (!$housingType) {
                return response()->json(['error' => 'Type de logement  non trouvé.'], 404);
            }

            $validatedData = $request->validate([
                'name' => 'required|string',
                'description' => 'required|string',
            ]);

            $housingType->update($validatedData);

            return response()->json(['data' => 'Type de logement  mise à jour avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }
 /**
     * @OA\Delete(
     *     path="/api/housingtype/destroy/{id}",
     *     summary="Delete a housing type by ID",
     *     tags={"HousingType"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the housing type",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Housing type deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Housing type not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        try{
            $housingType = HousingType::find($id);

            if (!$housingType) {
                return response()->json(['error' => 'Type de logement  non trouvé.'], 404);
            }

            $housingType->is_deleted = true;
            $housingType->save();

            return response()->json(['data' => 'Type de logement  supprimé avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

    /**
 * @OA\Put(
 *     path="/api/housingtype/block/{id}",
 *     summary="Block a housing type",
 *     tags={"HousingType"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the housing type to block",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="HousingType successfully blocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="HousingType successfully blocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="HousingType not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="HousingType not found")
 *         )
 *     )
 * )
 */
    public function block($id)
    {
        try{
                $housingType = HousingType::find($id);

                if (!$housingType) {
                    return response()->json(['error' => 'HousingType non trouvé.'], 404);
                }

                $housingType->is_blocked = true;
                $housingType->save();

                return response()->json(['data' => 'HousingType bloqué avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

    /**
 * @OA\Put(
 *     path="/api/housingtype/unblock/{id}",
 *     summary="Unblock a housing type",
 *     tags={"HousingType"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the housing type to unblock",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="HousingType successfully unblocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="HousingType successfully unblocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="HousingType not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="HousingType not found")
 *         )
 *     )
 * )
 */
    public function unblock($id)
    {
        try{
                $housingType = HousingType::find($id);

                if (!$housingType) {
                    return response()->json(['error' => 'HousingType non trouvée.'], 404);
                }

                $housingType->is_blocked = false;
                $housingType->save();

                return response()->json(['data' => 'HousingType débloquée avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }
}

