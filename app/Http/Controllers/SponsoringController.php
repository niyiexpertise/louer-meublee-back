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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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

    /**
     * Display the specified resource.
     */
    public function show(Sponsoring $sponsoring)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sponsoring $sponsoring)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sponsoring $sponsoring)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sponsoring $sponsoring)
    {
        //
    }
}
