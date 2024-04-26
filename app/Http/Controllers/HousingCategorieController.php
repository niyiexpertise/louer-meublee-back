<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\File;
use App\Models\Housing_category_file;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HousingCategorieController extends Controller
{

  /**
  *@OA\Post(
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
                $housingCategoryFile->is_verified= false;
                $housingCategoryFile->save();
            }
            $userId = Auth::id();
           $notification = new Notification([
               'name' => "Votre ajout de la pièce a été pris en compte. l'administrateur validera dans moin de 48h",
               'user_id' => $userId,
              ]);
            $adminUsers = User::where('is_admin', 1)->get();
                   foreach ($adminUsers as $adminUser) {
                       $notification = new Notification();
                       $notification->user_id = $adminUser->id;
                       $notification->name = "Un hôte  vient d'ajouter sur le site une nouvelle pièce pour son logement.Veuilez vous connecter pour valider";
                       $notification->save();
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
               $housingCategoryFile->is_verified = false;
               $housingCategoryFile->save();
           }
           $userId = Auth::id();
           $notification = new Notification([
               'name' => "Votre ajout de la pièce a été pris en compte. l'administrateur validera dans moin de 48h",
               'user_id' => $userId,
              ]);
            $adminUsers = User::where('is_admin', 1)->get();
                   foreach ($adminUsers as $adminUser) {
                       $notification = new Notification();
                       $notification->user_id = $adminUser->id;
                       $notification->name = "Un hôte  vient d'ajouter sur le site une nouvelle pièce inexistante.Veuilez vous connecter pour valider";
                       $notification->save();
                   }
       
           return response()->json(['message' => 'Catégorie ajoutée avec succès au logement'], 201);
       }

     /**
 * @OA\Get(
 *      path="/api/logement/category/default/invalid",
 *      tags={"Housing Category Photo"},
 *     security={{"bearerAuth": {}}},
 *      summary="Récupérer les catégories existantes par défaut des logement en attente de validation",
 *      description="Retourne une liste des catégories existantes par défaut des logement en attente de validation et les photos des catégories associées.",
 *      @OA\Response(
 *          response=200,
 *          description="Succès - Liste des logements par catégorie",
 *          @OA\JsonContent(
 *          )
 *      ),
 * )
 */
public function getCategoryDefaultInvalidHousings()
{
    $categories = Category::where('is_verified', true)->get();

    $data = [];

    foreach ($categories as $category) {
        $housingCategoryFiles = Housing_category_file::where('category_id', $category->id)
            ->whereHas('housing', function ($query) {
                $query->where('is_verified', false);
            })
            ->with('housing')
            ->get();

        foreach ($housingCategoryFiles as $housingCategoryFile) {
            $housingId = $housingCategoryFile->housing->id;

            $existingHousingIndex = null;
            foreach ($data as $index => $existingHousing) {
                if ($existingHousing['housing_id'] === $housingId && $existingHousing['category_id'] === $category->id) {
                    $existingHousingIndex = $index;
                    break;
                }
            }

            if ($existingHousingIndex === null) {
                $housingData = [
                    'housing_category_file_id' => $housingCategoryFile->id,
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'housing_id' => $housingId,
                    'housing_name' => $housingCategoryFile->housing->name,
                    'housing_description' => $housingCategoryFile->housing->description,
                    'photos_category' => $housingCategoryFile->file()->select('id', 'path')->get()->toArray(),
                    'is_verified' => $housingCategoryFile->is_verified,
                ];

                $data[] = $housingData;
            } else {
                $data[$existingHousingIndex]['photos_category'] = array_merge(
                    $data[$existingHousingIndex]['photos_category'],
                    $housingCategoryFile->file()->select('id', 'path')->get()->toArray()
                );
            }
        }
    }

    return response()->json(['data' => $data]);
}
}
