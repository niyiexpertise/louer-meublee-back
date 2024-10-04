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

    public function __construct(FileService $fileService=null)
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
         'photos.*' => 'image|mimes:jpeg,png,jpg,gif',
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
         $identity_profil_url = $this->fileService->uploadFiles($photo, 'image/photo_category', 'extensionImageVideo');
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
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
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
            return (new ServiceController())->apiResponse(404,[], 'ucune relation entre le logement et la catégorie trouvée.');
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

        return (new ServiceController())->apiResponse(200,[], 'Catégorie et fichiers associés supprimés avec succès.');

    } catch (\Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
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
         'photos' => 'nullable',
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
 * @OA\Put(
 *     path="/api/logement/category/photo/validate",
 *     summary="Mettre à jour le statut de vérification des fichiers de logement",
 *     tags={"Housing Category Photo"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"photo_ids"},
 *             @OA\Property(property="photo_ids", type="array", @OA\Items(type="integer"), description="Tableau d'IDs des fichiers à valider")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Photos validées avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", description="Message de confirmation"),
 *             @OA\Property(property="photos", type="array", @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", description="ID du fichier"),
 *                 @OA\Property(property="message", type="string", description="Message concernant la validation"),
 *                 @OA\Property(property="is_verified", type="boolean", description="Statut de vérification"),
 *                 @OA\Property(property="status", type="string", description="Statut de l'opération")
 *             ))
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucune photo validée ou ID non trouvé",
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

 public function validateHousingCategoryFile(Request $request)
{
    try {
        $photoIds = $request->input('photo_ids');

        // Vérifier si les IDs de photo sont fournis et si le format est correct
        if (empty($photoIds) || !is_array($photoIds)) {
            return (new ServiceController())->apiResponse(404, [], "Aucun ID de photo fourni ou le format est incorrect.");
        }

        foreach ($photoIds as $id) {
                $housingCategoryFile = Housing_category_file::where('file_id', $id)->first();
                if (!$housingCategoryFile) {
                    return (new ServiceController())->apiResponse(404,$id,"Le fichier de logement avec cet ID n'a pas été trouvé");
                }

                if(Housing_category_file::whereHousingId($housingCategoryFile->housing_id)->whereCategoryId($housingCategoryFile->category_id)->whereIsVerified(false)->exists()){
                    return (new ServiceController())->apiResponse(404,$id,"Vous ne pouvez pas valider ce fichier car la pièce à laquelle elle est associé n'est pas encore validée.");
                }

                $file = File::find($id);
                if (!$file) {
                    return (new ServiceController())->apiResponse(404,$id,"Le fichier associé n'a pas été trouvé',
                        'status");
                }

                if ($housingCategoryFile->is_verified) {
                    return (new ServiceController())->apiResponse(404, $id, 'Le fichier a déjà été validé');
                }

                $housingCategoryFile->is_verified = true;
                $housingCategoryFile->save();

                Housing_category_file::where('file_id', $id)->update(["is_verified" =>true]);

                $mail = [
                    'title' => "Validation de la photo ajoutée à la catégorie de votre logement",
                    'body' => "Votre ajout de la photo à la catégorie a été validé avec succès par l'administrateur.",
                ];

                dispatch(new SendRegistrationEmail($housingCategoryFile->housing->user->email, $mail['body'], $mail['title'], 2));

        }

        return (new ServiceController())->apiResponse(200, $id, 'Statut de vérification mis à jour avec succès');


    } catch (\Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}

 

    /**
 * @OA\Post(
 *     path="/api/logement/category/updateHousingCategoryNumber/{housingId}/{categoryId}",
 *     summary="Mettre à jour le nombre de pièces d'une catégorie de logement",
 *     description="Cette API permet de mettre à jour le nombre de pièces d'une catégorie spécifique de logement.",
 *    tags={"Housing Category Photo"},
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         description="ID du logement à mettre à jour",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 * @OA\Parameter(
 *         name="categoryId",
 *         in="path",
 *         description="ID de la catégorie de logement à mettre à jour",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="number", type="integer", example=2, description="Le nombre de pièces à mettre à jour")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Nombre de pièces mis à jour avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Nombre de pièce modifié avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Accès refusé",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Vous n'avez pas le droit de modifier le nombre de pièces d'un logement qui ne vous appartient pas")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Logement ou pièce non trouvée",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Le logement auquel appartient cette pièce n'existe pas")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur interne",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
 *         )
 *     ),
 *     security={{ "bearerAuth": {} }}
 * )
 */


    public function updateHousingCategoryNumber(Request $request,$housingId,$categoryId){
        try {

            $request->validate([
                'number' => 'required'
            ]);

           $piece =  Housing_category_file::where('housing_id',$housingId)->where('category_id',$categoryId)->first();

           if(!$piece){
                return (new ServiceController())->apiResponse(404,[],'Piece non trouvé');
           }

           if(!Housing::whereId($piece->housing_id)->first()){
                return (new ServiceController())->apiResponse(404,[],"Le logement auquel appartient cette pièce n'existe pas");
           }

           if(Auth::user()->id != Housing::whereId($piece->housing_id)->first()->user_id){
            return (new ServiceController())->apiResponse(403,[],'Vous n\'avez pas le droit de modifié le nombre de piece d\un logement qui ne vous appartient pas');
           }

           if(intval($request->number)<=0){
            return (new ServiceController())->apiResponse(404,[],'Le nombre de pièce ne peut être inférieur ou égal à 0');
           }

           Housing_category_file::where('housing_id',$housingId)->where('category_id',$categoryId)->update(['number' => $request->number]);

           Housing_category_file::where('housing_id',$housingId)->where('category_id',$categoryId)->update(['is_verified' => false]);

           return (new ServiceController())->apiResponse(200,[],'Nombre de pièce modifié avec succès');

        } catch (\Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

    /**
 * @OA\Get(
 *     path="/api/logement/category/getHousingCategoryFile/{housingId}/{categoryId}",
 *     summary="Obtenir les fichiers d'une catégorie pour un logement",
 *     description="Récupère les fichiers associés à une catégorie (pièce) spécifique pour un logement donné.",
 *     operationId="getHousingCategoryFile",
 *    tags={"Housing Category Photo"},
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         required=true,
 *         description="ID du logement",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="categoryId",
 *         in="path",
 *         required=true,
 *         description="ID de la catégorie (pièce)",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Fichiers de la catégorie récupérés avec succès",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", description="ID du fichier"),
 *                 @OA\Property(property="name", type="string", description="Nom du fichier"),
 *                 @OA\Property(property="path", type="string", description="Chemin du fichier")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Logement ou catégorie non trouvé(e)",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Logement ou catégorie non trouvé(e)")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Accès refusé",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Vous n'avez pas le droit d'afficher les photos d'une pièce d'un logement qui ne vous appartiennent pas")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */

    public function getHousingCategoryFile($housingId,$categoryId){
        try {
    
           $housing =  Housing::whereId($housingId)->first();

    
           if(!$housing){
                return (new ServiceController())->apiResponse(404,[],'Logement non trouvée');
           }

           $category =  Category::whereId($categoryId)->first();

    
           if(!$category){
                return (new ServiceController())->apiResponse(404,[],'Catégorie non trouvée');
           }
    
           if(Auth::user()->id != $housing->user_id){
            return (new ServiceController())->apiResponse(403,[],'Vous n\'avez pas le droit d\'afficher les photos d\une pièce d\'un logement qui ne vous appartiennent pas');
           }

           $data = [];
    
           $fileIds = Housing_category_file::where('housing_id',$housing->id)
           ->where('category_id',$category->id)
           ->pluck('file_id')
           ->toArray();

        //    return $fileIds;

           $data = File::whereIn('id',$fileIds)->get();
         

           return (new ServiceController())->apiResponse(200,$data,'Photos d\'une pièce d\'un logement');
    
        } catch (\Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }



    /**
 * @OA\Get(
 *    path="/api/logement/category/getRemainingCategories/{housingId}",
 *     summary="Récupérer les catégories (pièces) restant à ajouter à un logement",
 *     description="Cette fonction retourne la liste des catégories (pièces) qui n'ont pas encore été associées au logement donné.",
 *     operationId="getRemainingCategories",
 *     tags={"Housing Category Photo"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         description="ID du logement pour lequel on veut récupérer les catégories restantes",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des catégories restantes à ajouter au logement",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="remaining_categories",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Salon"),
 *                     @OA\Property(property="icone", type="string", example="icone_salon.png"),
 *                     @OA\Property(property="is_verified", type="boolean", example=true)
 *                 )
 *             ),
 *             @OA\Property(property="nombre", type="integer", example=3)
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Logement non trouvé",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Logement non trouvé")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
 *         )
 *     ),
 * )
 */


    public function getRemainingCategories($housingId)
    {
        $allCategories = Category::where('is_deleted', false)
                                 ->where('is_blocked', false)
                                //  ->where('is_verified', true)
                                 ->pluck('id')
                                 ->toArray();
    
        $associatedCategories = DB::table('housing_category_files')
                                   ->where('housing_id', $housingId)
                                //    ->where('is_verified', true) 
                                   ->pluck('category_id')
                                   ->toArray();
          

        $remainingCategories = array_diff($allCategories, $associatedCategories);
        $categories = Category::whereIn('id', $remainingCategories) ->where('is_verified', true)->get();
    
        return response()->json([
            'remaining_categories' => $categories,
            'nombre' => count($remainingCategories),
        ], 200);
    }
    


    /**
 * @OA\Get(
 *     path="/api/logement/category/getHousingCategories/{housingId}",
 *     summary="Récupérer les catégories (pièces) d'un logement",
 *  security={{"bearerAuth": {}}},
 *     description="Cette fonction retourne la liste des catégories (pièces) associées à un logement spécifique, sans duplications, et excluant les catégories supprimées ou bloquées.",
 *     tags={"Housing Category Photo"},
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         description="ID du logement pour lequel on souhaite récupérer les catégories",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             example=1
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des catégories associées au logement",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Salon"),
 *                 @OA\Property(property="icone", type="string", example="icone_salon.png"),
 *                 @OA\Property(property="is_verified", type="boolean", example=true),
 *                 @OA\Property(property="is_deleted", type="boolean", example=false),
 *                 @OA\Property(property="is_blocked", type="boolean", example=false)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucune catégorie trouvée pour ce logement",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Aucune catégorie trouvée pour ce logement"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Erreur interne du serveur"
 *             )
 *         )
 *     )
 * )
 */

     public function getHousingCategories($housingId,$l=0)
     {
         try {
             $categoryIds = Housing_category_file::where('housing_id', $housingId)
                 ->distinct()
                 ->pluck('category_id');
     
             $categories = Category::whereIn('id', $categoryIds)
                 ->where('is_deleted', false)
                 ->where('is_blocked', false)
                 ->get();

            foreach($categories as $category){
                $category->number = Housing_category_file::whereCategoryId($category->id)->first()->number;
            }
     
             if ($categories->isEmpty()) {
                 return (new ServiceController())->apiResponse(404, [], 'Aucune catégorie trouvée pour ce logement');
             }

             if($l==1){
                return $categories;
             }
     
             return (new ServiceController())->apiResponse(200, $categories, 'Liste des catégories du logement');
     
         } catch (\Exception $e) {
             return (new ServiceController())->apiResponse(500, [], $e->getMessage());
         }
     }
     

     /**
 * @OA\Post(
 *     path="/api/logement/category/addCategoryToHousing/{housingId}",
 *     summary="Ajouter des catégories à un logement",
 *     tags={"Housing Category Photo"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         description="L'ID du logement auquel on veut ajouter des catégories",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="categorieIds",
 *                     type="array",
 *                     description="Tableau des IDs des catégories existantes à associer au logement",
 *                     @OA\Items(type="integer")
 *                 ),
 *                 @OA\Property(
 *                     property="numbers",
 *                     type="array",
 *                     description="Tableau des quantités correspondant aux catégories existantes",
 *                     @OA\Items(type="integer")
 *                 ),
 *                 @OA\Property(
 *                     property="newCategorieNames",
 *                     type="array",
 *                     description="Tableau des noms des nouvelles catégories à ajouter",
 *                     @OA\Items(type="string")
 *                 ),
 *                 @OA\Property(
 *                     property="newNumber",
 *                     type="array",
 *                     description="Tableau des quantités correspondant aux nouvelles catégories",
 *                     @OA\Items(type="integer")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Catégories ajoutées avec succès"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erreur de validation des données"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="La catégorie ou la quantité spécifiée est invalide"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur interne"
 *     )
 * )
 */


     public function addCategoryToHousing(Request $request, $housingId)
{
    try {
        $request->validate([
            'categorieIds' => 'required|array',
            'numbers' => 'required|array',
            'newCategorieNames' => 'nullable|array',
            'newNumber' => 'nullable|array'
        ]);

        $categorieIds = $request->categorieIds;
        $numbers = $request->numbers;
        $newCategorieNames = $request->newCategorieNames ?? [];
        $newNumber = $request->newNumber ?? [];

        if (count($categorieIds) !== count($numbers)) {
            return (new ServiceController())->apiResponse(404, [], 'Le nombre de catégories et de quantités ne correspond pas.');
        }

        if (count($newCategorieNames) !== count($newNumber)) {
            return (new ServiceController())->apiResponse(404, [], 'Le nombre de nouvelles catégories et de quantités ne correspond pas.');
        }

        foreach ($categorieIds as $index => $categorieId) {
            $category = Category::find($categorieId);
            if (!$category) {
                return (new ServiceController())->apiResponse(404, [], "La catégorie avec l'ID $categorieId n'existe pas.");
            }

            if (!is_numeric($numbers[$index]) || intval($numbers[$index]) <= 0) {
                return (new ServiceController())->apiResponse(404, [], "La quantité pour la catégorie avec l'ID $categorieId doit être un nombre positif.");
            }

            $existingRecord = Housing_category_file::where('housing_id', $housingId)
                ->where('category_id', $categorieId)
                ->first();
            if ($existingRecord) {
                return (new ServiceController())->apiResponse(404, [], "Une catégorie avec l'ID $categorieId est déjà associée à ce logement.");
            }
        }

        foreach ($newCategorieNames as $index => $newCategorieName) {
            if (Category::where('name', $newCategorieName)->exists()) {
                return (new ServiceController())->apiResponse(404, [], "Une catégorie avec le nom '$newCategorieName' existe déjà.");
            }

            if (!is_numeric($newNumber[$index]) || intval($newNumber[$index]) <= 0) {
                return (new ServiceController())->apiResponse(404, [], "La quantité pour la nouvelle catégorie '$newCategorieName' doit être un nombre positif.");
            }
        }

        foreach ($categorieIds as $index => $categorieId) {
            $housingCategoryFile = new Housing_category_file();
            $housingCategoryFile->housing_id = $housingId;
            $housingCategoryFile->category_id = $categorieId;
            $housingCategoryFile->number = $numbers[$index];
            $housingCategoryFile->is_verified= false;
            $housingCategoryFile->save();
        }

        foreach ($newCategorieNames as $index => $newCategorieName) {
            $newCategory = new Category();
            $newCategory->name = $newCategorieName;
            $newCategory->is_verified= false;
            $newCategory->save();

            $housingCategoryFile = new Housing_category_file();
            $housingCategoryFile->housing_id = $housingId;
            $housingCategoryFile->category_id = $newCategory->id;
            $housingCategoryFile->number = $newNumber[$index];
            $housingCategoryFile->is_verified= false;
            $housingCategoryFile->save();
        }

        $right = Right::where('name','admin')->first();
        $adminUsers = User_right::where('right_id', $right->id)->get();

        foreach ($adminUsers as $adminUser) {


            $mail = [
                "title" => "Ajout d'une/de nouvelle(s) catégorie(s) à un logement",
                "body" => "Un hote vient d'ajouter une/de categorie(s) photo(s) à un logement."
            ];
       
            dispatch( new SendRegistrationEmail($adminUser->user->email, $mail['body'], $mail['title'], 2));
                  }

        return (new ServiceController())->apiResponse(200, [], 'Catégories ajoutées avec succès.');
    } catch (\Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
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
    try {
        $housings = Housing::all();

        $data = [];

        foreach ($housings as $housing) {
            $categories = Housing_category_file::where('housing_id', $housing->id)
                ->distinct()
                ->pluck('category_id');

            $categoriesWithFiles = [];

            foreach ($categories as $categoryId) {
                $category = Category::whereId($categoryId)
                    ->where('is_deleted', false)
                    ->where('is_blocked', false)
                    ->first();

                if ($category) {
                    $unverifiedFiles = Housing_category_file::where('housing_id', $housing->id)
                        ->where('category_id', $categoryId)
                        ->where('is_verified', false)
                        ->pluck('file_id');

                    $files = File::whereIn('id', $unverifiedFiles)->get();

                    if ($files->isNotEmpty()) {
                        $categoriesWithFiles[] = [
                            'category' => $category,
                            'unverified_photos' => $files
                        ];
                    }
                }
            }

            if (!empty($categoriesWithFiles)) {
                $data[] = [
                    'housing_id' => $housing->id, 
                    'user_id' => $housing->user->id,
                    'housing_name' => $housing->name??"non renseigné",
                    'categories_with_unverified_photos' => $categoriesWithFiles
                ];
            }
        }

        if (empty($data)) {
            return (new ServiceController())->apiResponse(404, [], 'Aucune photo non vérifiée trouvée pour les logements');
        }

        return (new ServiceController())->apiResponse(200, $data, 'Liste des logements avec photos non vérifiées par catégorie');
    } catch (\Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}

/**
 * @OA\Get(
 *     path="/api/logement/category/getHousingCategoryFiles/{isexist}",
 *     tags={"Housing Category Photo"},
 *     security={{"bearerAuth": {}}},
 *     summary="Récupérer les logements avec des photos non vérifiées par catégorie",
 *     description="Retourne une liste des logements avec des photos non vérifiées par catégorie",
 *     @OA\Parameter(
 *         name="isexist",
 *         in="path",
 *         required=true,
 *         description="Valeur booléenne pour filtrer les catégories vérifiées ou non",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements avec photos non vérifiées par catégorie",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="housing_id",
 *                 type="integer",
 *                 description="ID du logement"
 *             ),
 *             @OA\Property(
 *                 property="housing_name",
 *                 type="string",
 *                 description="Nom du logement"
 *             ),
 *             @OA\Property(
 *                 property="categories_with_unverified_photos",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(
 *                         property="category",
 *                         type="object",
 *                         description="Catégorie du logement"
 *                     ),
 *                     @OA\Property(
 *                         property="unverified_photos",
 *                         type="array",
 *                         @OA\Items(
 *                             @OA\Property(
 *                                 property="file_id",
 *                                 type="integer",
 *                                 description="ID du fichier photo"
 *                             )
 *                         )
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucune photo non vérifiée trouvée pour les logements"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur"
 *     )
 * )
 */



public function getHousingCategoryFiles($isexist)
{
    try {
        $housings = Housing::all();

        $data = [];

        if(intval($isexist)!=0 && intval($isexist)!=1){
            return (new ServiceController())->apiResponse(404, [], "isexiste doit avoir pour valeur 1 ou 0");
        }

        foreach ($housings as $housing) {
            $categories = Housing_category_file::where('housing_id', $housing->id)
                ->distinct()
                ->pluck('category_id');

            $categoriesWithFiles = [];

            foreach ($categories as $categoryId) {
                $category = Category::whereId($categoryId)
                    ->where('is_deleted', false)
                    ->where('is_blocked', false)
                    ->where('is_verified', $isexist)
                    ->first();

               

                if ($category) {
                    $unverifiedFiles = Housing_category_file::where('housing_id', $housing->id)
                        ->where('category_id', $categoryId)
                        ->where('is_verified', false)
                        ->pluck('file_id');

                    $files = File::whereIn('id', $unverifiedFiles)->get();

                    if ($files->isNotEmpty()) {
                        $categoriesWithFiles[] = [
                            'category' => $category,
                            'unverified_photos' => $files
                        ];
                    }
                }
            }

            if (!empty($categoriesWithFiles)) {
                $data[] = [
                    'housing_id' => $housing->id, 
                    'housing_name' => $housing->name??"non renseigné", 
                    'user_id' => $housing->user->id,
                    'categories_with_unverified_photos' => $categoriesWithFiles
                ];
            }
        }

        if (empty($data)) {
            return (new ServiceController())->apiResponse(404, [], 'Aucune photo non vérifiée trouvée pour les logements');
        }

        return (new ServiceController())->apiResponse(200, $data, 'Liste des logements avec photos non vérifiées par catégorie');
    } catch (\Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}

/**
 * @OA\Post(
 *      path="/api/logement/category/default/validate",
 *      tags={"Housing Category Photo"},
 *      security={{"bearerAuth": {}}}, 
 *      summary="Valider plusieurs catégories par défaut en attente de validation pour différents logements",
 *      description="Valide plusieurs catégories par défaut en attente de validation pour différents logements en mettant à jour le statut is_verified de housing_category_file à true pour chaque catégorie.",
 *      @OA\RequestBody(
 *          required=true,
 *          description="Tableau contenant les IDs des logements et les listes de catégories à valider",
 *          @OA\JsonContent(
 *              type="array",
 *              @OA\Items(
 *                  type="object",
 *                  @OA\Property(
 *                      property="housing_id",
 *                      type="integer",
 *                      description="ID du logement associé"
 *                  ),
 *                  @OA\Property(
 *                      property="categoryIds",
 *                      type="array",
 *                      description="Liste des IDs des catégories à valider",
 *                      @OA\Items(
 *                          type="integer"
 *                      )
 *                  )
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Succès - Toutes les catégories ont été validées avec succès"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Erreur - Logement ou catégorie non trouvés"
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Erreur - Certaines catégories ne sont pas vérifiées ou sont invalides"
 *      )
 * )
 */

 public function validateDefaultCategoriesHousing(Request $request)
 {
     try {
         $housingCategories = $request->all();
 
     foreach ($housingCategories as $housingCategory) {
         $housing_id = $housingCategory['housing_id'];
         $categoryIds = $housingCategory['categoryIds'];
 
         $housing = Housing::find($housing_id);
 
         if (!$housing) {
             return response()->json(['error' => "Le logement avec l'ID $housing_id n'existe pas"], 404);
         }
 
         foreach ($categoryIds as $categoryId) {
             $category = Category::find($categoryId);
 
             if (!$category || !$category->is_verified) {
                 return (new ServiceController())->apiResponse(404, [], "La catégorie avec l'ID $categoryId pour le logement $housing_id n'est pas vérifiée ou n'existe pas");
             }
 
             $housingCategoryFiles = Housing_category_file::where('housing_id', $housing_id)
                 ->where('category_id', $categoryId)
                 ->get();
 
             if ($housingCategoryFiles->isEmpty()) {
                 return (new ServiceController())->apiResponse(404, [],"Aucune catégorie trouvée pour le logement $housing_id et la catégorie $categoryId");
             }
 
             if(!Housing_category_file::where('housing_id', $housing_id)
             ->where('category_id', $categoryId)
             ->where('is_verified',false)
             ->exists()){
                 return (new ServiceController())->apiResponse(404, [],"Aucune catégorie en attente de validation pour le logement $housing_id et la catégorie $categoryId");
             }
 
             foreach ($housingCategoryFiles as $housingCategoryFile) {
                 $housingCategoryFile->update(['is_verified' => true]);
             }
 
             $user_id = $housing->user_id;
 
             $notification = new Notification([
                 'name' => "Votre ajout de catégorie a été validé avec succès par l'administrateur",
                 'user_id' => $user_id,
             ]);
             $notification->save();
 
             $mail = [
                 'title' => "Validation de la catégorie ajoutée au logement",
                 'body' => "L'ajout de cette catégorie : " . $category->name . " a été validé par l'administrateur.",
             ];
 
             dispatch(new SendRegistrationEmail($housing->user->email, $mail['body'], $mail['title'], 2));
         }
     }
 
     return (new ServiceController())->apiResponse(200, [], 'Catégories validées avec succès pour tous les logements');
     } catch (\Exception $e) {
         return (new ServiceController())->apiResponse(500, [], $e->getMessage());
     }
 
 }


 /**
 * @OA\Post(
 *      path="/api/logement/category/inexistant/validate",
 *      tags={"Housing Category Photo"},
 *      security={{"bearerAuth": {}}}, 
 *      summary="Valider plusieurs catégories inexistantes en attente de validation pour différents logements",
 *      description="Valide plusieurs catégories inexistantes en attente de validation pour différents logement.",
 *      @OA\RequestBody(
 *          required=true,
 *          description="Tableau contenant les IDs des logements et les listes de catégories à valider",
 *          @OA\JsonContent(
 *              type="array",
 *              @OA\Items(
 *                  type="object",
 *                  @OA\Property(
 *                      property="housing_id",
 *                      type="integer",
 *                      description="ID du logement associé"
 *                  ),
 *                  @OA\Property(
 *                      property="categoryIds",
 *                      type="array",
 *                      description="Liste des IDs des catégories à valider",
 *                      @OA\Items(
 *                          type="integer"
 *                      )
 *                  )
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Succès - Toutes les catégories ont été validées avec succès"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Erreur - Logement ou catégorie non trouvés"
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Erreur - Certaines catégories ne sont pas vérifiées ou sont invalides"
 *      )
 * )
 */
public function validateInexistantCategoriesHousing(Request $request)
{
    $data = $request->all();

    foreach ($data as $housingData) {
        $housing_id = $housingData['housing_id'];
        $categoryIds = $housingData['categoryIds'];

        foreach ($categoryIds as $category_id) {
            $housingCategoryFile = Housing_category_file::where('housing_id', $housing_id)
                ->where('category_id', $category_id)
                ->first();

            if (!$housingCategoryFile) {
                return (new ServiceController())->apiResponse(404, [], 'Aucune catégorie trouvée pour le logement ID : ' . $housing_id . ' et la catégorie ID : ' . $category_id);
            }

            if ($housingCategoryFile->is_verified == true) {
                return (new ServiceController())->apiResponse(404, [], 'La catégorie ID : ' . $category_id . ' pour le logement ID : ' . $housing_id . ' est déjà vérifiée');
            }

            $category = Category::find($category_id);

            if (!$category || $category->is_verified == true) {
                return (new ServiceController())->apiResponse(404, [], 'La catégorie ID : ' . $category_id . ' est déjà vérifiée ou n\'existe pas');
            }

            $category->update(['is_verified' => true]);

            $housingCategoryFile->update(['is_verified' => true]);

            $user_id = $housingCategoryFile->housing->user_id;

            $notification = new Notification([
                'name' => "Votre nouvelle pièce a été validée avec succès par l'administrateur",
                'user_id' => $user_id,
            ]);
            $notification->save();

            $mail = [
                'title' => "Validation de la nouvelle pièce",
                'body' => "La nouvelle pièce pour la catégorie : " . $category->name . " a été validée par l'administrateur.",
            ];

            dispatch(new SendRegistrationEmail($housingCategoryFile->housing->user->email, $mail['body'], $mail['title'], 2));
        }
    }

    return (new ServiceController())->apiResponse(200, [], 'Toutes les nouvelles pièces ont été validées avec succès');
}



}
