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
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmail;
use App\Mail\NotificationEmailwithoutfile;
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
    $existingCategory = Category::where('name', $request->category_name)->first();
    if ($existingCategory) {
        return response()->json(['error' => 'Le nom de la catégorie existe déjà par défaut'], 400);
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
                    'user_id' => $housingCategoryFile->housing->user->id,
                    'user_lastname' => $housingCategoryFile->housing->user->lastname,
                    'user_firstname' => $housingCategoryFile->housing->user->firstname,
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


/**
 * @OA\Put(
 *      path="/api/logement/category/default/{housing_id}/{category_id}/validate",
 *      tags={"Housing Category Photo"},
 *     security={{"bearerAuth": {}}},
 *      summary="Valider une catégorie existante par défaut en attente de validation",
 *      description="Valide une catégorie existante par défaut en attente de validation en mettant à jour le statut is_verified de housing_category_file à true.",
 *      @OA\Parameter(
 *          name="housing_id",
 *          in="path",
 *          required=true,
 *          description="ID du logement associé à la catégorie",
 *          @OA\Schema(
 *              type="integer",
 *          )
 *      ),
 *      @OA\Parameter(
 *          name="category_id",
 *          in="path",
 *          required=true,
 *          description="ID de la catégorie à valider",
 *          @OA\Schema(
 *              type="integer",
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Succès - Catégorie validée avec succès"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Erreur - Catégorie non trouvée"
 *      )
 * )
 */
public function validateDefaultCategoryHousing($housing_id, $category_id)
{
    $category = Category::find($category_id);

    if (!$category || !$category->is_verified) {
        return response()->json(['error' => 'La catégorie n\'est pas vérifiée, la validation ne peut pas être effectuée'], 400);
    }

    $housing = Housing::find($housing_id);

    if (!$housing) {
        return response()->json(['error' => 'Le logement associé n\'existe pas'], 404);
    }

    $housingCategoryFiles = Housing_category_file::where('housing_id', $housing_id)
        ->where('category_id', $category_id)
        ->get();

    if ($housingCategoryFiles->isEmpty()) {
        return response()->json(['error' => 'Aucune catégorie trouvée pour la validation'], 404);
    }

    $user_id = $housingCategoryFiles->first()->housing->user_id;

    foreach ($housingCategoryFiles as $housingCategoryFile) {
        $housingCategoryFile->update(['is_verified' => true]);
    }

    $notification = new Notification([
        'name' => "Votre ajout de catégorie a été validé avec succès par l'administrateur",
        'user_id' => $user_id,
    ]);
    $notification->save();
    $mail = [
        'title' => "Validation de  la catégorie ajoutée au logement",
        'body' => "L'ajout de cette catégorie : " . $category->name . " a été validé par l'administrateur.",
    ];
    Mail::to($housingCategoryFiles->first()->housing->user->email)->send(new NotificationEmailwithoutfile($mail));

    return response()->json(['message' => 'Catégories validées avec succès'], 200);
}




/**
 * @OA\Get(
 *      path="/api/logement/category/unexist/invalid",
 *      tags={"Housing Category Photo"},
 *     security={{"bearerAuth": {}}},
 *      summary="Récupérer les catégories inexistantes par défaut des logement en attente de validation",
 *      description="Retourne une liste des catégories inexistantes par défaut des logement en attente de validation et les photos des catégories associées.",
 *      @OA\Response(
 *          response=200,
 *          description="Succès - Liste des logements par catégorie",
 *          @OA\JsonContent(
 *          )
 *      ),
 * )
 */
public function getCategoryUnexistInvalidHousings()
{
    $categories = Category::where('is_verified', false)->get();

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

            // Vérifier si le housing_id et category_id existent déjà dans $data
            $existingHousingIndex = null;
            foreach ($data as $index => $existingHousing) {
                if ($existingHousing['housing_id'] == $housingId && $existingHousing['category_id'] == $category->id) {
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
                    'user_id' => $housingCategoryFile->housing->user->id,
                    'user_lastname' => $housingCategoryFile->housing->user->lastname,
                    'user_firstname' => $housingCategoryFile->housing->user->firstname,
                    'is_verified' => $housingCategoryFile->is_verified,
                ];

                $data[] = $housingData;
            } else {
                // Ajouter les photos_category à l'entrée existante
                $data[$existingHousingIndex]['photos_category'] = array_merge(
                    $data[$existingHousingIndex]['photos_category'],
                    $housingCategoryFile->file()->select('id', 'path')->get()->toArray()
                );
            }
        }
    }

    return response()->json(['data' => $data]);
}


