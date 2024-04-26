<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Housing;
use App\Models\Housing_charge;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File as F ;
use Illuminate\Validation\ValidationException ;
use Exception;

class HousingChargeController extends Controller
{
  /**
 * @OA\Post(
 *     path="/api/logement/charge/addChargeToHousing",
 *     summary="Ajouter une charge au logement",
 *     tags={"Housing Charge"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(property="housingId", type="string", example="1"),
 *                 @OA\Property(
 *                     property="hote",
 *                     type="object",
 *                     @OA\Property(property="id", type="array", @OA\Items(type="string")),
 *                     @OA\Property(property="valeur", type="array", @OA\Items(type="string")),
 *                     description="Informations sur les charges de l'hôte"
 *                 ),
 *                 @OA\Property(
 *                     property="traveler",
 *                     type="object",
 *                     @OA\Property(property="id", type="array", @OA\Items(type="string")),
 *                     @OA\Property(property="valeur", type="array", @OA\Items(type="string")),
 *                     description="Informations sur les charges du voyageur"
 *                 ),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Charge ajoutée avec succès au logement"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Credentials invalides"
 *     )
 * )
 */

        public function addChargeToHousing(Request $request){
            try{
                if (!Housing::find($request->housingId)) {
                    return response()->json(['message' => 'Logement non trouvé'], 404);
                }
                
                $e = [];
                $m = [];
                $hoteCharge_id = [];
                $travelerCharge_id = [];
                $hote = $request->input('hote');
                $traveler = $request->input('traveler');
        
                $hoteRepeatedInTraveler = array_intersect($hote['id'], $traveler['id']);
                $travelerRepeatedInHote = array_intersect($traveler['id'], $hote['id']);
        
                if (!empty($hoteRepeatedInTraveler) || !empty($travelerRepeatedInHote)) {
                    return response()->json(['message' => 'Element se répète'], 404);
                } else {
                    foreach ($hote['id'] as $index => $chargeId) {
                        if (!Charge::find($chargeId)) {
                            return response()->json(['message' => 'Une ou plusieurs charges non trouvées'], 404);
                        }
                        $existingAssociation = Housing_charge::where('housing_id', $request->housingId)
                            ->where('charge_id', $chargeId)
                            ->exists();
                        if ($existingAssociation) {
                            $e[] = [
                                Charge::find($chargeId)->name . ' existe déjà dans le logement',
                            ];
                        } else {
                            $m[] = [
                                Charge::find($chargeId)->name . ' a été ajouté avec succès au logement',
                            ];
        
                            $housingCharge = new Housing_charge();
                            $housingCharge->housing_id = $request->housingId;
                            $housingCharge->is_mycharge = true;
                            $housingCharge->charge_id = $chargeId;
                            $housingCharge->valeur = $hote['valeur'][$index];
                            $housingCharge->save();
        
                            $charge = Charge::find($housingCharge->charge_id);
                            $hoteCharge_id[] = [
                                'id_housing_charge' => $housingCharge->id,
                                'housing_id' => $housingCharge->housing_id,
                                'id_charge' => $charge->id,
                                'charge_name' => $charge->name,
                                'is_mycharge' => $housingCharge->is_mycharge,
                                'valeur_chageur' => $housingCharge->valeur,
                            ];
                        }
                    }
        
                    foreach ($traveler['id'] as $index => $chargeId) {
                        if (!Charge::find($chargeId)) {
                            return response()->json(['message' => 'Charge non trouvée'], 404);
                        }
                        $existingAssociation = Housing_charge::where('housing_id', $request->housingId)
                            ->where('charge_id', $chargeId)
                            ->exists();
                        if ($existingAssociation) {
                            $e[] = [
                                Charge::find($chargeId)->name . ' existe déjà dans le logement',
                            ];
                        } else {
                            $m[] = [
                                Charge::find($chargeId)->name . ' a été ajouté avec succès au logement',
                            ];
        
                            $housingCharge = new Housing_charge();
                            $housingCharge->housing_id = $request->housingId;
                            $housingCharge->is_mycharge = false;
                            $housingCharge->charge_id = $chargeId;
                            $housingCharge->valeur = $traveler['valeur'][$index];
                            $housingCharge->save();
        
                            $charge = Charge::find($housingCharge->charge_id);
                            $travelerCharge_id[] = [
                                'id_housing_charge' => $housingCharge->id,
                                'housing_id' => $housingCharge->housing_id,
                                'id_charge' => $charge->id,
                                'charge_name' => $charge->name,
                                'is_mycharge' => $housingCharge->is_mycharge,
                                'valeur_chageur' => $housingCharge->valeur,
                            ];
                        }
                    }
                }
        
                $data = [
                    'hoteCharge_id' => $hoteCharge_id,
                    'travelerCharge_id' => $travelerCharge_id
                ];
        
                return response()->json([
                    'data' => $data,
                    'message' => empty($m) ? 'Erreur' : $m,
                    'error' => empty($e) ? 'Aucune erreur' : $e
                ], 200);
            } catch(Exception $e) {    
                return response()->json($e->getMessage());
            }
        }
        

    
    /**
     * @OA\Get(
     *     path="/api/charge/listelogementcharge/{housingId}",
     *     summary="Get all charges for a specific housing",
     *     tags={"Housing Charge"},
     * security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="housingId",
     *         in="path",
     *         description="L'ID du logement pour lequel récupérer les équipements",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of charges"
     *
     *     )
     * )
     */
    public function listelogementcharge($housingId){
        try {
            $hoteCharge_id = [];
            $travelerCharge_id = [];
            $housingCharges = Housing_charge::where('housing_id', $housingId)->get();
            if ($housingCharges->isEmpty()) {
                return response()->json(['message' => 'Aucune charge associé à ce logement'], 404);
            }
            
            foreach ($housingCharges as $housingCharge) {
                $charge = Charge::find($housingCharge->charge_id);
                // return response()->json($charge);
                if ($housingCharge->is_mycharge == true) {
                    $hoteCharge_id[] = [
                        'id_housing_charge' => $housingCharge->id,
                        'housing_id' => $housingCharge->housing_id,
                        'valeur_chageur' => $housingCharge->valeur,
                        'id_charge' => $charge->id,
                        'charge_name' => $charge->name,
                        'is_mycharge' => $housingCharge->is_mycharge
                    ];
                }else{
                    $travelerCharge_id[] = [
                        'id_housing_charge' => $housingCharge->id,
                        'housing_id' => $housingCharge->housing_id,
                        'valeur_chageur' => $housingCharge->valeur,
                        'id_charge' => $charge->id,
                        'charge_name' => $charge->name,
                        'is_mycharge' => $housingCharge->is_mycharge
                    ];
                }
            }
            return response()->json([
                'data' => [
                    'charge_hote' => $hoteCharge_id,
                    'charge_traveler' => $travelerCharge_id
                ]
            ], 200);
        } catch(Exception $e) {
            return response()->json($e->getMessage());
        }
    }



}
