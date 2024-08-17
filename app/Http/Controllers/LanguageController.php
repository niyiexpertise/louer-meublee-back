<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Language;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Validation\Rule;
use App\Models\User_language;

class LanguageController extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/language/index",
     *     summary="Get all languages",
     *     tags={"Language"},
     * security={{"bearerAuth": {}}},
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
              return response()->json(['error' => $e->getMessage()], 500);
        }

    }



/**
         * @OA\Post(
         *     path="/api/language/store",
         *     summary="Create a new language ",
         *     tags={"Language"},
         * security={{"bearerAuth": {}}},
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(property="name", type="string", example="Allemand , Mandarin"),
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
         *         description="Language  created successfully"
         *     ),
         *     @OA\Response(
         *         response=401,
         *         description="Invalid credentials"
         *     )
         * )
         */
        public function store(Request $request)
        {
            try {
                $data = $request->validate([
                    'name' => 'required|unique:languages|max:255',
                ]);
        
                $language = new Language();
                if ($request->hasFile('icone')) {
                    $icone_name = uniqid() . '.' . $request->file('icone')->getClientOriginalExtension();
                    $identity_profil_path = $request->file('icone')->move(public_path('image/iconeLanguage'), $icone_name);
                    $base_url = url('/');
                    $icone_url = $base_url . '/image/iconeLanguage/' . $icone_name;
                    $language->icone = $icone_url;
                    }
                $language->name = $request->name;
                $language->save();
        
                return response()->json([
                    'message' => 'Language created successfully',
                    'data' => $language
                ]);
            } catch(ValidationException $e) {
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => $e->validator->errors()->first()
                ], 422);
            } catch(Exception $e) {
                return response()->json([
                    'error' => 'An error occurred',
                    'message' => $e->getMessage()
                ], 500);
            }
        }

  /**
     * @OA\Get(
     *     path="/api/language/show/{id}",
     *     summary="Get a specific language by ID",
     *     tags={"Language"},
     * security={{"bearerAuth": {}}},
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
              return response()->json(['error' => $e->getMessage()], 500);
        }

    }


/**
     * @OA\Put(
     *     path="/api/language/updateName/{id}",
     *     summary="Update a language by ID",
     *     tags={"Language"},
     * security={{"bearerAuth": {}}},
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
    
    public function updateName(Request $request, string $id)
    {
        try {
            $data = $request->validate([
                'name' => [
                    'required',
                    'string',
                    Rule::unique('languages')->ignore($id),
                ],
            ]);
    
            $language = Language::whereId($id)->update($data);
            
            return response()->json(['data' => 'Langage mis à jour avec succès.'], 200);
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    

    /**
     * @OA\Post(
     *     path="/api/language/updateIcone/{id}",
     *     summary="Update a language icone by ID",
     *     tags={"Language"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the language to update",
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
     *         description="Language updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Language updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Language not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Language not found")
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
            $language = Language::find($id);
            
            if (!$language) {
                return response()->json(['error' => 'Language non trouvé.'], 404);
            }
            
            // $request->validate([
            //         'icone' => 'image|mimes:jpeg,jpg,png,gif'
            //     ]);

            $oldProfilePhotoUrl = $language->icone;
            if ($oldProfilePhotoUrl) {
                $parsedUrl = parse_url($oldProfilePhotoUrl);
                $oldProfilePhotoPath = public_path($parsedUrl['path']);
                if (F::exists($oldProfilePhotoPath)) {
                    F::delete($oldProfilePhotoPath);
                }
            }
                
                if ($request->hasFile('icone')) {
                    $icone_name = uniqid() . '.' . $request->file('icone')->getClientOriginalExtension();
                    $icone_path = $request->file('icone')->move(public_path('image/iconeLanguage'), $icone_name);
                    $base_url = url('/');
                    $icone_url = $base_url . '/image/iconeLanguage/' . $icone_name;
                    
                    Language::whereId($id)->update(['icone' => $icone_url]);
                    
                    return response()->json(['data' => 'icône de la langue mis à jour avec succès.'], 200);
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
     *     path="/api/language/destroy/{id}",
     *     summary="Delete a language by ID",
     *     tags={"Language"},
     * security={{"bearerAuth": {}}},
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
                    return response()->json(['error' => 'Language non trouvé.'], 200);
                }
                $nbexist= User_language::where('language_id', $id)->count(); 
        
            if ($nbexist > 0) {
                return response()->json(['error' => "Suppression impossible car la langue est déjà associé à un utilisateur."],200);
    
            }

                return response()->json(['data' => 'language supprimé avec succès.'], 200);
        } catch(Exception $e) {    
              return response()->json(['error' => $e->getMessage()], 500);
        }

    }


        /**
 * @OA\Put(
 *     path="/api/language/block/{id}",
 *     summary="Block a language",
 *     tags={"Language"},
 * security={{"bearerAuth": {}}},
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
          return response()->json(['error' => $e->getMessage()], 500);
    }
 }

  /**
 * @OA\Put(
 *     path="/api/language/unblock/{id}",
 *     summary="Unblock a language",
 *     tags={"Language"},
 * security={{"bearerAuth": {}}},
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
          return response()->json(['error' => $e->getMessage()], 500);
    }


}
}
