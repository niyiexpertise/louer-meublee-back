<?php

namespace App\Http\Controllers;

use App\Models\Sponsoring;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SponsoringController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/sponsoring/indexAccueil",
     *     summary="Liste des tarifs de sponsoring actifs",
     *     tags={"Hote Housing Sponsoring"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des tarifs de sponsoring.",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur."
     *     )
     * )
     */
    public function indexAccueil()
    {
          try {
            $sponsorings = Sponsoring::where('is_deleted',false)->where('is_actif',true)->get();

            return (new ServiceController())->apiResponse(200, $sponsorings, "Liste des tarifs de sponsoring.");
                } catch(Exception $e) {
                     return (new ServiceController())->apiResponse(500,[],$e->getMessage());
                }
    }

     /**
     * @OA\Get(
     *     path="/api/sponsoring/indexAdmin",
     *     summary="Liste complète des tarifs de sponsoring",
     *     tags={"Tarif Sponsoring"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des tarifs de sponsoring.",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur."
     *     )
     * )
     */

    public function indexAdmin()
    {
        try {
            $sponsorings = Sponsoring::where('is_deleted',false)->get();

            return (new ServiceController())->apiResponse(200, $sponsorings, "Liste des tarifs de sponsoring.");
                } catch(Exception $e) {
                     return (new ServiceController())->apiResponse(500,[],$e->getMessage());
                }
    }

    /**
     * @OA\Get(
     *     path="/api/sponsoring/indexActifAdmin",
     *     summary="Liste complète des tarifs de sponsoring actifs",
     *     tags={"Tarif Sponsoring"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des tarifs de sponsoring actifs.",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur."
     *     )
     * )
     */

    public function indexActifAdmin()
    {
        try {
            $sponsoringActif = Sponsoring::where('is_actif', true)->where('is_deleted', false)->get();
            return (new ServiceController())->apiResponse(200, $sponsoringActif, "Liste des tarifs de sponsoring actifs");

        } catch(Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/sponsoring/indexInactifAdmin",
     *     summary="Liste complète des tarifs de sponsoring inactifs",
     *     tags={"Tarif Sponsoring"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des tarifs de sponsoring inactifs.",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur."
     *     )
     * )
     */

    public function indexInactifAdmin()
    {
        try {
            $sponsoringInactif = Sponsoring::where('is_actif', false)->where('is_deleted', false)->get();
            return (new ServiceController())->apiResponse(200, $sponsoringInactif, "Liste des tarifs de sponsoring inactifs");
        } catch(Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


     /**
     * @OA\Post(
     *     path="/api/sponsoring/store",
     *     summary="Créer un nouveau tarif de sponsoring",
     *     tags={"Tarif Sponsoring"},
     * security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="duree", type="integer", example=12),
     *             @OA\Property(property="prix", type="number", format="float", example=99.99),
     *             @OA\Property(property="description", type="string", example="Description du tarif")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarif de sponsoring créé avec succès."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Erreur de validation des données."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur."
     *     )
     * )
     */
    public function store(Request $request)
    {
          try {
                    $validator = Validator::make($request->all(), [
                       'duree' => 'required',
                        'prix' => 'required',
                        'description' => 'required',
                    ]);

                    $message = [];

                    if ($validator->fails()) {
                        $message[] = $validator->errors();
                        return (new ServiceController())->apiResponse(505,[],$message);
                    }

                    if(!is_int($request->duree)){
                        return (new ServiceController())->apiResponse(404,[],'La valeur de la durée doit être un entier');
                    }
                    if($request->duree <= 0){
                        return (new ServiceController())->apiResponse(404,[],'La valeur de la durée doit être un entier positif supérieur à 0');
                    }
                    if(!is_numeric($request->prix)){
                        return (new ServiceController())->apiResponse(404,[],'La valeur du prix doit être un nombre');
                    }

                    if($request->prix <= 0){
                        return (new ServiceController())->apiResponse(404,[],'La valeur du prix doit être un nombre positif supérieur à 0');
                    }

                    $existDescription = Sponsoring::whereDescription($request->description)->where('is_actif',true)->exists();
                    if($existDescription){
                        return (new ServiceController())->apiResponse(404,[],'Choisissez un autre nom pour le plan car un tarif de sponsoring existe déjà avec lle même non ');
                    }

                    $existDuree = Sponsoring::whereDuree($request->duree)->where('is_actif',true)->exists();
                    if($existDuree){
                        return (new ServiceController())->apiResponse(404,[],'Choisissez une autre duree car un tarif de sponsoring existe déjà avec la même ');
                    }

                    $existPrix = Sponsoring::where('is_actif',true)->wherePrix($request->prix)->exists();

                    if($existPrix){
                        return (new ServiceController())->apiResponse(404,[],'Choisissez un autre prix car un tarif de sponsoring existe déjà avec le prix');
                    }

                    $sponsoring = new Sponsoring();
                    $sponsoring->duree = $request->duree;
                    $sponsoring->prix = $request->prix;
                    $sponsoring->description = $request->description;
                    $sponsoring->save();
                   return (new ServiceController())->apiResponse(200,[],'Tarif de sponsoring créé avec succès');

                } catch(Exception $e) {
                     return (new ServiceController())->apiResponse(500,[],$e->getMessage());
                }
    }

    /**
     * @OA\Post(
     *     path="/api/sponsoring/update/{id}",
     *     summary="Mettre à jour un tarif de sponsoring",
     *     tags={"Tarif Sponsoring"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="duree", type="integer", example=12),
     *             @OA\Property(property="prix", type="number", format="float", example=99.99),
     *             @OA\Property(property="description", type="string", example="Description mise à jour")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarif de sponsoring modifié avec succès."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tarif de sponsoring non trouvé ou erreur de validation des données."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur."
     *     )
     * )
     */
    public function update(Request $request,$id)
    {
        try {

            $sponsoring = Sponsoring::find($id);

            if(!$sponsoring){
                return (new ServiceController())->apiResponse(404,[],'Tarif de sponsoring non trouvé');
            }

            if(!is_int($request->duree)){
                return (new ServiceController())->apiResponse(404,[],'La valeur de la durée doit être un entier');
            }
            if($request->duree <= 0){
                return (new ServiceController())->apiResponse(404,[],'La valeur de la durée doit être un entier positif supérieur à 0');
            }
            if(!is_numeric($request->prix)){
                return (new ServiceController())->apiResponse(404,[],'La valeur du prix doit être un nombre');
            }

            if($request->duree <= 0){
                return (new ServiceController())->apiResponse(404,[],'La valeur du prix doit être un nombre positif supérieur à 0');
            }

            $exist = Sponsoring::whereDuree($request->duree)->wherePrix($request->prix)->exists();

            if($exist){
                return (new ServiceController())->apiResponse(404,[],'Le tarif de sponsoring existe de déjà');
            }

            $sponsoring->duree = $request->duree??$sponsoring->duree;
            $sponsoring->prix = $request->prix??$sponsoring->prix;
            $sponsoring->description = $request->description??$sponsoring->description;
            $sponsoring->save();

            return (new ServiceController())->apiResponse(200, [], "Tarif de sponsoring modifié avec succès");
                } catch(Exception $e) {
                     return (new ServiceController())->apiResponse(500,[],$e->getMessage());
                }
    }


     /**
     * @OA\Get(
     *     path="/api/sponsoring/show/{id}",
     *     summary="Afficher un tarif de sponsoring",
     *     tags={"Tarif Sponsoring"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détail d'un tarif.",
     *         @OA\JsonContent(ref="")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tarif de sponsoring non trouvé."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur."
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $sponsoring = Sponsoring::find($id);

            if(!$sponsoring){
                return (new ServiceController())->apiResponse(404,[],'Tarif de sponsoring non trouvé');
            }

            return (new ServiceController())->apiResponse(200, $sponsoring, "Détail d'un tarif.");
        }catch(Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }


    //  /**
    //  * @OA\Post(
    //  *     path="/api/sponsoring/destroy/{id}",
    //  *     summary="Supprimer un tarif de sponsoring",
    //  *     tags={"Tarif Sponsoring"},
    //  * security={{"bearerAuth": {}}},
    //  *     @OA\Parameter(
    //  *         name="id",
    //  *         in="path",
    //  *         required=true,
    //  *         @OA\Schema(type="integer")
    //  *     ),
    //  *     @OA\Response(
    //  *         response=200,
    //  *         description="Tarif de sponsoring supprimé avec succès."
    //  *     ),
    //  *     @OA\Response(
    //  *         response=404,
    //  *         description="Tarif de sponsoring non trouvé ou déjà supprimé."
    //  *     ),
    //  *     @OA\Response(
    //  *         response=500,
    //  *         description="Erreur interne du serveur."
    //  *     )
    //  * )
    //  */
    // public function destroy($id)
    // {
    //     try {
    //         $sponsoring = Sponsoring::find($id);

    //         if(!$sponsoring){
    //             return (new ServiceController())->apiResponse(404,[],'Tarif de sponsoring non trouvé');
    //         }

    //         if($sponsoring->is_deleted == true){
    //             return (new ServiceController())->apiResponse(404,[],'Tarif de sponsoring déjà supprimé');
    //         }

    //         $sponsoring->is_deleted = true;
    //         $sponsoring->save();

    //         return (new ServiceController())->apiResponse(200, [], "Tarif de sponsoring supprimé avec succès.");
    //     }catch(Exception $e) {
    //         return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    //     }
    // }


   /**
     * @OA\Post(
     *     path="/api/sponsoring/active/{id}",
     *     summary="Activer un tarif de sponsoring",
     *     tags={"Tarif Sponsoring"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarif de sponsoring activé avec succès."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tarif de sponsoring non trouvé ou déjà actif."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur."
     *     )
     * )
     */
    public function active($id)
    {
        try {
            $sponsoring = Sponsoring::find($id);

            if(!$sponsoring){
                return (new ServiceController())->apiResponse(404,[],'Tarif de sponsoring non trouvé');
            }

            if($sponsoring->is_actif == true){
                return (new ServiceController())->apiResponse(404,[],'Tarif de sponsoring déjà actif');
            }

            $sponsoring->is_actif = true;
            $sponsoring->save();

            return (new ServiceController())->apiResponse(200, [], "Tarif de sponsoring activé avec succès.");
        }catch(Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }


      /**
     * @OA\Post(
     *     path="/api/sponsoring/desactive/{id}",
     *     summary="Désactiver un tarif de sponsoring",
     *     tags={"Tarif Sponsoring"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarif de sponsoring désactivé avec succès."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tarif de sponsoring non trouvé ou déjà désactivé."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur."
     *     )
     * )
     */
    public function desactive($id)
    {
        try {
            $sponsoring = Sponsoring::find($id);

            if(!$sponsoring){
                return (new ServiceController())->apiResponse(404,[],'Tarif de sponsoring non trouvé');
            }

            if($sponsoring->is_actif == false){
                return (new ServiceController())->apiResponse(404,[],'Tarif de sponsoring déjà désactivé');
            }

            $sponsoring->is_actif = false;
            $sponsoring->save();

            return (new ServiceController())->apiResponse(200, [], "Tarif de sponsoring désactivé avec succès.");
        }catch(Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }
}