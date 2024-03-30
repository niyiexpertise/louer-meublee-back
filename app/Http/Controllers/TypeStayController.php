<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TypeStay;
use Exception;

class TypeStayController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/typestays/index",
     *     summary="Tous les types de séjour possibles",
     *     tags={"TypeStay"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste de tous les types de séjour",
     *         
     *     )
     * )
     */
    public function index()
    {
        try{
                $typeStays = TypeStay::where('is_deleted', false)->get();

                return response()->json(['data' => $typeStays], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

    /**
     * @OA\Post(
     *     path="/api/typestays/store",
     *     summary="Créer un nouveau type de séjour",
     *     tags={"TypeStay"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="jours,nuit,mois,etc")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Type de séjour créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Type de séjour créé avec succès")
     *         )
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
                    'name' => 'required|unique:type_stays|max:255',
                ]);

                $typeStay = TypeStay::create($validatedData);

                return response()->json(['data' => 'Type de séjour créé avec succès.', 'typeStay' => $typeStay], 201);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

    /**
     * @OA\Get(
     *     path="/api/typestays/show/{id}",
     *     summary="Obtenir un type de séjour spécifique par ID",
     *     tags={"TypeStay"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du type de séjour",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du type de séjour"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de séjour non trouvé"
     *     )
     * )
     */
    public function show($id)
    {
        try{
                $typeStay = TypeStay::find($id);

                    if (!$typeStay) {
                        return response()->json(['error' => 'Type de séjour non trouvé.'], 404);
                    }

                    return response()->json(['data' => $typeStay], 200);
    
        } catch(Exception $e) {    
            return response()->json($e);
        }
 
    }

    /**
     * @OA\Put(
     *     path="/api/typestays/update/{id}",
     *     summary="Mettre à jour un type de séjour par ID",
     *     tags={"TypeStay"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du type de séjour",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="jours")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type de séjour mis à jour avec succès"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de séjour non trouvé"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try{
                $typeStay = TypeStay::find($id);

                if (!$typeStay) {
                    return response()->json(['error' => 'Type de séjour non trouvé.'], 404);
                }

                $validatedData = $request->validate([
                    'name' => 'required|string',
                ]);

                $typeStay->update($validatedData);

                return response()->json(['data' => 'Type de séjour mis à jour avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

    /**
     * @OA\Delete(
     *     path="/api/typestays/destoy/{id}",
     *     summary="
    * Supprimer un type de séjour par ID",
     *     tags={"TypeStay"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du type de séjour",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Type de séjour supprimé avec succès"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de séjour non trouvé"
     *     )
     * )
     */
    public function destroy($id)
    {
        try{
                $typeStay = TypeStay::find($id);

                if (!$typeStay) {
                    return response()->json(['error' => 'Type de séjour non trouvé.'], 404);
                }

                $typeStay->is_deleted = true;
                $typeStay->save();

                return response()->json(['data' => 'Type de séjour supprimé avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

    /**
     * @OA\Put(
     *     path="/api/typestays/block/{id}",
     *     summary="Bloquer un type de séjour",
     *     tags={"TypeStay"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du type de séjour à bloquer",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type de séjour bloqué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Type de séjour bloqué avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de séjour non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Type de séjour non trouvé")
     *         )
     *     )
     * )
     */
    public function block($id)
    {
        try{
                $typeStay = TypeStay::find($id);

                if (!$typeStay) {
                    return response()->json(['error' => 'Type de séjour non trouvé.'], 404);
                }

                $typeStay->is_blocked = true;
                $typeStay->save();

                return response()->json(['data' => 'Type de séjour bloqué avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

    /**
     * @OA\Put(
     *     path="/api/typestays/unblock/{id}",
     *     summary="Débloquer un type de séjour",
     *     tags={"TypeStay"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du type de séjour à débloquer",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type de séjour débloqué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Type de séjour débloqué avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de séjour non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Type de séjour non trouvé")
     *         )
     *     )
     * )
     */
    public function unblock($id)
    {
        try{
                $typeStay = TypeStay::find($id);

                if (!$typeStay) {
                    return response()->json(['error' => 'Type de séjour non trouvé.'], 404);
                }

                $typeStay->is_blocked = false;
                $typeStay->save();

                return response()->json(['data' => 'Type de séjour débloqué avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }
}