/**
 * @OA\Put(
 *      path="/api/logement/category/unexist/{housing_id}/{category_id}/validate",
 *      tags={"Housing Category Photo"},
 *     security={{"bearerAuth": {}}},
 *      summary="Valider une catégorie inexistante par défaut en attente de validation",
 *      description="Valide une catégorie inexistante par défaut en attente de validation en mettant à jour le statut is_verified de housing_category_file à true.",
 *      @OA\Parameter(
 *          name="housing_id",
 *          in="path",
 *          required=true,
 *          description="ID du logement associé à la catégorie",
 *          @OA\Schema(
 *              type="integer",
 *          )
 *      ),
 *      @OA\Parameter(
 *          name="category_id",
 *          in="path",
 *          required=true,
 *          description="ID de la catégorie à valider",
 *          @OA\Schema(
 *              type="integer",
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Succès - Catégorie validée avec succès"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Erreur - Catégorie non trouvée"
 *      )
 * )
 */
public function validateUnexistCategoryHousing($housing_id, $category_id)
{
    $category = Category::find($category_id);

    if (!$category || $category->is_verified) {
        return response()->json(['error' => 'La catégorie est déjà vérifiée, la validation ne peut pas être effectuée'], 400);
    }

    $housing = Housing::find($housing_id);

    if (!$housing) {
        return response()->json(['error' => 'Le logement associé n\'existe pas'], 404);
    }

    $housingCategoryFiles = Housing_category_file::where('housing_id', $housing_id)
        ->where('category_id', $category_id)
        ->get();

    if ($housingCategoryFiles->isEmpty()) {
        return response()->json(['error' => 'Aucune catégorie trouvée pour la validation'], 404);
    }

    $user_id = $housingCategoryFiles->first()->housing->user_id;

    foreach ($housingCategoryFiles as $housingCategoryFile) {
        $housingCategoryFile->update(['is_verified' => true]);
    }
    $category->update(['is_verified' => true]);

    $notification = new Notification([
        'name' => "Votre ajout de catégorie a été validé avec succès par l'administrateur",
        'user_id' => $user_id,
    ]);
    $notification->save();
    $mail = [
        'title' => "Validation de  la catégorie ajoutée au logement",
        'body' => "L'ajout de cette catégorie : " . $category->name . " a été validé par l'administrateur.",
    ];
    Mail::to($housingCategoryFiles->first()->housing->user->email)->send(new NotificationEmailwithoutfile($mail));

    return response()->json(['message' => 'Catégories validées avec succès'], 200);
}

/**
 * @OA\Get(
 *      path="/api/logement/category/{housing_id}/{category_id}/detail",
 *      tags={"Housing Category Photo"},
 *      security={{"bearerAuth": {}}},
 *      summary="Obtenir les détails d'une catégorie donné de logement",
 *      description="Retourne les détails d'une catégorie de logement spécifique, y compris les informations détaillées du logement,les photos de la categorie et de l'utilisateur associé.",
 *      @OA\Parameter(
 *          name="housing_id",
 *          in="path",
 *          required=true,
 *          description="ID du logement",
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),
 *      @OA\Parameter(
 *          name="category_id",
 *          in="path",
 *          required=true,
 *          description="ID de la catégorie",
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Succès - Détails de la catégorie de logement retournés",
 *          @OA\JsonContent(
 *          )
 *      ),

 * )
 */

 public function getCategoryDetail($housing_id, $category_id)
 {
     $categories = Category::all();
 
     $data = [];
 
     foreach ($categories as $category) {
         $housingCategoryFiles = Housing_category_file::where('category_id', $category_id)
             ->where('housing_id', $housing_id)
             ->with('housing.user')
             ->get();
 
         foreach ($housingCategoryFiles as $housingCategoryFile) {
             $housingId = $housingCategoryFile->housing->id;
 
             $existingHousingIndex = null;
             foreach ($data as $index => $existingHousing) {
                 if ($existingHousing['housing_id'] === $housingId && $existingHousing['category_id'] === $category_id) {
                     $existingHousingIndex = $index;
                     break;
                 }
             }
 
             if ($existingHousingIndex === null) {
                 $housingData = [
                     'housing_category_file_id' => $housingCategoryFile->id,
                     'category_id' => $category_id,
                     'category_name' => $category->name,
                     'is_verified' => $housingCategoryFile->is_verified,
                     'housing_id' => $housingId,
                     'housing_details' => $housingCategoryFile->housing->toArray(),
                     'photos_category' => [],
                     'user_id' => $housingCategoryFile->housing->user->id,
                     'user_details' => $housingCategoryFile->housing->user->toArray(),
                 ];
 
                 $data[] = $housingData;
             }
 
             $photos = $housingCategoryFile->file()->select('id', 'path')->get()->toArray();
             foreach ($photos as $photo) {
                 if (!isset($data[$existingHousingIndex]['photos_category'][$photo['id']])) {
                     $data[$existingHousingIndex]['photos_category'][$photo['id']] = $photo;
                 }
             }
         }
     }
 
     foreach ($data as &$housing) {
         $housing['photos_category'] = array_values($housing['photos_category']);
     }
 
     return response()->json(['data' => $data]);
 }
 


}