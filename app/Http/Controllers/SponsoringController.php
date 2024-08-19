<?php

namespace App\Http\Controllers;

use App\Models\Sponsoring;
use Exception;
use Illuminate\Http\Request;

class SponsoringController extends Controller
{
    /**
     * Display a listing of the resource.
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

    public function indexAdmin()
    {
        try {
            $sponsorings = Sponsoring::get();

            return (new ServiceController())->apiResponse(200, $sponsorings, "Liste des tarifs de sponsoring.");
                } catch(Exception $e) {
                     return (new ServiceController())->apiResponse(500,[],$e->getMessage());
                }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
          try {

                    $request->validate([
                        'duree' => 'required',
                        'prix' => 'required',
                        'description' => 'required',
                    ]);

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

            $sponsoring->duree = $request->duree??$sponsoring->duree;
            $sponsoring->prix = $request->prix??$sponsoring->prix;
            $sponsoring->description = $request->description??$sponsoring->description;
            $sponsoring->save();

            return (new ServiceController())->apiResponse(200, [], "Tarif de sponsoring modifié avec succès");
                } catch(Exception $e) {
                     return (new ServiceController())->apiResponse(500,[],$e->getMessage());
                }
    }



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

    public function destroy($id)
    {
        try {
            $sponsoring = Sponsoring::find($id);

            if(!$sponsoring){
                return (new ServiceController())->apiResponse(404,[],'Tarif de sponsoring non trouvé');
            }

            if($sponsoring->is_deleted == true){
                return (new ServiceController())->apiResponse(404,[],'Tarif de sponsoring déjà supprimé');
            }

            $sponsoring->is_deleted = true;
            $sponsoring->save();

            return (new ServiceController())->apiResponse(200, [], "Tarif de sponsoring supprimé avec succès.");
        }catch(Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }

    public function active($id)
    {
        try {
            $sponsoring = Sponsoring::find($id);

            if(!$sponsoring){
                return (new ServiceController())->apiResponse(404,[],'Tarif de sponsoring non trouvé');
            }

            if($sponsoring->is_actif == true){
                return (new ServiceController())->apiResponse(404,[],'Tarif de sponsoring déjà supprimé');
            }

            $sponsoring->is_actif = true;
            $sponsoring->save();

            return (new ServiceController())->apiResponse(200, [], "Tarif de sponsoring activé avec succès.");
        }catch(Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }

    public function desactive($id)
    {
        try {
            $sponsoring = Sponsoring::find($id);

            if(!$sponsoring){
                return (new ServiceController())->apiResponse(404,[],'Tarif de sponsoring non trouvé');
            }

            if($sponsoring->is_actif == false){
                return (new ServiceController())->apiResponse(404,[],'Tarif de sponsoring déjà supprimé');
            }

            $sponsoring->is_actif = false;
            $sponsoring->save();

            return (new ServiceController())->apiResponse(200, [], "Tarif de sponsoring désactivé avec succès.");
        }catch(Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    } 
}
