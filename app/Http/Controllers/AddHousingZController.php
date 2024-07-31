<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use App\Models\Housing;
use App\Models\HousingType;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddHousingZController extends Controller
{
/**
 * @OA\Post(
 *     path="/api/logement/store_step_3/{housingId}",
 *     summary="Etape3: Ajouter des coordonnées géographiques à un logement",
 *     tags={"Ajout de logement"},
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         required=true,
 *         description="ID du logement",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"sit_geo_lat", "sit_geo_lng"},
 *                 @OA\Property(
 *                     property="sit_geo_lat",
 *                     description="Latitude géographique",
 *                     type="number",
 *                   
 *                 ),
 *                 @OA\Property(
 *                     property="sit_geo_lng",
 *                     description="Longitude géographique",
 *                     type="number",
 *                    
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Étape 3 terminée avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="housing_id",
 *                 description="ID du logement",
 *                 type="integer"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Logement non trouvé",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 description="Message d'erreur",
 *                 type="string"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 description="Message d'erreur",
 *                 type="string"
 *             )
 *         )
 *     ),
 * *    @OA\Response(
 *         response=505,
 *         description="Donnée invalide",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 description="Message d'erreur",
 *                 type="string"
 *             )
 *         )
 *     ),
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */

 public function addHousing_step_3(Request $request, $housingId){
    try {
        $validator = Validator::make($request->all(), [
            'sit_geo_lat' => 'required|numeric',
            'sit_geo_lng' => 'required|numeric'
        ]);
        $message = [];
        if ($validator->fails()) {
            $message[] = $validator->errors();
            return (new ServiceController())->apiResponse(505,[],$message);
        }
        if ($request->sit_geo_lat < -90 || $request->sit_geo_lat > 90) {
            return (new ServiceController())->apiResponse(404, [], 'La latitude doit être comprise entre -90 et 90.');
        }

        if ($request->sit_geo_lng < -180 || $request->sit_geo_lng > 180) {
            return (new ServiceController())->apiResponse(404, [], 'La longitude doit être comprise entre -180 et 180.');
        }

        $housing = Housing::whereId($housingId)->first();
        if (!$housing) {
            return (new ServiceController())->apiResponse(404, [], 'Logement non trouvé');
        }

        $housing->sit_geo_lat = $request->sit_geo_lat;
        $housing->sit_geo_lng = $request->sit_geo_lng;
        $housing->step = 3;
        $housing->save();

        $data = ["housing_id" => $housing->id];

        return (new ServiceController())->apiResponse(200, $data, 'Étape 3 terminée avec succès');

    } catch(\Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}

/**
 * @OA\Post(
 *     path="/api/logement/store_step_7/{housingId}",
 *     summary="Ajouter les règlements intérieurs à un logement",
 *     tags={"Ajout de logement"},
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         required=true,
 *         description="ID du logement",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="interior_regulation",
 *                     description="Règlement intérieur",
 *                     type="string",
 *                     nullable=true
 *                 ),
 *                 @OA\Property(
 *                     property="interior_regulation_pdf",
 *                     description="Fichier PDF du règlement intérieur",
 *                     type="string",
 *                     format="binary",
 *                     nullable=true
 *                 )
 *             ),
 *             example={
 *                 "interior_regulation": "Sample interior regulation text",
 *                 "interior_regulation_pdf": "file.pdf"
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Étape 7 terminée avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="housing_id",
 *                 description="ID du logement",
 *                 type="integer"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Logement non trouvé ou fichier PDF non valide",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 description="Message d'erreur",
 *                 type="string"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 description="Message d'erreur",
 *                 type="string"
 *             )
 *         )
 *     ),
 *    @OA\Response(
 *         response=505,
 *         description="Donnée invalide",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 description="Message d'erreur",
 *                 type="string"
 *             )
 *         )
 *     ),
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */


public function addHousing_step_7(Request $request, $housingId){
    try {
        $validator = Validator::make($request->all(), [
            'interior_regulation' => 'nullable|string',
            'interior_regulation_pdf' => 'nullable|file'
        ]);

        $message = [];
        if ($validator->fails()) {
            $message[] = $validator->errors();
            return (new ServiceController())->apiResponse(505, [], $message);
        }

        if (empty($request->interior_regulation) && !$request->hasFile('interior_regulation_pdf')) {
            return (new ServiceController())->apiResponse(404, [], 'Au moins un des champs doit être renseigné.');
        }

        $housing = Housing::whereId($housingId)->first();
        if (!$housing) {
            return (new ServiceController())->apiResponse(404, [], 'Logement non trouvé');
        }

        if ($request->hasFile('interior_regulation_pdf')) {
            $pdfFile = $request->file('interior_regulation_pdf');
            $extension = $pdfFile->getClientOriginalExtension();
            if (strtolower($extension) !== 'pdf') {
                return (new ServiceController())->apiResponse(404, [], 'Le fichier doit être au format PDF.');
            }
            $pathName = uniqid() . '.' . $extension;
            $pdfFile->move(public_path('reglement_interieur'), $pathName);
            $pathUrl = url('/reglement_interieur/' . $pathName);
            $housing->interior_regulation_pdf = $pathUrl;
        }

        if (!empty($request->interior_regulation)) {
            $housing->interior_regulation = $request->interior_regulation;
        }

        $housing->step = 7;
        $housing->save();

        $data = ["housing_id" => $housing->id];

        return (new ServiceController())->apiResponse(200, $data, 'Étape 7 terminée avec succès');

    } catch(\Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}




}
