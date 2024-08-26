<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationEmail;
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
use Illuminate\Support\Facades\Response;
use App\Models\User_right;
use App\Models\Right;
use App\Services\FileService;
use Illuminate\Support\Facades\DB;

class HousingCategoryFileController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

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
     $identity_profil_url = '';
     foreach ($request->file('photos') as $photo) {
         $photoModel = new File();
         $identity_profil_url = $this->fileService->uploadFiles($photo, 'image/photo_category', 'extensionImageVideo');;
         if ($identity_profil_url['fails']) {
            return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
        }
         $photoModel->path = $identity_profil_url['result'];
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

    $mailhote = [
        "title" => "Notification ajout d'une pièce à un logement",
        "body" => "Votre ajout de la pièce a été pris en compte. l'administrateur validera dans moin de 48h."
     ];

    dispatch( new SendRegistrationEmail(User::whereId($userId)->first()->email, $mailhote['body'], $mailhote['title'], 2));

     $right = Right::where('name','admin')->first();
     $adminUsers = User_right::where('right_id', $right->id)->get();
     foreach ($adminUsers as $adminUser) {


     $mail = [
         "title" => "Ajout d'une/de nouvelle(s) pièce(s) à un logement",
         "body" => "Un hôte  vient d'ajouter sur le site une nouvelle pièce pour son logement.Veuilez vous connecter pour valider."
     ];

     dispatch( new SendRegistrationEmail($adminUser->user->email, $mailhote['body'], $mailhote['title'], 2));
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

    $identity_profil_url = '';
    foreach ($request->file('photos') as $photo) {
        $photoModel = new File();
        $identity_profil_url = $this->fileService->uploadFiles($photo, 'image/photo_category', 'extensionImageVideo');;
        if ($identity_profil_url['fails']) {
            return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
        }
        $photoModel->path = $identity_profil_url['result'];
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
       $mailhote = [
        "title" => "Notification ajout d'une pièce à un logement",
        "body" => "Votre ajout de la pièce a été pris en compte. l'administrateur validera dans moin de 48h."
     ];

    dispatch( new SendRegistrationEmail($request->email, $mailhote['body'], $mailhote['title'], 2));

     $right = Right::where('name','admin')->first();
     $adminUsers = User_right::where('right_id', $right->id)->get();
     foreach ($adminUsers as $adminUser) {
     $notification = new Notification();
     $notification->user_id = $adminUser->user_id;
     $notification->name = "Un hôte  vient d'ajouter sur le site une nouvelle pièce pour son logement.Veuilez vous connecter pour valider.";
     $notification->save();

     $mail = [
         "title" => "Ajout d'une/de nouvelle(s) pièce(s) à un logement",
         "body" => "Un hôte  vient d'ajouter sur le site une nouvelle pièce pour son logement.Veuilez vous connecter pour valider."
     ];


     dispatch( new SendRegistrationEmail($adminUser->user->email, $mail['body'], $mail['title'], 2));
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

    dispatch( new SendRegistrationEmail($housingCategoryFiles->first()->housing->user->email, $mail['body'], $mail['title'], 2));

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

    $mail = [
        'title' => "Validation de  la catégorie ajoutée au logement",
        'body' => "L'ajout de cette catégorie : " . $category->name . " a été validé par l'administrateur.",
    ];

    dispatch( new SendRegistrationEmail($housingCategoryFiles->first()->housing->user->email, $mail['body'], $mail['title'], 2));


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

 public function getCategoryDetail($housingId, $categoryId)
{
    try {

        $housing = Housing::with('user')->find($housingId);

        if (!$housing) {
            return response()->json(['error' => 'Logement non trouvé.'], 404);
        }

        // Récupérer la catégorie par son ID
        $category = Category::find($categoryId);

        if (!$category) {
            return response()->json(['error' => 'Catégorie non trouvée.'], 404);
        }

        // Récupérer les relations entre le logement et la catégorie
        $housingCategoryFiles = Housing_category_file::where('housing_id', $housingId)
                                                   ->where('category_id', $categoryId)
                                                   ->get();

        if ($housingCategoryFiles->isEmpty()) {
            return response()->json(['error' => 'Aucune relation entre le logement et la catégorie trouvée.'], 404);
        }

        $photos = [];
        foreach ($housingCategoryFiles as $housingCategoryFile) {
            $file = File::find($housingCategoryFile->file_id);

            if ($file) {
                $path = parse_url($file->path, PHP_URL_PATH);
                $photos[] = [
                    'id' => $file->id,
                    'path' => $file->path,
                ];
            }
        }

        $number = $housingCategoryFiles->first()->number;

        return response()->json([
            'housing' => $housing,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'number' => $number,
                'is_verified' => $category->is_verified,
                'photos' => $photos,

            ],
        ], 200);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



/**
 * @OA\Delete(
 *     path="/api/logement/{housingId}/category/{categoryId}/delete",
 *     summary="Supprimer une catégorie et ses fichiers associés d'un logement",
 *     tags={"Housing Category Photo"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         required=true,
 *         description="ID du logement",
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\Parameter(
 *         name="categoryId",
 *         in="path",
 *         required=true,
 *         description="ID de la catégorie",
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Catégorie et fichiers associés supprimés avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Catégorie et fichiers associés supprimés avec succès."),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucune relation entre le logement et la catégorie trouvée",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Aucune relation entre le logement et la catégorie trouvée."),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Erreur interne du serveur."),
 *         ),
 *     ),
 * )
 */

 public function deleteHousingCategory($housingId, $categoryId)
{
    try {

        $housingCategoryFiles = Housing_category_file::where('housing_id', $housingId)
                              ->where('category_id', $categoryId)
                              ->get();

        if ($housingCategoryFiles->isEmpty()) {
            return response()->json(['error' => 'Aucune relation entre le logement et la catégorie trouvée.'], 400);
        }

        foreach ($housingCategoryFiles as $housingCategoryFile) {
            $file = File::find($housingCategoryFile->file_id);

            if ($file) {
                $path = parse_url($file ->path, PHP_URL_PATH);
                $filePath = public_path($path);

                if (F::exists($filePath)) {
                    F::delete($filePath);
                }

                $file->delete();
            }

            $housingCategoryFile->delete();
        }
        $category = Category::find($categoryId);
        if ($category && !$category->is_verified) {
            $category->delete();
        }

        return response()->json(['message' => 'Catégorie et fichiers associés supprimés avec succès.'], 200);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

 /**
 * @OA\Post(
 *     path="/api/logement/{housingId}/category/{categoryId}/photos/add",
 *     tags={"Housing Category Photo"},
 *     summary="Ajouter des photos à une catégorie d'un logement",
 *     description="Ajouter des photos à une catégorie d'un logement",
 *     security={{"bearerAuth": {}}},
      *     @OA\Parameter(
     *         name="housingId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID du logement"
     *     ),
     *     @OA\Parameter(
     *         name="categoryId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID de la catégorie"
     *     ),

 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="photos[]",
 *                     type="array",
 *                     @OA\Items(type="string", format="binary", description="Image de la catégorie (JPEG, PNG, JPG, GIF, taille max : 2048)")
 *                 ),
 *                 required={"photos[]"}
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Photos ajoutées avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Photos ajoutées avec succès")
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

 public function addPhotosCategoryToHousing(Request $request, $housingId, $categoryId)
 {

     $validator = Validator::make($request->all(), [
         'photos' => 'required',
         'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
     ]);

     if ($validator->fails()) {
         return response()->json(['error' => $validator->errors()], 400);
     }

     $housingCategoryFile = Housing_category_file::where('housing_id', $housingId)
            ->where('category_id', $categoryId)
            ->first();

        if (is_null($housingCategoryFile)) {
            return response()->json([
                'message' => 'Aucune relation trouvée entre ce logement et cette catégorie',
            ], 404);
        }
     $constantNumber = $housingCategoryFile->number;

        $savedFiles = [];

        $identity_profil_url = '';
     foreach ($request->file('photos') as $photo) {
         $photoModel = new File();
         $identity_profil_url = $this->fileService->uploadFiles($photo, 'image/photo_category', 'extensionImageVideo');;
         if ($identity_profil_url['fails']) {
            return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
        }
         $photoModel->path = $identity_profil_url['result'];
         $photoModel->save();

         $housingCategoryFile = new Housing_category_file();
         $housingCategoryFile->housing_id = $housingId;
         $housingCategoryFile->category_id = $categoryId;
         $housingCategoryFile->file_id = $photoModel->id;
         $housingCategoryFile->number = $constantNumber;
         $housingCategoryFile->is_verified= false;
         $housingCategoryFile->save();
         $savedFiles[] = $photoModel;
     }
     $userId = Auth::id();

     $mailhote = [
        'title' => "Notification d'ajout de photo à une pièce",
        'body' => "Votre ajout de la photo a été prise en compte. l'administrateur validera dans moin de 48h.",
    ];

     dispatch( new SendRegistrationEmail($housingCategoryFile->housing->user->email, $mailhote['body'], $mailhote['title'], 2));

     $right = Right::where('name','admin')->first();
     $adminUsers = User_right::where('right_id', $right->id)->get();
     foreach ($adminUsers as $adminUser) {


     $mail = [
         "title" => "Ajout d'une/de nouvelle(s) photo(s) à une catégorie d'un logement",
         "body" => "Un hote vient d'ajouter une/de nouvelle(s) photo(s) pour une categorie d'un logement."
     ];

     dispatch( new SendRegistrationEmail($adminUser->user->email, $mail['body'], $mail['title'], 2));
           }

     return response()->json([
            'message' => 'Photos ajoutées avec succès',
            'housing_id' => $housingId,
            'category_id' => $categoryId,
            'saved_files' => $savedFiles,
        ], 201);
 }

/**
     * @OA\Get(
     *     path="/api/logement/category/photo/unverified",
     *     summary="Récupérer les photos des catégories(pièce) en attente de validation",
     *     tags={"Housing Category Photo"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des  photos des catégories(pièce) en attente de validation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="housing_id", type="integer", description="ID du logement"),
     *                 @OA\Property(property="category_id", type="integer", description="ID de la catégorie"),
     *                 @OA\Property(property="file_path", type="string", description="Chemin du fichier"),
     *                 @OA\Property(property="housing_name", type="string", description="Nom du logement"),
     *                 @OA\Property(property="housing_address", type="string", description="Adresse du logement"),
     *                 @OA\Property(property="owner_name", type="string", description="Nom du propriétaire"),
     *                 @OA\Property(property="owner_email", type="string", description="E-mail du propriétaire"),
     *                 @OA\Property(property="category_name", type="string", description="Nom de la catégorie"),
     *                 @OA\Property(property="is_verified", type="boolean", description="Statut de vérification")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Message d'erreur")
     *         )
     *     )
     * )
     */
public function getUnverifiedHousingCategoryFilesWithDetails()
{

    $unverifiedFiles = Housing_category_file::with(['file', 'housing.user', 'category'])
        ->where('is_verified', false)
        ->whereHas('category', function ($query) {
            $query->where('is_verified', true);
        })
        ->get();

    return response()->json([
        'message' => 'Liste des photos des catégoriesen attente de validation avec détails',
        'data' => $unverifiedFiles,
    ]);
}

 /**
     * @OA\Put(
     *     path="/api/logement/category/photo/{id}/validate",
     *     summary="Mettre à jour le statut de vérification d'un fichier de logement",
     *     tags={"Housing Category Photo"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID du housing_category_file"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statut de vérification mis à jour avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", description="Message de confirmation"),
     *             @OA\Property(property="is_verified", type="boolean", description="Statut de vérification")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Fichier non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", description="Message d'erreur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", description="Message d'erreur")
     *         )
     *     )
     * )
     */
    public function validateHousingCategoryFile($id)
    {
        try {
            $housingCategoryFile = Housing_category_file::find($id);
            if (!$housingCategoryFile) {
                return response()->json([
                    'message' => 'Le fichier de logement avec cet ID n\'a pas été trouvé',
                ], 404);
            }
            $housingCategoryFile->is_verified = true;
            $housingCategoryFile->save();

            $mail = [
                'title' => "Validation de  la photo ajoutée à la catégorie de votre logement",
                'body' => "Votre ajout de la photo à la catégorie a été validé avec succès par l'administrateur.",
            ];

            dispatch( new SendRegistrationEmail($housingCategoryFile->first()->housing->user->email, $mail['body'], $mail['title'], 2));

            return response()->json([
                'message' => 'Statut de vérification mis à jour avec succès',
                'is_verified' => $housingCategoryFile->is_verified,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([

               'message' => 'Une erreur s\'est produite',
            ], 500);
        }
    }

}
