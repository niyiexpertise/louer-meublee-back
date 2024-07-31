<?php

namespace App\Http\Controllers;

use App\Models\Equipment_category;
use App\Models\Housing;
use App\Models\HousingType;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddHousingController extends Controller
{

    /**
 * @OA\Post(
 *     path="/api/logement/store_step_1",
 *     summary="Ajouter une étape de logement, enregistrement de type de propriété",
 *     tags={"Ajout de logement"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"property_type_id"},
 *                 @OA\Property(
 *                     property="property_type_id",
 *                     description="ID du type de propriété",
 *                     type="integer"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Étape 1 terminée avec succès",
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
 *         description="Type de propriété non trouvé",
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
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */

    public function  addHousing_step_1(Request $request){
        try {

                $request->validate([
                    'property_type_id' => 'required|integer'
                ]);

                if(!PropertyType::whereId($request->property_type_id)->first()){
                    return (new ServiceController())->apiResponse(404,[], 'Type de propriété non trouvé');
                }

                $housing = new Housing();
                $housing->property_type_id = $request->property_type_id;
                $housing->user_id = Auth::user()->id;
                $housing->step = 1;
                $housing->save();

                $data =["housing_id" => $housing->id];

                return (new ServiceController())->apiResponse(200,$data, 'Etape 1 terminée avec succès');

        } catch(\Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }

    /**
 * @OA\Post(
 *    path="/api/logement/store_step_2/{housingId}",
 *     summary="Ajouter une étape de logement (étape 2), enregistrement de type de logement",
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
 *                 required={"housing_type_id"},
 *                 @OA\Property(
 *                     property="housing_type_id",
 *                     description="ID du type de logement",
 *                     type="integer"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Étape 2 terminée avec succès",
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
 *         description="Type de logement ou le nombre de voyageur doit avoir pour valeur minimale 1",
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
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */
    public function  addHousing_step_2(Request $request,$housingId){
        try {

                $request->validate([
                    'housing_type_id' => 'required|integer'
                ]);

                if(!HousingType::whereId($request->housing_type_id)->first()){
                    return (new ServiceController())->apiResponse(404,[], 'Type de le nombre de voyageur doit avoir pour valeur minimale 1');
                }

                $housing = Housing::whereId($housingId)->first();

                if(!$housing){
                    return (new ServiceController())->apiResponse(404,[], 'Le nombre de voyageur doit avoir pour valeur minimale 1');
                }

                $housing->housing_type_id = $request->housing_type_id;
                $housing->step = 2;
                $housing->save();

                $data =["housing_id" => $housingId];

                return (new ServiceController())->apiResponse(200,$data, 'Etape 2 terminée avec succès');

        } catch(\Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }


    /**
 * @OA\Post(
 *      path="/api/logement/store_step_4/{housingId}",
 *     summary="Ajouter une étape de logement (étape 4), enregistrement du pays, du département, de la ville, du numéro de téléphone et de l'adresse",
 *    tags={"Ajout de logement"},
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
 *                 required={"country", "department", "city", "telephone", "address"},
 *                 @OA\Property(
 *                     property="country",
 *                     description="Pays",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="department",
 *                     description="Département",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="city",
 *                     description="Ville",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="telephone",
 *                     description="Téléphone",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="address",
 *                     description="Adresse",
 *                     type="string"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Étape 4 terminée avec succès",
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
 *         description="Le nombre de voyageur doit avoir pour valeur minimale 1",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 description="Message d'erreur",
 *                 type="string"
 *             )
 *         )
 *     ),
 *  @OA\Response(
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
 *     @OA\Response(
 *         response=505,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 description="Message d'erreur",
 *                 type="array",
 *                 @OA\Items(
 *                     type="string"
 *                 )
 *             )
 *         )
 *     ),
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */
    public function  addHousing_step_4(Request $request,$housingId){
        try {

            $validator = Validator::make($request->all(), [
                'country' => 'required|string',
                'department' => 'required|string',
                'city' => 'required|string',
                'telephone' => 'required|integer',
                'address' => 'required|string',
            ]);

            $message = [];

            if ($validator->fails()) {
                $message[] = $validator->errors();
                return (new ServiceController())->apiResponse(505,[],$message);
            }

            $housing = Housing::whereId($housingId)->first();

            if(!$housing){
                return (new ServiceController())->apiResponse(404,[], 'Le nombre de voyageur doit avoir pour valeur minimale 1');
            }

            $housing->country = $request->country;
            $housing->department = $request->department;
            $housing->city = $request->city;
            $housing->telephone = $request->telephone;
            $housing->address = $request->address;
            $housing->step = 4;
            $housing->save();

            $data =["housing_id" => $housingId];

            return (new ServiceController())->apiResponse(200,$data, 'Etape 4 terminée avec succès');

        } catch(\Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }

    /**
 * @OA\Post(
 *      path="/api/logement/store_step_5/{housingId}",
 *      summary="Ajouter une étape de logement (étape 5), enregistrement du nombre de voyageurs, du nombre de lits et de la surface",
 *      tags={"Ajout de logement"},
 *      @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         required=true,
 *         description="ID du logement",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *      ),
 *      @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"number_of_traveller", "number_of_bed", "surface"},
 *                 @OA\Property(
 *                     property="number_of_traveller",
 *                     description="Nombre de voyageurs",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="number_of_bed",
 *                     description="Nombre de lits",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="surface",
 *                     description="Surface",
 *                     type="number",
 *                     format="double"
 *                 )
 *             )
 *         )
 *      ),
 *      @OA\Response(
 *         response=200,
 *         description="Étape 5 terminée avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="housing_id",
 *                 description="ID du logement",
 *                 type="integer"
 *             )
 *         )
 *      ),
 *      @OA\Response(
 *         response=404,
 *         description="Le nombre de voyageur doit avoir pour valeur minimale 1",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 description="Message d'erreur",
 *                 type="string"
 *             )
 *         )
 *      ),
 *      @OA\Response(
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
 *      ),
 *      @OA\Response(
 *         response=505,
 *         description="Erreur de validation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 description="Message d'erreur",
 *                 type="array",
 *                 @OA\Items(
 *                     type="string"
 *                 )
 *             )
 *         )
 *      ),
 *      security={
 *         {"bearerAuth": {}}
 *      }
 * )
 */
    public function  addHousing_step_5(Request $request,$housingId){
        try {

            $validator = Validator::make($request->all(), [
                'number_of_traveller' =>'required|integer',
                'number_of_bed' => 'required|integer',
                'surface' => 'required|numeric',
            ]);

            $message = [];

            if ($validator->fails()) {
                $message[] = $validator->errors();
                return (new ServiceController())->apiResponse(505,[],$message);
            }

            $housing = Housing::whereId($housingId)->first();

            if(!$housing){
                return (new ServiceController())->apiResponse(404,[], 'Logement non trouvé');
            }

            if($request->number_of_traveller <= 0){
                {
                    return (new ServiceController())->apiResponse(404,[], 'Le nombre de voyageur doit avoir pour valeur minimale 1');
                }
            }

            if($request->number_of_bed <= 0){
                {
                    return (new ServiceController())->apiResponse(404,[], 'Le nombre de lit doit avoir pour valeur minimale 1');
                }
            }

            if($request->surface <= 15){
                {
                    return (new ServiceController())->apiResponse(404,[], "L'aire de la surface ne peut ni être null, ni négatif et doit avoir pour valeur  minimal 15");
                }
            }

            $housing->number_of_traveller = $request->number_of_traveller;
            $housing->number_of_bed = $request->number_of_bed;
            $housing->surface = $request->surface;
            $housing->step = 5;
            $housing->save();

            $data =["housing_id" => $housingId];

            return (new ServiceController())->apiResponse(200,$data, 'Etape 5 terminée avec succès');

        } catch(\Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }


    /**
 * @OA\Post(
 *      path="/api/logement/store_step_6/{housingId}",
 *      summary="Ajouter une étape de logement (étape 6), enregistrement des acceptations et des règles",
 *      tags={"Ajout de logement"},
 *      @OA\Parameter(
 *          name="housingId",
 *          in="path",
 *          required=true,
 *          description="ID du logement",
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(
 *                  type="object",
 *                  required={
 *                      "is_accept_chill",
 *                      "is_accept_smoking",
 *                      "is_accept_noise",
 *                      "is_accept_alccol",
 *                      "is_camera",
 *                      "is_accept_arm",
 *                      "is_accepted_animal",
 *                      "is_animal_exist"
 *                  },
 *                  @OA\Property(
 *                      property="is_accept_chill",
 *                      description="Acceptation du calme",
 *                      type="boolean"
 *                  ),
 *                  @OA\Property(
 *                      property="is_accept_smoking",
 *                      description="Acceptation du tabagisme",
 *                      type="boolean"
 *                  ),
 *                  @OA\Property(
 *                      property="is_accept_noise",
 *                      description="Acceptation du bruit",
 *                      type="boolean"
 *                  ),
 *                  @OA\Property(
 *                      property="is_accept_alccol",
 *                      description="Acceptation de l'alcool",
 *                      type="boolean"
 *                  ),
 *                  @OA\Property(
 *                      property="is_camera",
 *                      description="Présence de caméras",
 *                      type="boolean"
 *                  ),
 *                  @OA\Property(
 *                      property="is_accept_arm",
 *                      description="Acceptation des armes",
 *                      type="boolean"
 *                  ),
 *                  @OA\Property(
 *                      property="is_accepted_animal",
 *                      description="Acceptation des animaux",
 *                      type="boolean"
 *                  ),
 *                  @OA\Property(
 *                      property="is_animal_exist",
 *                      description="Existence d'animaux",
 *                      type="boolean"
 *                  )
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Étape 6 terminée avec succès",
 *          @OA\JsonContent(
 *              type="object",
 *              @OA\Property(
 *                  property="housing_id",
 *                  description="ID du logement",
 *                  type="integer"
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Logement non trouvé",
 *          @OA\JsonContent(
 *              type="object",
 *              @OA\Property(
 *                  property="message",
 *                  description="Message d'erreur",
 *                  type="string"
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=505,
 *          description="Erreur de validation",
 *          @OA\JsonContent(
 *              type="object",
 *              @OA\Property(
 *                  property="message",
 *                  description="Message d'erreur",
 *                  type="array",
 *                  @OA\Items(
 *                      type="object",
 *                      @OA\Property(
 *                          property="field_name",
 *                          description="Nom du champ",
 *                          type="string"
 *                      ),
 *                      @OA\Property(
 *                          property="errors",
 *                          description="Liste des erreurs pour ce champ",
 *                          type="array",
 *                          @OA\Items(
 *                              type="string"
 *                          )
 *                      )
 *                  )
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=500,
 *          description="Erreur serveur",
 *          @OA\JsonContent(
 *              type="object",
 *              @OA\Property(
 *                  property="message",
 *                  description="Message d'erreur",
 *                  type="string"
 *              )
 *          )
 *      ),
 *      security={
 *          {"bearerAuth": {}}
 *      }
 * )
 */

    public function  addHousing_step_6(Request $request,$housingId){
        try {

            $validator = Validator::make($request->all(), [
                'is_accept_chill' =>'required|boolean',
                'is_accept_smoking' => 'required|boolean',
                'is_accept_noise' => 'required|boolean',
                'is_accept_alccol' => 'required|boolean',
                'is_camera' => 'required|boolean',
                'is_accept_arm' => 'required|boolean',
                'is_accepted_animal' => 'required|boolean',
                'is_animal_exist' => 'required|boolean',
            ]);

            $message = [];

            if ($validator->fails()) {
                $message[] = $validator->errors();
                return (new ServiceController())->apiResponse(505,[],$message);
            }

            $housing = Housing::whereId($housingId)->first();

            if(!$housing){
                return (new ServiceController())->apiResponse(404,[], 'Logement non trouvé');
            }

            $housing->is_accept_chill = $request->is_accept_chill;
            $housing->is_accept_smoking = $request->is_accept_smoking;
            $housing->is_accept_noise = $request->is_accept_noise;
            $housing->is_accept_alccol = $request->is_accept_alccol;
            $housing->is_camera = $request->is_camera;
            $housing->is_accept_arm = $request->is_accept_arm;
            $housing->is_accepted_animal = $request->is_accepted_animal;
            $housing->is_animal_exist = $request->is_animal_exist;
            $housing->step = 6;
            $housing->save();

            $data =["housing_id" => $housingId];

            return (new ServiceController())->apiResponse(200,$data, 'Etape 6 terminée avec succès');

        } catch(\Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }



    public function  addHousing_step_8(Request $request,$housingId){
        try {

            $validator = Validator::make($request->all(), [
                'equipment_housing' => 'nullable|array',
                'equipment_housing.*' => 'integer',
                'category_equipment_housing' => 'required|array',
                'category_equipment_housing.*' => 'integer',
                'category_id' => 'required|array',
                'category_id.*' => 'integer',
                'number_category' => 'required|array',
                'photo_categories.*' => 'required|file|image|max:2048',
                'new_categories' => 'nullable|array',
                'new_categories_numbers' => 'nullable|array',
                 'new_category_photos_.*' => 'nullable|array',
                'new_category_photos_.*.*' => 'file|image|max:2048',
            ]);

            $message = [];

            if ($validator->fails()) {
                $message[] = $validator->errors();
                return (new ServiceController())->apiResponse(505,[],$message);
            }

            $housing = Housing::whereId($housingId)->first();

            if(!$housing){
                return (new ServiceController())->apiResponse(404,[], 'Logement non trouvé');
            }


            if ($request->has('equipment_housing')) {
               
                if($request->has('category_equipment_housing')){
                           if (count($request->input('equipment_housing')) == count($request->input('category_equipment_housing')) ) {
                            foreach ($request->equipment_housing as $index=> $equipmentId) {
                                $EquipmentCategorieExists = Equipment_category::where('equipment_id', $equipmentId)
                                            ->where('category_id', $request->input('category_equipment_housing')[$index])
                                                ->exists();
                    
                                if (!$EquipmentCategorieExists) {
                                    return response()->json(['message' => "Revoyez les id de catégorie et équipement que vous renvoyez.L equipement $equipmentId n est pas associé à la catégorie ".$request->category_equipment_housing[$index]], 200);
                                    } 
                             
                            }
                    
                    }   else{
                        return response()->json(['message' => 'Le nombre de valeurs de équipements  ne correspond pas au nombre de catégorie.'], 200);
                                                } 
                       } else{
                            return response()->json(['message' => 'Renseigner les catégories des équipements s il vous plaît).'], 200);
                         }
               
               }  


            $housing->step = 8;
            $housing->save();

            $data =["housing_id" => $housingId];

            return (new ServiceController())->apiResponse(200,$data, 'Etape 8 terminée avec succès');

        } catch(\Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }

}
