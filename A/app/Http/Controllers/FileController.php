<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\File as F ;

class FileController extends Controller
{

     /**
     * @OA\Post(
     *     path="/api/file/updateFile/{imageID}",
     *     summary="Update an file path by ID",
     *     tags={"File"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="imageID",
     *         in="path",
     *         required=true,
     *         description="ID of the file to update",
     *         @OA\Schema(type="integer")
     *     ),
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(
 *           property="path",
 *           type="string",
 *           format="binary",
 *           description="Image de profil d'identité (JPEG, PNG, JPG, GIF, taille max : 2048)"
 *         ),
 *       )
 *     )
 *   ),
     *     @OA\Response(
     *         response=200,
     *         description="File updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="File updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="File not found")
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
    public function updateFile(Request $request, $imageID){
        try {
            $file = File::find($imageID);

            if (!$file) {
                return response()->json(['error' => 'image non trouvé.'], 404);
            }

            $request->validate([
                    'path' => 'image|mimes:jpeg,jpg,png,gif'
                ]);

                $oldProfilePhotoUrl = $file->path;
                if ($oldProfilePhotoUrl) {
                    $parsedUrl = parse_url($oldProfilePhotoUrl);
                    $oldProfilePhotoPath = public_path($parsedUrl['path']);
                    if (F::exists($oldProfilePhotoPath)) {
                        F::delete($oldProfilePhotoPath);
                    }
                }

                if ($request->hasFile('path')) {
                    $path_name = uniqid() . '.' . $request->file('path')->getClientOriginalExtension();
                    $path = $request->file('path')->move(public_path('image/photo_category'), $path_name);
                    $base_url = url('/');
                    $path_url = $base_url . '/image/photo_category/' . $path_name;

                    File::whereId($imageID)->update(['path' => $path_url]);

                    return response()->json(['data' => 'image mis à jour avec succès.'], 200);
                } else {
                return response()->json(['error' => 'Aucun fichier image trouvé dans la requête.'], 400);
            }
        } catch (QueryException $e) {
            return response()->json(['error' => 'Erreur de requête SQL: ' . $e->getMessage()], 500);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
