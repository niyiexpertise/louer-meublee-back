<?php

namespace App\Http\Controllers;

use App\Models\photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\Housing;
use App\Models\housing_preference;
use App\Models\reduction;
use App\Models\promotion;
use App\Models\Notification;
use App\Models\User;
use App\Models\Equipment;
use App\Models\Equipment_category;
use App\Models\Housing_equipment;
use App\Models\Housing_category_file;
use App\Services\FileService;
use Exception;
use Illuminate\Support\Facades\Validator;
class PhotoController extends Controller
{

    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

/**
 * @OA\Post(
 *   path="/api/logement/updatephoto/{photo_id}",
 *   tags={"Housing Photo"},
 *   security={{"bearerAuth": {}}},
 *   summary="Mettre à jour la photo d'un logement",
 *   description="Permet à l'hôte de mettre à jour une photo de logement en téléchargeant une nouvelle image",
 *   @OA\Parameter(
 *     name="photo_id",
 *     in="path",
 *     description="ID de la photo à mettre à jour",
 *     required=true,
 *     @OA\Schema(
 *       type="string"
 *     )
 *   ),
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(
 *           property="photo",
 *           type="string",
 *           format="binary",
 *           description="Nouvelle photo de profil de l'utilisateur (JPEG, PNG, JPG, GIF, taille max : 2048)"
 *         ),
 *         required={"photo"}
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Photo de logement mise à jour avec succès",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Photo de logement mise à jour avec succès"),
 *     )
 *   ),
 *   @OA\Response(
 *     response=400,
 *     description="Erreur de validation",
 *     @OA\JsonContent(
 *       @OA\Property(property="error", type="object", additionalProperties={"type": "string"})
 *     )
 *   )
 * )
 */

 public function updatePhotoHousing(Request $request,String $photo_id)
 {
     $userId = Auth::id();
     if (!$userId) {
         return response()->json(['error' => 'Unauthenticated'], 401);
     }

     $photo = photo::find($photo_id);


     $validator = Validator::make($request->all(), [
         'photo' => 'required|image',
     ]);

     if ($validator->fails()) {
         return response()->json(['error' => $validator->errors()], 400);
     }
     $oldProfilePhotoUrl = $photo->path;
     if ($oldProfilePhotoUrl) {
         $parsedUrl = parse_url($oldProfilePhotoUrl);
         $oldProfilePhotoPath = public_path($parsedUrl['path']);
         if (File::exists($oldProfilePhotoPath)) {
             File::delete($oldProfilePhotoPath);
         }
     }

     $identity_profil_url = '';
     $identity_profil_url = $this->fileService->uploadFiles($request->file('photo'), 'image/photo_logement', 'extensionImageVideo');;
     if ($identity_profil_url['fails']) {
        return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
    }
     $photo->path= $identity_profil_url['result'];
     $photo->save();

     return response()->json(['message' => ' photo updated successfully'], 200);
 }
/**
 * @OA\Post(
 *   path="/api/logement/{housingId}/setcoverphoto/{photoId}",
 *   tags={"Housing Photo"},
 *   summary="Définir une nouvelle photo comme couverture d'un logement",
 *   description="Permet à l'utilisateur de définir une nouvelle photo comme couverture pour un logement donné.",
 *   security={{"bearerAuth": {}}},
 *   @OA\Parameter(
 *     name="housingId",
 *     in="path",
 *     required=true,
 *     description="ID du logement",
 *     @OA\Schema(type="integer")
 *   ),
 *   @OA\Parameter(
 *     name="photoId",
 *     in="path",
 *     required=true,
 *     description="ID de la nouvelle photo",
 *     @OA\Schema(type="integer")
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="La nouvelle photo de couverture a été définie avec succès",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="La nouvelle photo de couverture a été définie avec succès")
 *     )
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Le logement ou la photo spécifiée n'existe pas",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Le logement ou la photo spécifiée n'existe pas")
 *     )
 *   ),
 *   @OA\Response(
 *     response=500,
 *     description="Erreur interne du serveur",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Une erreur s'est produite lors de la définition de la nouvelle photo de couverture")
 *     )
 *   )
 * )
 */

 public function setCoverPhoto($housingId, $photoId)
{
    try {
        // Vérifiez d'abord si le logement existe
        $housing = Housing::find($housingId);
        if (!$housing) {
            return response()->json(['message' => 'Le logement n\'existe pas.'], 404);
        }

        // Vérifiez ensuite si la photo existe et est associée au logement
        $photo = $housing->photos()->find($photoId);
        if (!$photo) {
            return response()->json(['message' => 'La photo n\'existe pas ou n\'est pas associée à ce logement.'], 404);
        }

        // Réinitialisez la photo de couverture pour ce logement
        $housing->is_couverture = false;
        $housing->save();


        $photo->is_couverture = true;
        $photo->save();

        return response()->json(['message' => 'La nouvelle photo de couverture a été définie avec succès.'], 200);
    } catch (Exception $e) {
        return response()->json(['message' => 'Une erreur s\'est produite lors de la définition de la nouvelle photo de couverture.'], 500);
    }
}


    /**
 * @OA\Delete(
 *     path="/api/logement/photo/{photoid}",
 *     tags={"Housing Photo"},
 *     summary="Supprimer une photo d'un logement",
 *     description="Supprime une photo d'un logement.",
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
 *         response=400,
 *         description="La photo est actuellement utilisée comme photo de couverture et ne peut pas être supprimée",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="La photo est actuellement utilisée comme photo de couverture et ne peut pas être supprimée.")
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

    public function deletePhotoHousing(Request $request, $id)
    {
        //todo: supprimer un tableau de photo et vérifier si ça appartient à ton logement
        $photo = photo::findOrFail($id);

        if ($photo->is_couverture == 1) {
            return response()->json(['message' => 'La photo est actuellement utilisée comme photo de couverture et ne peut pas être supprimée.'], 400);
        }

        $path = parse_url($photo->path, PHP_URL_PATH);
        $photoPath = public_path($path);

        if (File::exists($photoPath)) {
            File::delete($photoPath);
        }

        $photo->delete();

        return response()->json(['message' => 'La photo a été supprimée avec succès.'], 200);
    }

    /**
 * @OA\Get(
 *      path="/api/logement/getHousingPhoto/{id}",
 *     tags={"Housing Photo"},
 *     summary="Obtenir les photos d'un logement",
 *     description="Cette API permet de récupérer les photos d'un logement spécifique. L'utilisateur doit être propriétaire du logement.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID du logement",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Photos du logement récupérées avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="photos", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="url", type="string", example="https://example.com/photo1.jpg"),
 *                     @OA\Property(property="housing_id", type="integer", example=3)
 *                 )
 *             ),
 *             @OA\Property(property="message", type="string", example="Photo d'un logement")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Accès refusé",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Vous n'avez pas le droit d'afficher les photos d'un logement qui ne vous appartient pas")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Logement non trouvé",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Logement non trouvée")
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

    public function getHousingPhoto($id){
        try {
    
           $housing =  Housing::whereId($id)->first();

        //    return Auth::user()->roles[0]->name;
    
           if(!$housing){
                return (new ServiceController())->apiResponse(404,[],'Logement non trouvée');
           }
    
           if(Auth::user()->id != $housing->user_id){
            return (new ServiceController())->apiResponse(403,[],'Vous n\'avez pas le droit d\'afficher les photos d\'un logement qui ne vous appartiennent pas');
           }
    
           $data = photo::where('housing_id',$housing->id)->get();
         
           return (new ServiceController())->apiResponse(200,$data,'Photo d\un logement');
    
        } catch (\Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

}
