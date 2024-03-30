<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use Exception;

class LanguageController extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/language/index",
     *     summary="Get all languages",
     *     tags={"Language"},
     *     @OA\Response(
     *         response=200,
     *         description="List of languages"
     *
     *     )
     * )
     */
    public function index()
    {
        try{
                $languages = Language::where('is_deleted',false)->get();
                return response()->json([
                    'data' => $languages
                ]);
        } catch(Exception $e) {
            return response()->json($e);
        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

/**
     * @OA\Post(
     *     path="/api/language/store",
     *     summary="add new language",
     *     tags={"Language"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="français,anglais,etc")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="new language created successfuly",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="new language created successfuly")
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
                $data = $request->validate([
                    'name' => 'required|unique:languages|max:255',
                ]);
                $language = new Language();
                $language->name = $request->name;
                $language->icone = $request->icone;
                $language->save();
                return response()->json([
                    'message' =>'Language created successfully',
                    'data' => $language
                ]);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

  /**
     * @OA\Get(
     *     path="/api/language/show/{id}",
     *     summary="Get a specific language by ID",
     *     tags={"Language"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the language",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Language details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Language not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        try{
                $language = Language::find($id);

                if (!$language) {
                    return response()->json(['error' => 'Langue non trouvé.'], 404);
                }

                return response()->json(['data' => $language], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

/**
     * @OA\Put(
     *     path="/api/language/update/{id}",
     *     summary="Update a language by ID",
     *     tags={"Language"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the language",
     *         @OA\Schema(type="integer")
     *     ),
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="français,anglais,etc")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Language updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Language not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        try{
                $data = $request->validate([
                    'name' =>'required | string',
                ]);
                $language = Language::whereId($id)->update($data);
                return response()->json(['data' => 'Logement mise à jour avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

      /**
     * @OA\Delete(
     *     path="/api/language/destroy/{id}",
     *     summary="Delete a language by ID",
     *     tags={"Language"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the language",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Language deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Language not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try{
                $language = Language::whereId($id)->update(['is_deleted' => true]);

                if (!$language) {
                    return response()->json(['error' => 'Logement non trouvé.'], 404);
                }

                return response()->json(['data' => 'Logement supprimé avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }


        /**
 * @OA\Put(
 *     path="/api/language/block/{id}",
 *     summary="Block a language",
 *     tags={"Language"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the language to block",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Language successfully blocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Language successfully blocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Language not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Language not found")
 *         )
 *     )
 * )
 */
    public function block(string $id)
 {
    try{
            $language = Language::whereId($id)->update(['is_blocked' => true]);

            if (!$language) {
                return response()->json(['error' => 'Logement non trouvé.'], 404);
            }

            return response()->json(['data' => 'This type of propriety is block successfuly.'], 200);
    } catch(Exception $e) {    
        return response()->json($e);
    }
 }

  /**
 * @OA\Put(
 *     path="/api/language/unblock/{id}",
 *     summary="Unblock a language",
 *     tags={"Language"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the language to unblock",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Language successfully unblocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Language successfully unblocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Language not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Language not found")
 *         )
 *     )
 * )
 */

 public function unblock(string $id)
{
    try{
            $language = Language::whereId($id)->update(['is_blocked' => false]);

            if (!$language) {
                return response()->json(['error' => 'Logement non trouvé.'], 404);
            }
            return response()->json(['data' => 'his type of propriety is unblock successfuly.'], 200);
    } catch(Exception $e) {    
        return response()->json($e);
    }


}
}
