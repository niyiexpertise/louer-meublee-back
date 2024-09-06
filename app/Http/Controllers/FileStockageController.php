<?php

namespace App\Http\Controllers;

use App\Models\FileStockage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FileStockageController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/fileStockage/store",
     *     tags={"Systeme stockage"},
     *     summary="Ajouter un systeme de stockage",
     *     description="Stocke un nouveau systeme de fichier.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "access_key_id", "secret_access_key", "default_region", "bucket", "url"},
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="access_key_id", type="string"),
     *             @OA\Property(property="secret_access_key", type="string"),
     *             @OA\Property(property="default_region", type="string"),
     *             @OA\Property(property="bucket", type="string"),
     *             @OA\Property(property="url", type="string"),
     *             @OA\Property(property="is_actif", type="integer"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Systeme de stockage stocké avec succès."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de systeme de stockage existant."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur."
     *     ),
     *     security={{"bearerAuth": {}}},
     * )
     */
    public function store(Request $request){
        try {

            $validator = Validator::make($request->all(), [
                'type' =>'required',
                'access_key_id' => 'required',
                'secret_access_key' => 'required',
                'default_region' => 'required',
                'bucket' =>'required',
                'url' => 'required',
                'is_actif' => "required|boolean"
            ]);

            $message = [];

            if ($validator->fails()) {
                $message[] = $validator->errors();
                return (new ServiceController())->apiResponse(505,[],$message);
            }
            if($request->is_actif !=1 && $request->is_actif !=0){
                return (new ServiceController())->apiResponse(404, [], "is_actif doit être un booleen");
            }

            $existType = FileStockage::whereType($request->type)->exists();
            if($existType){
                return (new ServiceController())->apiResponse(404, [], 'Le Type de systeme de stockage existe déjà');
            }

           

            $inputType = strtolower($request->type);

            $similarityThreshold = 80;
        
            $referenceType = "s3";
        
            $similarity = 0;
            similar_text($inputType, $referenceType, $similarity);
        
            if ($similarity > $similarityThreshold) {
                $normalizedType = $referenceType;
            } else {
                $normalizedType = $request->type;
            }

            $fileStockage = new FileStockage();

            $fileStockage->type = $normalizedType;
            $fileStockage->access_key_id = $request->input('access_key_id');
            $fileStockage->secret_access_key = $request->input('secret_access_key');
            $fileStockage->default_region = $request->input('default_region');
            $fileStockage->bucket = $request->input('bucket');
            $fileStockage->url = $request->input('url');
            if($request->is_actif ==1){

                $existType = FileStockage::where('is_deleted',false)->where('is_actif',true)->first();
                if($existType){
                    $existType->is_actif = false;
                    $existType->save();
                }

                $fileStockage->is_actif = true;
                $fileStockage->url = $request->url;
                $fileStockage->save();

                return (new ServiceController())->apiResponse(200, [], "Systeme de stockage stocké et activé avec succès. Nous vous rappelons que l'activation d'un systeme de stockage entraîne la désactivation de celle qui était active");
            }
            $fileStockage->save();

            return (new ServiceController())->apiResponse(200, [], 'Systeme de stockage stocké avec succès.');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

    /**
 * @OA\Post(
 *     path="/api/fileStockage/update/{id}",
 *     tags={"Systeme stockage"},
 *     summary="Modifier un systeme de stockage",
 *     description="Modifie un systeme de stockage existant.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\JsonContent(
 *             @OA\Property(property="type", type="string"),
 *             @OA\Property(property="access_key_id", type="string"),
 *             @OA\Property(property="secret_access_key", type="string"),
 *             @OA\Property(property="default_region", type="string"),
 *             @OA\Property(property="bucket", type="string"),
 *             @OA\Property(property="url", type="string"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Systeme de stockage de fichier modifié avec succès."
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Systeme de stockage inexistant."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur."
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */

    public function update(Request $request, $id){
        try {
            $fileStockage =  FileStockage::whereId($id)->first();

            if(!$fileStockage){
                return (new ServiceController())->apiResponse(404, [], 'Systeme de stockage inexistant');
            }

            $existType = FileStockage::whereType($request->type)->exists();
            if($existType){
                return (new ServiceController())->apiResponse(404, [], 'Type de systeme de stockage existant');
            }

            $fileStockage->type = $request->input('type')??$fileStockage->type;
            $fileStockage->access_key_id = $request->input('access_key_id')??$fileStockage->access_key_id;
            $fileStockage->secret_access_key = $request->input('secret_access_key')??$fileStockage->secret_access_key;
            $fileStockage->default_region = $request->input('default_region')??$fileStockage->default_region;
            $fileStockage->bucket = $request->input('bucket')??$fileStockage->bucket;
            $fileStockage->url = $request->input('url')??$fileStockage->url;
            $fileStockage->save();
            return (new ServiceController())->apiResponse(200, [], 'Systeme de stockage de fichier modifié avec succès');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

    /**
 * @OA\Get(
 *     path="/api/fileStockage/show/{id}",
 *     tags={"Systeme stockage"},
 *     summary="Afficher un systeme de stockage",
 *     description="Affiche le détail d'un systeme de stockage existant.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Détail du systeme de stockage."
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Systeme de stockage inexistant."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur."
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */

    public function show($id){
        try {

            $fileStockage =  FileStockage::whereId($id)->where('is_deleted',false)->first();

            if(!$fileStockage){
                return (new ServiceController())->apiResponse(404, [], 'Systeme de stockage inexistant');
            }

            return (new ServiceController())->apiResponse(200, ['filestockage'=> $fileStockage], "Détail du systeme de stockage");
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }



    /**
 * @OA\Get(
 *     path="/api/fileStockage/showActif",
 *     tags={"Systeme stockage"},
 *     summary="Obtenir le détail du systeme de stockage actif",
 *     description="Retourne le détail du systeme de stockage actif si disponible",
 *     operationId="showActifFileStockage",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Détail du systeme de stockage actif",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="filestockage", type="object",
 *                 @OA\Property(property="type", type="string"),
 *                 @OA\Property(property="access_key_id", type="string"),
 *                 @OA\Property(property="secret_access_key", type="string"),
 *                 @OA\Property(property="default_region", type="string"),
 *                 @OA\Property(property="bucket", type="string"),
 *                 @OA\Property(property="url", type="string"),
 *                 @OA\Property(property="is_actif", type="boolean"),
 *                 @OA\Property(property="is_deleted", type="boolean")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun Systeme de stockage actif",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Aucun Systeme de stockage actif")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
 *         )
 *     )
 * )
 */
    public function showActif(){
        try {

            $fileStockage =  FileStockage::where('is_deleted',false)->where('is_actif',true)->first();

            if(!$fileStockage){
                return (new ServiceController())->apiResponse(404, [], 'Aucun Systeme de stockage actif');
            }

            return (new ServiceController())->apiResponse(200, ['filestockage'=> $fileStockage], "Détail du systeme de stockage actif");
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/fileStockage/indexInactif",
     *     tags={"Systeme stockage"},
     *     summary="Liste des systemes de stockage inactifs",
     *     description="Récupère la liste des systemes de stockage inactifs.",
     *     @OA\Response(
     *         response=200,
     *         description="Liste des systemes de stockage inactifs."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur."
     *     ),
     *     security={{"bearerAuth": {}}},
     * )
     */


    public function indexInactif(){
        try {

            $fileStockages = FileStockage::where('is_deleted',false)->where('is_actif',false)->get();

            return (new ServiceController())->apiResponse(200, [$fileStockages], "Liste des systemes de stockage inactif");

        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/fileStockage/active/{id}",
     *     tags={"Systeme stockage"},
     *     summary="Activer un systeme de stockage",
     *     description="Active un systeme de stockage et désactive les autres.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Systeme de fichier activé avec succès."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Systeme de stockage inexistant."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur."
     *     ),
     *     security={{"bearerAuth": {}}},
     * )
     */


    public function active($id){
        try {

            $fileStockage =  FileStockage::whereId($id)->first();

            if(!$fileStockage){
                return (new ServiceController())->apiResponse(404, [], 'Systeme de stockage inexistant');
            }

            if( $fileStockage->is_actif == true){
                return (new ServiceController())->apiResponse(404, [], 'Systeme de stockage déjà actif');
            }

            $existType = FileStockage::where('is_deleted',false)->where('is_actif',true)->first();
            if($existType){
                $existType->is_actif = false;
                $existType->save();
            }

            $fileStockage->is_actif = true;
            $fileStockage->save();

            return (new ServiceController())->apiResponse(200, [], "Activation du systeme de fichier fait avec succès. Nous vous rappelons que l'activation d'un systeme de stockage entraîne la désactivation de celle qui était active");
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

 /**
 * @OA\Post(
 *     path="/api/fileStockage/delete/{id}",
 *     tags={"Systeme stockage"},
 *     summary="Supprimer un systeme de stockage",
 *     description="Supprime un systeme de stockage en le marquant comme supprimé.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Systeme de stockage supprimé avec succès."
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Systeme de stockage inexistant."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur."
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */

    public function delete($id){
        try {

            $fileStockage =  FileStockage::whereId($id)->first();

            if(!$fileStockage){
                return (new ServiceController())->apiResponse(404, [], 'Systeme de stockage inexistant');
            }

            if( $fileStockage->is_actif == true){
                return (new ServiceController())->apiResponse(404, [], 'Impossible de supprimer le Systeme de stockage actif');
            }

            $fileStockage->is_deleted= true;
            $fileStockage->save();

            return (new ServiceController())->apiResponse(200, [], "Systeme de stockage supprimé avec succès.");
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }
}
