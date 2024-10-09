<?php

namespace App\Http\Controllers;

use App\Models\MethodPayement;
use App\Models\ServicePaiement;
use Illuminate\Http\Request;
use PHPUnit\Framework\MockObject\Rule\MethodName;

class ServicePaiementController extends Controller
{

    /**
 * @OA\Post(
 *     path="/api/servicepaiement/store",
 *     tags={"Service paiement"},
 *     summary="Créer un nouveau service de paiement",
 *     description="Cette fonction permet de créer un nouveau service de paiement.",
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"type", "method_payement_id", "public_key", "private_key", "secret_key"},
 *             @OA\Property(property="type", type="string"),
 *             @OA\Property(property="method_payement_id", type="integer"),
 *             @OA\Property(property="public_key", type="string"),
 *             @OA\Property(property="private_key", type="string"),
 *             @OA\Property(property="secret_key", type="string"),
 *             @OA\Property(property="description_type", type="string"),
 *             @OA\Property(property="description_service", type="string"),
 *             @OA\Property(property="fees", type="number"),
 *             @OA\Property(property="is_actif", type="integer"),
 *             @OA\Property(property="is_sandbox", type="integer")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Service de paiement créé avec succès",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent()
 *     )
 * )
 */

    public function store(Request $request)
{
    try {

        $request->validate([
            'type' => 'required|string',
            'method_payement_id' => 'required|integer',
            'public_key' => 'required|string',
            'private_key' => 'required|string',
            'secret_key' => 'required|string',
            'description_type' => 'nullable|string',
            'description_service' => 'nullable|string',
            'fees' => 'nullable|numeric',
            'is_actif' => 'nullable|boolean',
            'is_sanbox' => 'nullable|boolean'
        ]);
    
        if(!MethodPayement::whereId($request->method_payement_id)->first()){
            return (new ServiceController())->apiResponse(404,[],'Méthode de paiement non trouvé.');
        }

        if($request->is_actif != 1 && $request->is_actif != 0){
            return (new ServiceController())->apiResponse(404, [], 'is_actif doit être un 1 ou 0');
        }

        if($request->is_sanbox != 1 && $request->is_sanbox != 0){
            return (new ServiceController())->apiResponse(404, [], 'is_sanbox doit être un 1 ou 0');
        }

        $exist = ServicePaiement::whereType($request->type)->where('method_payement_id',$request->method_payement_id)->where('is_deleted',false)->exists();

        if ($exist) {
            return (new ServiceController())->apiResponse(404,[],'Un service de payement existe déjà avec la même méthode de paiement et le même type.');
        }
    
        $is_actif = false;
        $is_sandbox = false;


        if ($request->is_sandbox == 1) {
            $is_sandbox = true;
        }

        if ($request->is_actif == 1) {
            $is_actif = true;
            ServicePaiement::where('method_payement_id', $request->method_payement_id)
                ->update(['is_actif' => false]);
        }
    
        if ($request->is_actif == 1) {
            $is_actif = true;
            ServicePaiement::where('method_payement_id', $request->method_payement_id)
                ->update(['is_actif' => false]);
        }
    
        $service = new ServicePaiement();
        $service->type = strtolower($request->type);
        $service->method_payement_id = $request->method_payement_id;
        $service->public_key = $request->public_key;
        $service->private_key = $request->private_key;
        $service->secret_key = $request->secret_key;
        $service->description_type = $request->description_type??null;
        $service->description_service = $request->description_service??null;
        $service->fees = $request->fees??null;
        $service->is_actif = $is_actif;
        $service->is_sandbox = $is_sandbox;
        $service->date_activation = $request->is_actif ? now() : null;
    
        $service->save();
    
        if ($request->is_actif == 1) {
            return (new ServiceController())->apiResponse(200,$service,"Service de paiement créé avec succès mais nous vous rappelons que comme vous avez décidé qu'elle serait actif, le service précédemment actif lié à cette méthode de paiement a été désactivé.");
        }
    
        return (new ServiceController())->apiResponse(200,$service,'Service de paiement créé avec succès.');

    } catch(\Exception $e) {
    return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
    

}

/**
 * @OA\Post(
 *     path="/api/servicepaiement/update/{id}",
 *     tags={"Service paiement"},
 *     summary="Modifier un service de paiement",
 *     description="Cette fonction permet de modifier un service de paiement existant.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="type", type="string"),
 *             @OA\Property(property="public_key", type="string"),
 *             @OA\Property(property="private_key", type="string"),
 *             @OA\Property(property="secret_key", type="string"),
 *             @OA\Property(property="description_type", type="string"),
 *             @OA\Property(property="description_service", type="string"),
 *             @OA\Property(property="fees", type="number")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Service de paiement modifié avec succès",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Service de paiement non trouvé",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent()
 *     )
 * )
 */


public function update(Request $request, $id)
{
    try {

        $service = ServicePaiement::whereId($id)->first();

        if (!$service) {
            return (new ServiceController())->apiResponse(404,[],'Service de paiement non trouvé.');
        }
    
        $request->validate([
            'type' => 'nullable|string',
            'public_key' => 'nullable|string',
            'private_key' => 'nullable|string',
            'secret_key' => 'nullable|string',
            'description_type' => 'nullable|string',
            'description_service' => 'nullable|string',
            'fees' => 'nullable|numeric'
        ]);
    
        $service->type = $request->type??$service->type;
        $service->public_key = $request->public_key??$service->public_key;
        $service->private_key = $request->private_key??$service->private_key;
        $service->secret_key = $request->secret_key??$service->secret_key;
        $service->description_type = $request->description_type??$service->description_type;
        $service->description_service = $request->description_service??$service->description_service;
        $service->fees = $request->fees??$service->fees;
    
        $service->save();
    
        return (new ServiceController())->apiResponse(200,$service,'Service de paiement modifié avec succès.');

    } catch(\Exception $e) {
    return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
   
}

/**
 * @OA\Get(
 *     path="/api/servicepaiement/getServicesByMethodPaiement/{method_payement_id}",
 *     tags={"Service paiement"},
 *     summary="Liste des services de paiement par méthode de paiement",
 *     description="Cette fonction renvoie la liste des services de paiement pour une méthode de paiement spécifique.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="method_payement_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des services de paiement par méthode",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent()
 *     )
 * )
 */

public function getServicesByMethodPaiement($method_payement_id,$l=0)
{
    try {

        $services = ServicePaiement::where('method_payement_id', $method_payement_id)->where('is_deleted',false)->get();
        $data = [] ;

        foreach($services as $service){
            $data[] = [
                'id' => $service->id,
                'method_payement_id' => $service->method_payement_id,
                'type' => $service->type,
                'public_key' => $service->public_key,
                'description_service' => $service->description_service,
                'description_type' => $service->description_type,
                'fees' => $service->fees,
                'id' => $service->id,
                'is_actif' => $service->is_actif,
                'is_sandbox' => $service->is_sandbox,
                'is_deleted' => $service->is_deleted,
                'private_key' =>$l==1?  $service->private_key:null,
                'secret_key' => $l==1? $service->secret_key:null,
                'created_at' => $service->created_at,
            ];
        }

        return (new ServiceController())->apiResponse(200,$data,'Liste des services de paiement par méthode de paiement.');

    } catch(\Exception $e) {
    return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
   
}

/**
 * @OA\Get(
 *     path="/api/servicepaiement/getActiveServices",
 *     tags={"Service paiement"},
 *     summary="Liste des services de paiement actifs",
 *     description="Cette fonction renvoie la liste des services de paiement actifs.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des services de paiement actifs",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent()
 *     )
 * )
 */

public function getActiveServices()
{
    try {

        $services = ServicePaiement::where('is_actif', true)->where('is_deleted',false)->get();

        $data = [] ;

        foreach($services as $service){
            $data[] = [
                'id' => $service->id,
                'method_payement_id' => $service->method_payement_id,
                'type' => $service->type,
                'public_key' => $service->public_key,
                'description_service' => $service->description_service,
                'description_type' => $service->description_type,
                'fees' => $service->fees,
                'id' => $service->id,
                'is_actif' => $service->is_actif,
                'is_sandbox' => $service->is_sandbox,
                'is_deleted' => $service->is_deleted
            ];
        }

        return (new ServiceController())->apiResponse(200,$data,'Liste des services de paiement actifs.');

    } catch(\Exception $e) {
    return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
  
}

/**
 * @OA\Get(
 *     path="/api/servicepaiement/getInactiveServices",
 *     tags={"Service paiement"},
 *     summary="Liste des services de paiement inactifs",
 *     description="Cette fonction renvoie la liste des services de paiement inactifs.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des services de paiement inactifs",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent()
 *     )
 * )
 */

public function getInactiveServices()
{
    try {

        $services = ServicePaiement::where('is_actif', false)->where('is_deleted',false)->get();
        $data = [] ;

        foreach($services as $service){
            $data[] = [
                'id' => $service->id,
                'method_payement_id' => $service->method_payement_id,
                'type' => $service->type,
                'public_key' => $service->public_key,
                'description_service' => $service->description_service,
                'description_type' => $service->description_type,
                'fees' => $service->fees,
                'id' => $service->id,
                'is_actif' => $service->is_actif,
                'is_sandbox' => $service->is_sandbox,
                'is_deleted' => $service->is_deleted
            ];
        }

        return (new ServiceController())->apiResponse(200,$data,'Liste des services de paiement inactifs.');

    } catch(\Exception $e) {
    return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
   
}

/**
 * @OA\Post(
 *     path="/api/servicepaiement/active/{id}",
 *     tags={"Service paiement"},
 *     summary="Activer un service de paiement",
 *     description="Cette fonction permet d'activer un service de paiement et désactive tout autre service actif pour la même méthode de paiement.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Service de paiement activé avec succès",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Service de paiement non trouvé",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent()
 *     )
 * )
 */

public function active($id)
{
    try {

        $service = ServicePaiement::whereId($id)->first();

        if (!$service) {
            return (new ServiceController())->apiResponse(404,[],'Service de paiement non trouvé.');
        }
    
        if ($service->is_actif) {
            return (new ServiceController())->apiResponse(404,[],'Le service de paiement est déjà actif.');
        }
    
        ServicePaiement::where('method_payement_id', $service->method_payement_id)
            ->update(['is_actif' => false]);
    
        $service->is_actif = true;
        $service->date_activation = now();
        $service->save();
    
        $methodeName= MethodPayement::whereId($service->method_payement_id)->first()->name;
    
        return (new ServiceController())->apiResponse(200,$service,"Activation du service de paiement de la méthode de paiement $methodeName fait avec succès. Nous vous rappelons que c'est le seul service actif de cette méthode puisque ce qui l'était a été désactivé après l'activation de celui ci.");

    } catch(\Exception $e) {
    return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
    
}

/**
 * @OA\Post(
 *     path="/api/servicepaiement/desactive/{id}",
 *     tags={"Service paiement"},
 *     summary="Désactiver un service de paiement",
 *     description="Cette fonction permet de désactiver un service de paiement.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Service de paiement désactivé avec succès",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Service de paiement non trouvé",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent()
 *     )
 * )
 */

public function desactive($id)
{
    try {

        $service = ServicePaiement::whereId($id)->first();

        if (!$service) {
            return (new ServiceController())->apiResponse(404,[],'Service de paiement non trouvé.');
        }
    
        if (!$service->is_actif) {
            return (new ServiceController())->apiResponse(404,[],'Le service de paiement est déjà inactif.');
        }
    
        $service->is_actif = false;
        $service->save();
    
        $methodeName= MethodPayement::whereId($service->method_payement_id)->first()->name;
    
        $ServiceCount = ServicePaiement::where('method_payement_id',$service->method_payement_id)->where('is_actif',true)->count();
    
        $message = '';
    
        if($ServiceCount == 0){
            $message = "Tout les services de paiement de la méthode de paiement $methodeName sont désactivés. Aucun n'est actif";
        }
    
        return (new ServiceController())->apiResponse(200,$service,"Désactivation du service de paiement de la méthode de paiement $methodeName fait avec succès.$message");

    } catch(\Exception $e) {
    return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
   
}

/**
 * @OA\Get(
 *     path="/api/servicepaiement/showServiceActifByMethodPaiement/{method_payement_id}",
 *     tags={"Service paiement"},
 *     summary="Afficher le service actif pour une méthode de paiement",
 *     description="Cette fonction affiche le service de paiement actif pour une méthode de paiement donnée.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="method_payement_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Service de paiement actif renvoyé avec succès",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Méthode de paiement non trouvée",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent()
 *     )
 * )
 */

public function showServiceActifByMethodPaiement($methodPaiementId,$data=false){
    try {

        $method = MethodPayement::whereId($methodPaiementId)->first();
        $donnee = [];

        if(!$method){
            return (new ServiceController())->apiResponse(404,[],'Méthode de paiement non trouvé.');
        }

        if(!$method->is_actif){
            return (new ServiceController())->apiResponse(404,[],'Méthode de paiement non actif.');
        }

        $service = ServicePaiement::where('method_payement_id',$methodPaiementId)->where('is_deleted',false)->where('is_actif',true)->first();

        if($data==true){
            return $service;
        }

        $donnee[] = [
            'id' => $service->id,
            'method_payement_id' => $service->method_payement_id,
            'type' => $service->type,
            'public_key' => $service->public_key,
            'description_service' => $service->description_service,
            'description_type' => $service->description_type,
            'fees' => $service->fees,
            'id' => $service->id,
            'is_actif' => $service->is_actif,
            'is_sandbox' => $service->is_sandbox,
            'is_deleted' => $service->is_deleted
        ];
        return (new ServiceController())->apiResponse(200,$donnee,'Service actif d une méthode de paiement.');
    } catch(\Exception $e) {
    return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
}

/**
 * @OA\Get(
 *     path="/api/servicepaiement/show/{id}",
 *     tags={"Service paiement"},
 *     summary="Afficher un service de paiement",
 *     description="Retourne le détail d'un service de paiement spécifique.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID du service de paiement",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Détail du service de paiement récupéré avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Service de paiement non trouvé"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur"
 *     )
 * )
 */

public function show($id){
    try {

        $service = ServicePaiement::whereId($id)->first();
        $donnee = [];

        if (!$service) {
            return (new ServiceController())->apiResponse(404,[],'Service de paiement non trouvé.');
        }

        $donnee[] = [
            'id' => $service->id,
            'method_payement_id' => $service->method_payement_id,
            'type' => $service->type,
            'public_key' => $service->public_key,
            'description_service' => $service->description_service,
            'description_type' => $service->description_type,
            'fees' => $service->fees,
            'id' => $service->id,
            'is_actif' => $service->is_actif,
            'is_sandbox' => $service->is_sandbox,
            'is_deleted' => $service->is_deleted
        ];
        return (new ServiceController())->apiResponse(200,$donnee,"Détail d'un service de paiement.");

    } catch(\Exception $e) {
    return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
}

/**
 * @OA\Post(
 *     path="/api/servicepaiement/destroy/{id}",
 *     tags={"Service paiement"},
 *     summary="Supprimer un service de paiement",
 *     description="Supprime un service de paiement spécifié.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID du service de paiement à supprimer",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Service de paiement supprimé avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Service de paiement non trouvé"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur"
 *     )
 * )
 */

public function destroy($id){
    try {

        $service = ServicePaiement::whereId($id)->first();

        if (!$service) {
            return (new ServiceController())->apiResponse(404,[],'Service de paiement non trouvé.');
        }

        $service->is_deleted = true;
        $service->save();


        return (new ServiceController())->apiResponse(200,$service,"Suppression effectué avec succès.");

    } catch(\Exception $e) {
    return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
}

/**
 * @OA\Post(
 *     path="/api/servicepaiement/activeSandbox/{id}",
 *     summary="Active le mode sandbox pour un service de paiement",
 *     tags={"Service paiement"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID du service de paiement",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Mode sandbox activé avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object", ref=""),
 *             @OA\Property(property="message", type="string", example="Activation du mode sandbox pour le service de paiement fait avec succès.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Service de paiement non trouvé ou déjà en mode sandbox",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string")
 *         )
 *     )
 * )
 */
public function activeSandbox($id)
{
    try {

        $service = ServicePaiement::whereId($id)->first();

        if (!$service) {
            return (new ServiceController())->apiResponse(404,[],'Service de paiement non trouvé.');
        }
    
        if ($service->is_sandbox) {
            return (new ServiceController())->apiResponse(404,[],'Le service de paiement est déjà en mode sandbox.');
        }
    
        $service->is_sandbox = true;
        $service->save();
        
        return (new ServiceController())->apiResponse(200,$service,"Activation du mode sandbox pour le service de paiement fait avec succès.");

    } catch(\Exception $e) {
    return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }

}

/**
 * @OA\Post(
 *     path="/api/servicepaiement/desactiveSandbox/{id}",
 *     summary="Désactive le mode sandbox pour un service de paiement",
 *     tags={"Service paiement"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID du service de paiement",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Mode live activé avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object", ref=""),
 *             @OA\Property(property="message", type="string", example="Activation du mode live pour le service de paiement fait avec succès.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Service de paiement non trouvé ou déjà en mode live",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string")
 *         )
 *     )
 * )
 */
public function desactiveSandbox($id)
{
    try {

        $service = ServicePaiement::whereId($id)->first();

        if (!$service) {
            return (new ServiceController())->apiResponse(404,[],'Service de paiement non trouvé.');
        }
    
        if (!$service->is_sandbox) {
            return (new ServiceController())->apiResponse(404,[],'Le service de paiement est déjà en mode live.');
        }
    
        $service->is_sandbox = false;
        $service->save();
        
        return (new ServiceController())->apiResponse(200,$service,"Activation du mode live pour le service de paiement fait avec succès.");

    } catch(\Exception $e) {
    return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
    
}

/**
 * @OA\Get(
 *     path="/api/servicepaiement/getSandboxServices",
 *     summary="Récupère la liste des services de paiement en mode sandbox",
 *     tags={"Service paiement"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des services de paiement qui sont en mode sandbox",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="")),
 *             @OA\Property(property="message", type="string", example="Liste des services de paiement qui sont en mode sandbox.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string")
 *         )
 *     )
 * )
 */
public function getSandboxServices()
{
    try {

        $services = ServicePaiement::where('is_sandbox', true)->where('is_deleted',false)->get();

        return (new ServiceController())->apiResponse(200,$services,'Liste des services de paiement qui sont en mode sandbox.');

    } catch(\Exception $e) {
    return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
   
}

/**
 * @OA\Get(
 *     path="/api/servicepaiement/getNotSandboxServices",
 *     summary="Récupère la liste des services de paiement en mode live",
 *     tags={"Service paiement"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des services de paiement qui sont en mode live",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="")),
 *             @OA\Property(property="message", type="string", example="Liste des services de paiement qui sont en mode live.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string")
 *         )
 *     )
 * )
 */
public function getNotSandboxServices()
{
    try {

        $services = ServicePaiement::where('is_sandbox', false)->where('is_deleted',false)->get();

        return (new ServiceController())->apiResponse(200,$services,'Liste des services de paiement qui sont en mode live.');

    } catch(\Exception $e) {
    return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
   
}

    /**
 * @OA\Get(
 *     path="/api/servicepaiement/getServicesGroupedByMethodPaiement",
 *     summary="Récupérer les services groupés par méthode de paiement",
 *     description="Retourne les services de paiement actifs et non supprimés groupés par méthode de paiement.",
 *     tags={"Service paiement"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des services de paiement groupés par méthode de paiement",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1, description="ID de la méthode de paiement"),
 *                 @OA\Property(property="nom", type="string", example="Carte de Crédit", description="Nom de la méthode de paiement"),
 *                 @OA\Property(property="service_paiement", type="array", @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1, description="ID du service de paiement"),
 *                     @OA\Property(property="type", type="string", example="Visa", description="Type du service de paiement"),
 *                     @OA\Property(property="public_key", type="string", example="public_key_value", description="Clé publique du service"),
 *                     @OA\Property(property="is_actif", type="boolean", example=true, description="État actif ou non du service")
 *                 ))
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Erreur interne du serveur")
 *         )
 *     )
 * )
 */


    public function getServicesGroupedByMethodPaiement(){
        try {

            $methodPayements = MethodPayement::where('is_deleted', false)
                ->where('is_actif', true)
                ->where('is_accepted', true)
                ->Where('is_received',true)
                ->get();

            foreach($methodPayements as $methodPayement){
                $methodPayement->service_paiement = $this->getServicesByMethodPaiement($methodPayement->id,1)->original['data'];
            }

            return (new ServiceController())->apiResponse(200,$methodPayements,"Methodes de paiement et leur services de paiement");
    
        } catch(\Exception $e) {
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }


}

// try {

    

// } catch(\Exception $e) {
// return (new ServiceController())->apiResponse(500,[],$e->getMessage());
// }