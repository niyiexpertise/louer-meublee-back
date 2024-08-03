<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Equipment_category;
use App\Models\Housing;
use App\Models\Housing_charge;
use App\Models\housing_preference;
use App\Models\HousingType;
use App\Models\photo;
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

            if($request->has('surface')){
                if(!is_numeric($request->surface)){
                    return (new ServiceController())->apiResponse(404,[], "La valeur de l'aire de surface doit être un nombre");
                }
                if($request->surface <= 15){
                    {
                        return (new ServiceController())->apiResponse(404,[], "L'aire de la surface ne peut être inférieur à 15m², ni négatif");
                    }
                }
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
                'is_accept_arm' => 'required|boolean',
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


    /**
 * @OA\Post(
 *     path="/api/logement/store_step_9/{housingId}",
 *     summary="Ajouter des photos au logement (étape 9)",
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
 *                 required={"photos"},
 *                 @OA\Property(
 *                     property="photos",
 *                     description="Photos à télécharger",
 *                     type="array",
 *                     @OA\Items(
 *                         type="string",
 *                         format="binary"
 *                     )
 *                 ),
 *                
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Étape 9 terminée avec succès",
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
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */
    public function  addHousing_step_9(Request $request,$housingId){
        try {

            $request->validate([
                'photos' => 'required'
            ]);

            $housing = Housing::whereId($housingId)->first();

            if(!$housing){
                return (new ServiceController())->apiResponse(404,[], 'Logement non trouvé');
            }

            foreach ($request->file('photos') as $index => $photo) {
                $photoName = uniqid() . '.' . $photo->getClientOriginalExtension();
                $photoPath = $photo->move(public_path('image/photo_logement'), $photoName);

                if(env('MODE') == 'PRODUCTION'){
                    $photoUrl = url('/image/photo_logement/' . $photoName);
                }

                if(env('MODE') == 'DEVELOPPEMENT'){
                    $ip= env('LOCAL_ADDRESS');
                    $photoUrl = $ip.'/image/photo_logement/' . $photoName;
                }
               
                $type = $photo->getClientOriginalExtension();
                $photoModel = new photo();
                $photoModel->path = $photoUrl;
                $photoModel->extension = $type;
                $photoModel->housing_id = $housingId;;
                $photoModel->save();
            }

            $housing->step = 9;
            $housing->save();

            $data = ["housing_id" => $housingId];
            

            return (new ServiceController())->apiResponse(200,$data, 'Etape 9 terminée avec succès');

        } catch(\Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }


    /**
 * @OA\Post(
 *      path="/api/logement/store_step_10/{housingId}",
 *      summary="Ajouter une étape de logement (étape 10), enregistrement de la photo de couverture",
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
 *                  required={"profile_photo_id"},
 *                  @OA\Property(
 *                      property="profile_photo_id",
 *                      description="ID de la photo à définir comme couverture",
 *                      type="integer"
 *                  )
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Étape 10 terminée avec succès",
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
 *          description="Logement ou photo non trouvés",
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

    public function  addHousing_step_10(Request $request,$housingId){
        try {

            $request->validate([
                'profile_photo_id' => 'required'
            ]);

            $housing = Housing::whereId($housingId)->first();

            if(!$housing){
                return (new ServiceController())->apiResponse(404,[], 'Logement non trouvé');
            }

           $photo = Photo::whereId($request->profile_photo_id)->first();

           if(!$photo){
            return (new ServiceController())->apiResponse(404,[], 'Photo non trouvé');
            }

            if($photo->housing_id != $housingId){
                return (new ServiceController())->apiResponse(404,[], "Cette photo n'est pas associer à ce logement");
            }

            $existPhoto = Photo::where('is_couverture',true)->first();
            if($existPhoto){
                $existPhoto->update(['is_couverture' => false]);
            }

            $photo->is_couverture = true;
            $photo->save();

            $housing->step = 10;
            $housing->save();

            $data =[
                "housing_id" => $housingId,
                "housing_files" => photo::where('housing_id',$housingId)->where('is_deleted', false)->get()
            ];

            return (new ServiceController())->apiResponse(200,$data, 'Etape 10 terminée avec succès');

        } catch(\Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }

    
    /**
 * @OA\Post(
 *      path="/api/logement/store_step_11/{housingId}",
 *      summary="Ajouter une étape de logement (étape 11), enregistrement des préférences, des nom et des descriptions",
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
 *          required=false,
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(
 *                  type="object",
 *                  @OA\Property(
 *                      property="name",
 *                      description="Nom du logement",
 *                      type="string"
 *                  ),
 *                  @OA\Property(
 *                      property="description",
 *                      description="Description du logement",
 *                      type="string"
 *                  ),
 *                  @OA\Property(
 *                      property="preferences",
 *                      description="Liste des préférences associées au logement",
 *                      type="array",
 *                      @OA\Items(
 *                          type="integer"
 *                      )
 *                  )
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Étape 11 terminée avec succès",
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

    public function  addHousing_step_11(Request $request,$housingId){
        try {

            $housing = Housing::whereId($housingId)->first();

            if(!$housing){
                return (new ServiceController())->apiResponse(404,[], 'Logement non trouvé');
            }

            $housing->name = $request->name??null;
            $housing->description = $request->description??null;

            if ($request->has('preferences')) {
                foreach(housing_preference::where('housing_id',$housingId)->get() as $exist){
                    $exist->delete();
                }
                foreach ($request->input('preferences') as $preference) {
                    if(!housing_preference::where('housing_id',$housingId)->where('preference_id',$preference)->exists()){
                        $housingPreference = new housing_preference();
                        $housingPreference->housing_id = $housing->id;
                        $housingPreference->preference_id = $preference;
                        $housingPreference->is_verified = true;
                        $housingPreference->save();
                    }
             }
            }

            $housing->step = 11;
            $housing->save();

            $data =[
                "housing_id" => $housingId,
            ];

            return (new ServiceController())->apiResponse(200,$data, 'Etape 11 terminée avec succès');

        } catch(\Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }

    private function areAllValuesNumeric(array $values): bool
    {
        foreach ($values as $value) {
            if (!is_numeric($value)) {
                return false;
            }
        }
        return true;
    }

    private function areAllValuesPositif(array $values): bool
    {
        foreach ($values as $value) {
            if ($value<0) {
                return false;
            }
        }
        return true;
    }


    /**
 * @OA\Post(
 *      path="/api/logement/store_step_12/{housingId}",
 *      summary="Ajouter une étape de logement (étape 12), enregistrement des charges",
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
 *                      "Hotecharges",
 *                      "Travelercharges",
 *                      "Travelerchargesvalue"
 *                  },
 *                  @OA\Property(
 *                      property="Hotecharges",
 *                      description="Liste des identifiants des charges pour l'hôte",
 *                      type="array",
 *                      @OA\Items(
 *                          type="integer"
 *                      )
 *                  ),
 *                  @OA\Property(
 *                      property="Travelercharges",
 *                      description="Liste des identifiants des charges pour le voyageur",
 *                      type="array",
 *                      @OA\Items(
 *                          type="integer"
 *                      )
 *                  ),
 *                  @OA\Property(
 *                      property="Travelerchargesvalue",
 *                      description="Valeurs associées aux charges pour le voyageur",
 *                      type="array",
 *                      @OA\Items(
 *                          type="number",
 *                          format="float"
 *                      )
 *                  )
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Étape 12 terminée avec succès",
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
 *          description="Logement ou charge non trouvé",
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

    public function  addHousing_step_12(Request $request,$housingId){
        try {

            $housing = Housing::whereId($housingId)->first();

            if(!$housing){
                return (new ServiceController())->apiResponse(404,[], 'Logement non trouvé');
            }

            if ($request->has('Hotecharges')) {

                    foreach ($request->Hotecharges as $HotechargesId) {
                    $HotechargesExists = Charge::where('id', $HotechargesId)->exists();

                    if (!$HotechargesExists) {
                        return (new ServiceController())->apiResponse(404,[], 'Revoyez les id de charges que vous renvoyez;précisement la variable HoteCharge.');
                        }
                }
            }
            if ($request->has('Travelercharges')) {
                if($request->has('Travelerchargesvalue')){
                    if (count($request->input('Travelercharges')) == count($request->input('Travelerchargesvalue')) ) {

                        if (!$this->areAllValuesNumeric($request->input('Travelerchargesvalue'))) {
                            return (new ServiceController())->apiResponse(404,[], 'Les valeurs des charges doivent être des nombres.');
                        }
                        if (!$this->areAllValuesPositif($request->input('Travelerchargesvalue'))) {
                            return (new ServiceController())->apiResponse(404,[], 'Les valeurs des charges doivent être positive.');
                        }
                            foreach ($request->Travelercharges as $TravelerchargesId) {
                                $TravelerchargesExists = Charge::where('id', $TravelerchargesId)->exists();

                                if (!$TravelerchargesExists) {
                                    return (new ServiceController())->apiResponse(404,[], 'Revoyez les id de charges que vous renvoyez;précisement la variable TravelerCharge.');
                                }
                            }

                        }   else{
                                return (new ServiceController())->apiResponse(404,[], 'Le nombre de valeurs de charges Traveler ne correspond pas au nombre de charges.');
                            }
                       } else{
                            return (new ServiceController())->apiResponse(404,[], 'Renseigner svp les valeurs de chaque charge. si elle ne sont renseigné,mettez comme valeur 0 pour chacun(Indicatif pour font end).');
                         }
               }
               foreach(Housing_charge::where('housing_id',$housingId)->get() as $exist){
                $exist->delete();
            }
            if ($request->has('Hotecharges')) {
                foreach ($request->input('Hotecharges') as $index => $charge) {
                    if(!Housing_charge::where('housing_id',$housingId)->where('charge_id',$charge)->exists()){
                        $housingCharge = new Housing_charge();
                        $housingCharge->housing_id = $housing->id;
                        $housingCharge->charge_id = $charge;
                        $housingCharge->is_mycharge= true;
                        $housingCharge->save();
                    }
                }
             }
             if ($request->has('Travelercharges')) {
                foreach ($request->input('Travelercharges') as $index => $charge) {
                    if(!Housing_charge::where('housing_id',$housingId)->where('charge_id',$charge)->exists()){
                        $housingCharge = new Housing_charge();
                        $housingCharge->housing_id = $housing->id;
                        $housingCharge->charge_id = $charge;
                        $housingCharge->is_mycharge= false;
                        $housingCharge->valeur=$request->input('Travelerchargesvalue')[$index];
                        $housingCharge->save();
                    }
                }
             }

            $housing->step = 12;

            $housing->save();

            $data =["housing_id" => $housingId];

            return (new ServiceController())->apiResponse(200,$data, 'Etape 12 terminée avec succès');

        } catch(\Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }


}
