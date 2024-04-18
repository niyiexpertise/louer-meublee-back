<?php

namespace App\Http\Controllers;

use App\Models\Housing_category_file;
use App\Models\Housing;
use App\Models\housing_preference;
use App\Models\reduction;
use App\Models\promotion;
use App\Models\photo;
use App\Models\housing_price;
use App\Models\File;
use App\Models\Notification;
use App\Models\User;
use App\Models\Equipment;
use App\Models\Equipment_category;
use App\Models\Housing_equipment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File as F;
use Illuminate\Http\Request;
use App\Models\Category;

class HousingCategoryFileController extends Controller
{
  
    /**
 * @OA\Delete(
 *     path="/api/logement/category/photo/{photoid}",
 *     tags={"Housing Category Photo"},
 *     summary="Supprimer une photo d'une categorie",
 *     description="Supprime une photo d'une categorie.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="photoid",
 *         in="path",
 *         description="ID de la photo à supprimer",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="La photo a été supprimée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="La photo a été supprimée avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Photo non trouvée",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Photo non trouvée")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Une erreur s'est produite lors de la suppression de la photo.")
 *         )
 *     )
 * )
 */

 public function deletePhotoHousingCategory(Request $request, $id)
 {
     $photo = File::findOrFail($id);
 
     $path = parse_url($photo->path, PHP_URL_PATH);
     $photoPath = public_path($path);
 
     if (F::exists($photoPath)) {
         F::delete($photoPath);
     }
 
     $photo->delete();
 
     return response()->json(['message' => 'La photo a été supprimée avec succès.'], 200);
 }

 /**
 * @OA\Post(
 *     path="/api/logement/category/default/add",
 *     tags={"Housing Category Photo"},
 *     summary="Ajouter une catégorie existant par défaut à un logement",
 *     description="Ajoute une nouvelle catégorie de logement avec les informations fournies",
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(property="housing_id", type="integer", example="1", description="ID du logement"),
 *                 @OA\Property(property="category_id", type="integer", example="1", description="ID de la catégorie"),
 *                 @OA\Property(property="number", type="integer", example="3", description="Nombre de catégorie que possède le logement"),
 *                 @OA\Property(
 *                     property="photos[]",
 *                     type="array",
 *                     @OA\Items(type="string", format="binary", description="Image de la catégorie (JPEG, PNG, JPG, GIF, taille max : 2048)")
 *                 ),
 *                 required={"housing_id", "category_id", "number", "photos[]"}
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Catégorie de logement ajoutée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Catégorie de logement ajoutée avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erreur de validation",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="object", additionalProperties={"type": "string"})
 *         )
 *     ),
 *     @OA\Response(
 *         response=409,
 *         description="Conflit - Une entrée pour cette catégorie et ce logement existe déjà",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Une entrée pour cette catégorie et ce logement existe déjà")
 *         )
 *     )
 * )
 */

 public function addHousingCategory(Request $request)
 {

     $validator = Validator::make($request->all(), [
         'housing_id' => 'required|exists:housings,id',
         'category_id' => 'required|exists:categories,id',
         'number' => 'required|integer',
         'photos' => 'required',
         'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
     ]);
 
     if ($validator->fails()) {
         return response()->json(['error' => $validator->errors()], 400);
     }
 
     $existingEntry = Housing_category_file::where('housing_id', $request->housing_id)
         ->where('category_id', $request->category_id)
         ->first();
 
     if ($existingEntry) {
         return response()->json(['error' => 'Une entrée pour cette catégorie et ce logement existe déjà'], 409);
     }
     foreach ($request->file('photos') as $photo) {
         $photoModel = new File();
         $photoName = uniqid() . '.' . $photo->getClientOriginalExtension();
         $photoPath = $photo->move(public_path('image/photo_category'), $photoName);
         $photoUrl = url('/image/photo_category/' . $photoName);
         $photoModel->path = $photoUrl;
         $photoModel->save();
 
         $housingCategoryFile = new Housing_category_file();
         $housingCategoryFile->housing_id = $request->housing_id;
         $housingCategoryFile->category_id = $request->category_id;
         $housingCategoryFile->file_id = $photoModel->id;
         $housingCategoryFile->number = $request->number;
         $housingCategoryFile->save();
     }
 
     return response()->json(['message' => 'Catégorie ajoutée avec succès au logement'], 201);
 }
 

/**
 * @OA\Post(
 *     path="/api/logement/category/default/addNew",
 *     tags={"Housing Category Photo"},
 *     summary="Ajouter une catégorie inexistante par défaut à un logement",
 *     description="Ajouter une nouvelle catégorie inexistante par defaut à un logement",
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(property="housing_id", type="integer", example="1", description="ID du logement"),
 *                 @OA\Property(property="category_name", type="string", example="Nouvelle Catégorie", description="Nom de la nouvelle catégorie"),
 *                 @OA\Property(property="number", type="integer", example="3", description="Nombre de catégorie que possède le logement"),
 *                 @OA\Property(
 *                     property="photos[]",
 *                     type="array",
 *                     @OA\Items(type="string", format="binary", description="Image de la catégorie (JPEG, PNG, JPG, GIF, taille max : 2048)")
 *                 ),
 *                 required={"housing_id", "category_name", "number", "photos[]"}
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Catégorie de logement ajoutée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Catégorie de logement ajoutée avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erreur de validation",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="object", additionalProperties={"type": "string"})
 *         )
 *     ),
 *     @OA\Response(
 *         response=409,
 *         description="Conflit - Une entrée pour cette catégorie et ce logement existe déjà",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Une entrée pour cette catégorie et ce logement existe déjà")
 *         )
 *     )
 * )
 */

 public function addHousingCategoryNew(Request $request)
{
    $validator = Validator::make($request->all(), [
        'housing_id' => 'required|exists:housings,id',
        'category_name' => 'required|string',
        'number' => 'required|integer',
        'photos' => 'required',
        'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    $category = new Category();
    $category->name = $request->category_name;
    $category->is_verified = false;
    $category->save();
    $category_id = $category->id;

    foreach ($request->file('photos') as $photo) {
        $photoModel = new File();
        $photoName = uniqid() . '.' . $photo->getClientOriginalExtension();
        $photoPath = $photo->move(public_path('image/photo_category'), $photoName);
        $photoUrl = url('/image/photo_category/' . $photoName);
        $photoModel->path = $photoUrl;
        $photoModel->save();

        $housingCategoryFile = new Housing_category_file();
        $housingCategoryFile->housing_id = $request->housing_id;
        $housingCategoryFile->category_id = $category_id;
        $housingCategoryFile->file_id = $photoModel->id;
        $housingCategoryFile->number = $request->number;
        $housingCategoryFile->save();
    }

    return response()->json(['message' => 'Catégorie ajoutée avec succès au logement'], 201);
}
}
