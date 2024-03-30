<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Exception;

class RoleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/role/index",
     *     summary="Tous les types de role possibles",
     *     tags={"Role"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste de tous les différentes sortes de role",
     *         
     *     )
     * )
     */
    public function index()
    {
        try{
                $role = Role::where('is_deleted', false)->get();

                return response()->json(['data' => $role], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

    /**
     * @OA\Post(
     *     path="/api/role/store",
     *     summary="Créer un nouveau type de role",
     *     tags={"Role"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="admin,hôte,voyageur,manageur,etc")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Type de role créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Type de role créé avec succès")
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
                        'name' => 'required|unique:roles|max:255',
                ]);

                $role = Role::create($validatedData);

                return response()->json(['data' => 'Type de role créé avec succès.', 'role' => $role], 201);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

    /**
     * @OA\Get(
     *     path="/api/role/show/{id}",
     *     summary="Obtenir un type de role spécifique par ID",
     *     tags={"Role"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du type de role",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du type de role"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de role non trouvé"
     *     )
     * )
     */
    public function show($id)
    {
        try{
                $role= Role::find($id);

                if (!$role) {
                    return response()->json(['error' => 'Type de role non trouvé.'], 404);
                }
                return response()->json(['data' => $role], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

    /**
     * @OA\Put(
     *     path="/api/role/update/{id}",
     *     summary="Mettre à jour un type de role par ID",
     *     tags={"Role"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du type de role",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="voyageur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type de role mis à jour avec succès"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de role non trouvé"
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
                $role= Role::find($id);

                    if (!$role) {
                        return response()->json(['error' => 'Type de role non trouvé.'], 404);
                    }

                    $validatedData = $request->validate([
                        'name' => 'required|string',
                    ]);

                    $role->update($validatedData);

                    return response()->json(['data' => 'Type de role mis à jour avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }
 
    }

    /**
     * @OA\Delete(
     *     path="/api/role/destroy/{id}",
     *     summary="
    * Supprimer un type de role par ID",
     *     tags={"Role"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du type de role",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Type de role supprimé avec succès"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de role non trouvé"
     *     )
     * )
     */
    public function destroy($id)
    {
        try{
                $role= Role::find($id);

                if (!$role) {
                    return response()->json(['error' => 'Type de role non trouvé.'], 404);
                }

                $role->is_deleted = true;
                $role->save();

                return response()->json(['data' => 'Type de role supprimé avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

    /**
     * @OA\Put(
     *     path="/api/role/block/{id}",
     *     summary="Bloquer un type de role",
     *     tags={"Role"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du type de role à bloquer",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type de role bloqué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Type de role bloqué avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de role non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Type de role non trouvé")
     *         )
     *     )
     * )
     */
    public function block($id)
    {
        try{
                $role= Role::find($id);

                if (!$role) {
                    return response()->json(['error' => 'Type de role non trouvé.'], 404);
                }

                $role->is_blocked = true;
                $role->save();

                return response()->json(['data' => 'Type de role bloqué avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

    /**
     * @OA\Put(
     *     path="/api/role/unblock/{id}",
     *     summary="Débloquer un type de role",
     *     tags={"Role"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du type de role à débloquer",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type de role débloqué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Type de role débloqué avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de role non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Type de role non trouvé")
     *         )
     *     )
     * )
     */
    public function unblock($id)
    {
        try{
                $role= Role::find($id);

                if (!$role) {
                    return response()->json(['error' => 'Type de role non trouvé.'], 404);
                }

                $role->is_blocked = false;
                $role->save();

                return response()->json(['data' => 'Type de role débloqué avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }
}
