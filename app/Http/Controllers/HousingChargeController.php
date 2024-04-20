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
 *                 @OA\Property(property="housingId", type="integer", example="1"),
 *                 @OA\Property(
 *                     property="hote",
 *                     type="object",
 *                     @OA\Property(property="idCharge", type="array", @OA\Items(type="integer")),
 *                     @OA\Property(property="valeurCharge", type="array", @OA\Items(type="integer")),
 *                     description="Informations sur les charges de l'hôte"
 *                 ),
 *                 @OA\Property(
 *                     property="traveler",
 *                     type="object",
 *                     @OA\Property(property="idCharge", type="array", @OA\Items(type="integer")),
 *                     @OA\Property(property="valeurCharge", type="array", @OA\Items(type="integer")),
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
        
                $hoteRepeatedInTraveler = array_intersect($hote['idCharge'], $traveler['idCharge']);
                $travelerRepeatedInHote = array_intersect($traveler['idCharge'], $hote['idCharge']);
        
                if (!empty($hoteRepeatedInTraveler) || !empty($travelerRepeatedInHote)) {
                    return response()->json(['message' => 'Element se répète'], 404);
                } else {
                    foreach ($hote['idCharge'] as $index => $chargeId) {
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
                            $housingCharge->valeur = $hote['valeurCharge'][$index];
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
        
                    foreach ($traveler['idCharge'] as $index => $chargeId) {
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
                            $housingCharge->valeur = $traveler['valeurCharge'][$index];
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
     *     path="/api/logement/charge/listelogementcharge/{housingId}",
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
    public function listelogementcharge($housingId)
{
    try {
        $hoteCharge_id = [];
        $travelerCharge_id = [];
        $totalHoteCharge = 0;
        $totalTravelerCharge = 0;

        $housingCharges = Housing_charge::where('housing_id', $housingId)->get();

        if ($housingCharges->isEmpty()) {
            return response()->json(['message' => 'Aucune charge associée à ce logement'], 404);
        }

        foreach ($housingCharges as $housingCharge) {
            $charge = Charge::find($housingCharge->charge_id);
            $chargeData = [
                'id_housing_charge' => $housingCharge->id,
                'housing_id' => $housingCharge->housing_id,
                'valeur_charge' => $housingCharge->valeur,
                'id_charge' => $charge->id,
                'charge_name' => $charge->name,
                'is_mycharge' => $housingCharge->is_mycharge
            ];

            if ($housingCharge->is_mycharge) {
                $hoteCharge_id[] = $chargeData;
                $totalHoteCharge += $housingCharge->valeur;
            } else {
                $travelerCharge_id[] = $chargeData;
                $totalTravelerCharge += $housingCharge->valeur;
            }
        }

        return response()->json([
            'data' => [
                'charge_hote' => [
                    'charges' => $hoteCharge_id,
                    'total_charge_hote' => $totalHoteCharge
                ],
                'charge_traveler' => [
                    'charges' => $travelerCharge_id,
                    'total_charge_traveler' => $totalTravelerCharge
                ],
                'total_charge' => $totalHoteCharge + $totalTravelerCharge
            ]
        ], 200);
    } catch(Exception $e) {
        return response()->json($e->getMessage(), 500);
    }
}



}