<?php

namespace App\Http\Controllers;

use App\Models\MethodPayement;
use App\Models\Payement;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isNull;

class ServiceController extends Controller
{
    public function apiResponse($status,$data,$message=''){

        if(Setting::first()->app_mode == 'PRODUCTION' && $status == 500){
            $message = "An error occured";
        }

        return response()->json([
            'status_code' => $status,
            'data' =>$data,
            'message' => $message,
        ], 200);
    }

    public function validatePayement($amount,$paiement_methode,$transaction_id){

        if(!MethodPayement::whereName($paiement_methode)->where('is_deleted', false)->where('is_actif', true)->exists()){
            return $this->apiResponse(404, [], 'Méthode de paiement non trouvé.');
        }

        $existTransaction = Payement::where('id_transaction',$transaction_id)->exists();
        if ($existTransaction) {
            return $this->apiResponse(404, [], 'L\'id de la transaction existe déjà');
        }

        if(!is_numeric($amount)){
            return $this->apiResponse(404, [], 'Le montant doit être un entier');
        }

        if($amount<=0){
            return $this->apiResponse(404, [], 'Le montant doit être supérieur à 0 ');
        }

        if(!is_null(Setting::first()->montant_minimum_recharge)){
            if($amount < Setting::first()->montant_minimum_recharge){
                return $this->apiResponse(404, [], "Le montant minimum à recharger doit être supérieur à  ".Setting::first()->montant_minimum_recharge. " FCFA");
            }
        }

       if(!is_null(Setting::first()->montant_maximum_recharge)){
            if($amount > Setting::first()->montant_maximum_recharge){
                return $this->apiResponse(404, [], "Le montant maximum à recharger doit être inférieur à  ".Setting::first()->montant_maximum_recharge. " FCFA");
            }
       }

    }

}
