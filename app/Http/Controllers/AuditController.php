<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{

    /**
 * @OA\Get(
 *     path="/api/audit/getAudits",
 *     summary="Récupère tous les audits",
 *     description="Récupère l'ensemble des enregistrements de la table `audits`.",
 *     operationId="getAudits",
 *     tags={"Audits"},
 *  security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des audits récupérée avec succès",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucune donnée disponible"
 *     )
 * )
 */
public function getAudits()
{
    $auditsGrouped = DB::table('audits')
        ->select('auditable_type', DB::raw('count(*) as total'))
        ->groupBy('auditable_type')
        ->get();

    if ($auditsGrouped->isEmpty()) {
        return (new ServiceController())->apiResponse(404, [], 'Aucune donnée disponible');
    }

    $detailedAudits = [];
    $totalCounts = [];

    foreach ($auditsGrouped as $group) {
        $typeName = str_replace('App\\Models\\', '', $group->auditable_type);
        
        $detailedAudits[$typeName] = DB::table('audits')
            ->where('auditable_type', $group->auditable_type)
            ->paginate(30);
        
        $totalCounts[$typeName] = $group->total;
    }

    $response = [
        'totals' => $totalCounts,
        'details' => $detailedAudits
    ];

    return (new ServiceController())->apiResponse(200, $response, 'Données récupérées avec succès');
}



   /**
 * @OA\Get(
 *     path="/api/audit/getAuditsByModelType/{modelType}",
 *     summary="Récupère les audits par type de modèle",
 *     description="Récupère les enregistrements de la table `audits` en fonction du `auditable_type`.",
 *     operationId="getAuditsByModelType",
 *     tags={"Audits"},
 *  security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="modelType",
 *         in="path",
 *         required=true,
 *         description="Type de modèle (par exemple : 'App\\Models\\Review')",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des audits récupérée avec succès",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="user_type", type="string", nullable=true),
 *                 @OA\Property(property="user_id", type="integer", nullable=true),
 *                 @OA\Property(property="event", type="string"),
 *                 @OA\Property(property="auditable_type", type="string"),
 *                 @OA\Property(property="auditable_id", type="integer"),
 *                 @OA\Property(property="old_values", type="string"),
 *                 @OA\Property(property="new_values", type="string"),
 *                 @OA\Property(property="url", type="string"),
 *                 @OA\Property(property="ip_address", type="string"),
 *                 @OA\Property(property="user_agent", type="string"),
 *                 @OA\Property(property="tags", type="string", nullable=true),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucune donnée disponible",
 *     )
 * )
 */
public function getAuditsByModelType($modelType)
{
    $models = $this->getAllModels();

    if (!in_array($modelType, $models)) {
        return (new ServiceController())->apiResponse(404, [], "Le modèle $modelType spécifié n'existe pas.");
    }

    $audits = DB::table('audits')
                ->where('auditable_type', 'App\\Models\\' . $modelType)
                ->get();

    if (count($audits) == 0) {
        return (new ServiceController())->apiResponse(404, $audits, 'Aucune donnée disponible');
    }

    foreach ($audits as $audit) {
        $audit->auditable_type = str_replace('App\\Models\\', '', $audit->auditable_type);
    }

    return (new ServiceController())->apiResponse(200, $audits, 'Liste des audits groupée par type de modèle');
}


 /**
 * @OA\Get(
 *     path="/api/audit/getAuditsByModelTypeAndId/{modelType}/{modelId}",
 *     summary="Récupère les audits par type de modèle et ID",
 *     description="Récupère les enregistrements de la table `audits` en fonction du `auditable_type` et de l'`auditable_id`.",
 *     operationId="getAuditsByModelTypeAndId",
 *     tags={"Audits"},
 *  security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="modelType",
 *         in="path",
 *         required=true,
 *         description="Type de modèle (par exemple : 'App\\Models\\Review')",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="modelId",
 *         in="path",
 *         required=true,
 *         description="ID du modèle",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des audits récupérée avec succès",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="user_type", type="string", nullable=true),
 *                 @OA\Property(property="user_id", type="integer", nullable=true),
 *                 @OA\Property(property="event", type="string"),
 *                 @OA\Property(property="auditable_type", type="string"),
 *                 @OA\Property(property="auditable_id", type="integer"),
 *                 @OA\Property(property="old_values", type="string"),
 *                 @OA\Property(property="new_values", type="string"),
 *                 @OA\Property(property="url", type="string"),
 *                 @OA\Property(property="ip_address", type="string"),
 *                 @OA\Property(property="user_agent", type="string"),
 *                 @OA\Property(property="tags", type="string", nullable=true),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucune donnée disponible"
 *     )
 * )
 */
public function getAuditsByModelTypeAndId($modelType, $modelId)
{

    $models = $this->getAllModels();

    if (!in_array($modelType, $models)) {
        return (new ServiceController())->apiResponse(404, [], "Le modèle $modelType spécifié n'existe pas.");
    }

    $modelMappings = [];
    foreach ($models as $model) {
        $modelName = class_basename($model);
        $modelMappings[$modelName] = "App\Models\\$model";
    }

    if ($modelType) {
        $modelClass = $modelMappings[$modelType];
        if (!(new $modelClass())::find($modelId)) {
            return (new ServiceController())->apiResponse(404, [], "$modelType non trouvé pour l'id $modelId");
        }
    }


    $audits = DB::table('audits')
                ->where('auditable_type', 'App\\Models\\'.$modelType)
                ->where('auditable_id', $modelId)
                ->get();

    if (count($audits) == 0) {
        return (new ServiceController())->apiResponse(404, $audits, 'Aucune donnée disponible');
    }

    foreach ($audits as $audit) {
        $audit->auditable_type = str_replace('App\\Models\\', '', $audit->auditable_type);
    }

    return (new ServiceController())->apiResponse(200, $audits, 'Liste des audits groupés par type de model et l\'id du model ');
}






public function getAllModels()
{
    $models = [];
    $modelPath = app_path('Models');

    if (!File::exists($modelPath)) {
        return $models; // Retourne un tableau vide si le répertoire n'existe pas
    }

    $files = File::allFiles($modelPath);

    foreach ($files as $file) {
        $namespace = App::getNamespace();
        $path = $file->getRelativePathname();
        $class = sprintf(
            '\%sModels\%s',
            $namespace,
            strtr(substr($path, 0, strrpos($path, '.')), '/', '\\')
        );

        if (is_subclass_of($class, Model::class) && !(new \ReflectionClass($class))->isAbstract()) {
            // Enlève le namespace et les doubles backslashes
            $models[] = ltrim(str_replace($namespace . 'Models\\', '', $class), '\\');
        }
    }

    return $models;
}




}
