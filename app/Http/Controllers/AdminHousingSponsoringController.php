<?php

namespace App\Http\Controllers;

use App\Models\HousingSponsoring;
use Exception;
use Illuminate\Http\Request;

class AdminHousingSponsoringController extends Controller
{
    public function demandeSponsoringNonvalidé(){
        try {
                $sponsoringrequests = HousingSponsoring::where('is_actif',false)->where('is_deleted',false)->get();

            return (new ServiceController())->apiResponse(200, $sponsoringrequests, 'Liste des demandes de sponsoring d\'un hôte connecté');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }
}
