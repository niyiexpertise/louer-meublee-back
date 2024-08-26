<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationEmail;
use Illuminate\Support\Facades\Validator;
use App\Models\Housing;
use App\Models\HousingType;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use App\Models\Charge;
use App\Models\housing_preference;
use App\Models\reduction;
use App\Models\promotion;
use App\Models\photo;
use App\Models\housing_price;
use App\Models\File;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Equipment;
use App\Models\User_right;
use App\Models\Right;
use App\Models\Equipment_category;
use App\Models\Housing_equipment;
use App\Models\Housing_category_file;
use Illuminate\Support\Facades\File as F;
use App\Models\Category;
use App\Models\Housing_charge;
use App\Models\Review_reservation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmail;
use App\Mail\NotificationEmailwithoutfile;
use App\Models\UserVisiteHousing;
use App\Services\FileService;
use DateTime;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Exception;
class AddHousingZController extends Controller
{

    protected $fileService;

public function __construct(FileService $fileService = null)
{
    $this->fileService = $fileService ?: new FileService();
}


    public function checkOwner($housingId){
        if(Auth::user()->id != Housing::whereId($housingId)->first()->user_id){
            return (new ServiceController())->apiResponse(404,[], "Vous ne pouvez pas modifier un logement que vous n'avez pas ajouter. Veuillez ne pas procéder à de telles modifications sans remplir les critères .");
        }
    }
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

 public function addHousing_step_3(Request $request, $housingId){
    try {
        $housing = Housing::whereId($housingId)->first();
        if (!$housing) {
            return (new ServiceController())->apiResponse(404, [], 'Logement non trouvé');
        }
       $errorcheckOwner= $this->checkOwner($housingId);
        if($errorcheckOwner){
            return $errorcheckOwner;
        }
        $validationResponse =$this->validateStepOrder(3, $housingId);
        if ($validationResponse) {
            return $validationResponse;
        }
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
        $housing = Housing::whereId($housingId)->first();
        if (!$housing) {
            return (new ServiceController())->apiResponse(404, [], 'Logement non trouvé');
        }
       $errorcheckOwner= $this->checkOwner($housingId);
        if($errorcheckOwner){
            return $errorcheckOwner;
        }
        $validationResponse =$this->validateStepOrder(7, $housingId);
        if ($validationResponse) {
            return $validationResponse;
        }
        $validator = Validator::make($request->all(), [
            'interior_regulation' => 'nullable|string',
            'interior_regulation_pdf' => 'nullable'
        ]);

        $message = [];
        if ($validator->fails()) {
            $message[] = $validator->errors();
            return (new ServiceController())->apiResponse(505, [], $message);
        }

        if (empty($request->interior_regulation) && !$request->hasFile('interior_regulation_pdf')) {
            return (new ServiceController())->apiResponse(404, [], 'Au moins un des champs doit être renseigné.');
        }


        if ($request->hasFile('interior_regulation_pdf') && is_array($request->file('interior_regulation_pdf'))) {
            $pdfArray = $request->file('interior_regulation_pdf');
            
            // Vérifiez que le premier élément du tableau est un fichier
            if (isset($pdfArray[0])) {
                $pdfFile = $pdfArray[0];
                // $extension = $pdfFile->getClientOriginalExtension();
        
                // if (strtolower($extension) !== 'pdf') {
                //     return (new ServiceController())->apiResponse(404, [], 'Le fichier doit être au format PDF.');
                // }
        
                // Téléchargez le fichier PDF
                $validationResultFile= $this->fileService->uploadFiles($pdfFile, 'reglement_interieur','extensionDocument');

                if ($validationResultFile['fails']) {
                    return (new ServiceController())->apiResponse(404, [], $validationResultFile['result']);
                }

                 $housing->interior_regulation_pdf =  $validationResultFile['result'];
            } else {
                return (new ServiceController())->apiResponse(404, [], 'Le fichier PDF n\'a pas été correctement envoyé.');
            }
        } else {
            return (new ServiceController())->apiResponse(404, [], 'Aucun fichier PDF trouvé dans les données.');
        }
        

        if (!empty($request->interior_regulation)) {
            $housing->interior_regulation = $request->interior_regulation;
        }

        $housing->step = 7;
        $housing->save();

        $data = ["housing_id" => $housing->id,"pdf_reglement" => $housing->interior_regulation_pdf,"text_reglement" => $housing->interior_regulation];
        return (new ServiceController())->apiResponse(200, $data, 'Étape 7 terminée avec succès');

    } catch(\Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}



 public function  addHousing_step_8_old(Request $request,$housingId){
        try {
            $housing = Housing::whereId($housingId)->first();

            if(!$housing){
                return (new ServiceController())->apiResponse(404,[], 'Logement non trouvé');
            }

            if(Auth::user()->id != Housing::whereId($housingId)->first()->user_id){
                return (new ServiceController())->apiResponse(404,[], "Vous ne pouvez pas modifier un logement que vous n'avez pas ajouter");
            }
            $validationResponse =$this->validateStepOrder(8, $housingId);
            if ($validationResponse) {
                return $validationResponse;
            }
            $validator = Validator::make($request->all(), [
                'equipment_housing' => 'required|array',
                'equipment_housing.*' => 'integer',
                'category_equipment_housing' => 'required|array',
                'category_equipment_housing.*' => 'integer',
                'category_id' => 'required|array',
                'category_id.*' => 'integer',
                'number_category' => 'required|array',
                'new_categories' => 'nullable|array',
                'new_categories_numbers' => 'nullable|array',
                'new_equipment' => 'nullable|array',
                'new_equipment.*' => 'string',
                'new_equipment_category' => 'nullable|array',
                'new_equipment_category.*' => 'integer',
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




            // Reinitialisation de la donnée
             $this->deleteHousingData($housingId);

            // Validation de des équipements existants par pièce

            if(count($request->input('equipment_housing')) == 0){
                    return (new ServiceController())->apiResponse(404,[], "Renseigner au moin un équipement svp");

                }
            if (count($request->input('equipment_housing')) == count($request->input('category_equipment_housing')) )
            {
                foreach ($request->equipment_housing as $index=> $equipmentId) {
                    $EquipmentCategorieExists = Equipment_category::where('equipment_id', $equipmentId)
                                ->where('category_id', $request->input('category_equipment_housing')[$index])
                                    ->exists();

                    if (!$EquipmentCategorieExists) {
                        return (new ServiceController())->apiResponse(404,[], "Revoyez les id de catégorie et équipement que vous renvoyez.L'équipement $equipmentId n est pas associé à la catégorie ".$request->category_equipment_housing[$index]);
                        }

                    }

            }else{
                    return (new ServiceController())->apiResponse(404,[], "La taille de la variable <<equipment_housing >> doit être égale à la taille de <<category_equipment_housing>>");
            }

            $errors = $this->validateUniqueEquipmentCategories($request->equipment_housing, $request->category_equipment_housing);

            if (!empty($errors)) {
                return (new ServiceController())->apiResponse(505,[],$errors);
            }

  // Validation information des pièces et des photos asociées

            if(count($request->input('category_id')) == 0){
                return (new ServiceController())->apiResponse(404,[], "Renseigner au moin une pièce svp");

            }
            if (count($request->input('category_id')) != count($request->input('number_category')))
             {
                 return (new ServiceController())->apiResponse(404,[], "La taille de la variable <<category_id>> doit être égale à la taille de <<number_category>>");

             }
            foreach($request->input('category_id') as $categoryId){
                $existCategorie = Category::whereId($categoryId)->first();
                if(!$existCategorie)
                {
                   return (new ServiceController())->apiResponse(404,[], "La categorie ayant pour id $categoryId n'existe pas");
                }

            }

            $items = $request->input('category_id');

            $uniqueItems = array_unique($items);

            if (count($uniqueItems) < count($items)) {
                 return (new ServiceController())->apiResponse(404,[], "Vous ne pouvez pas ajouter deux  catégories existants avec le même nom.");
            }
            foreach ($request->input('category_id') as $index => $categoryId) {
                $photoCategoryKey = 'photo_categories' . $categoryId;
                if (!$request->hasFile($photoCategoryKey)) {

                    return (new ServiceController())->apiResponse(404,[], "Aucune clée touvée pour stocker les  photo de la catégorie $categoryId.");
                }
                if(count($request->file($photoCategoryKey)) == 0){
                    return (new ServiceController())->apiResponse(404,[], "Il doit y avoir au moins une photo pour la catégorie $categoryId");

                }
            }

     //validation d'ajout de nouvelles pièces qui,n'existaient pas avec les photos

     if($request->has('new_categories') and count($request->input('new_categories')) == 0)
     {
       return (new ServiceController())->apiResponse(404,[], "Vous devrez renseigner obligatoirement une nouvelle pièce");

    }

     if (($request->has('new_categories') && !$request->has('new_categories_numbers')) || (!$request->has('new_categories') && $request->has('new_categories_numbers'))) {

        return (new ServiceController())->apiResponse(404,[], "new_categories et new_categories_numbers sont simultanément obligatoires. Vous devez tous renseigner");

    }

    if ($request->has('new_categories') && $request->has('new_categories_numbers') && count($request->input('new_categories')) != count(($request->input('new_categories_numbers'))) ) {
        return (new ServiceController())->apiResponse(404,[], "new_categories et new_categories_numbers doivent avoir la même taille");
    }
     if ($request->has('new_categories') )
     {
        foreach($request->input('new_categories') as $categoryName)
        {
                $items = $request->input('new_categories');

                $uniqueItems = array_unique($items);

                if (count($uniqueItems) < count($items)) {
                     return (new ServiceController())->apiResponse(404,[], "Vous ne pouvez pas ajouter deux nouvelles catégories avec le même nom.");
                }
               $category = Category::all();
               foreach($category as $e)
               {
                if($categoryName == $e->name && Category::whereName($categoryName)->exists())
                    {
                    return (new ServiceController())->apiResponse(404,[], "Une autre catégorie ayant le même nom existe déjà dans la base de donnée ou a été exclu.$categoryName existe déjà comme nom de pièce");
                     }



                foreach ($request->input('new_categories') as $index => $new_categoriesName)
                {
                    $photoCategoryKey = 'new_category_photos_' . $new_categoriesName;

                    if (!$request->hasFile($photoCategoryKey))
                    {
                        return (new ServiceController())->apiResponse(404,[], "Aucune photo trouvée pour la catégorie $new_categoriesName. $photoCategoryKey est requis comme clé");

                    }
                    if(count($request->file($photoCategoryKey)) == 0)
                    {
                        return (new ServiceController())->apiResponse(404,[], "Il doit y avoir au moins une photo pour la catégorie $new_categoriesName.");

                    }
                }
                    }

   // Validation d'ajout d'équipements inexistants
   if($request->has('new_equipment') and count($request->input('new_equipment')) == 0)
     {
       return (new ServiceController())->apiResponse(404,[], "Vous devrez renseigner obligatoirement les nouveaux équipements. new_equipment est vide");

    }

     if (($request->has('new_equipment') && !$request->has('new_equipment_category')) || (!$request->has('new_equipment') && $request->has('new_equipment_category')))
     {
        return (new ServiceController())->apiResponse(404,[], "new_equipment et new_equipment_category sont simultanément obligatoires. Vous devez tous renseigner");

     }
    if ($request->has('new_equipment') and count($request->input('new_equipment')) != count($request->input('new_equipment_category')) ) {

        return (new ServiceController())->apiResponse(404,[], "La taille de la variable <<new_equipment>> doit être égale à la taille de <<new_equipment_category>>");

    }




    if ($request->has('new_equipment')) {
        foreach ($request->new_equipment as $index=> $equipmentId)
         {
            $CategorieExists = Category::where('id', $request->input('new_equipment_category')[$index])
                            ->exists();

            if (!$CategorieExists) {

                 return (new ServiceController())->apiResponse(404,[], "La clé new_equipment_category contient l'id ". $request->new_equipment_category[$index]." qui n'existe pas");

                }
                $equipments = Equipment::all();
                foreach($equipments as $e){
                    if($equipmentId == $e->name && Equipment::whereName($equipmentId)->exists()){
                    return (new ServiceController())->apiResponse(404,[], "Un autre équipement ayant le même nom existe déjà dans la base de donnée ou a été exclu.$e->name existe déjà comme nom d'équipment");
                    }
                }
            }


            $errors = $this->validateUniqueEquipmentCategories($request->new_equipment, $request->new_equipment_category);

            if (!empty($errors)) {
                return (new ServiceController())->apiResponse(505,[],$errors);
            }


       }


    // Debut de Mise à jours pour les équipements existants associés aux pièces pour un logement donné
     if ($request->has('equipment_housing')) {

        foreach ($request->equipment_housing as $index => $equipmentId ) {
            $equipment = Equipment::find($equipmentId);
            if ($equipment) {
                $housingEquipment = new Housing_equipment();
                $housingEquipment->equipment_id = $equipmentId;
                $housingEquipment->category_id =  $request->input('category_equipment_housing')[$index];
                $housingEquipment->housing_id = $housing->id;
                $housingEquipment->is_verified = true;
                $housingEquipment->save();
            }
        }
    }
    // Debut de Mise à jours pour les équipements inexistants associés aux pièces pour un logement donné
    if ($request->has('new_equipment') && $request->has('new_equipment_category')) {
        $newEquipments = $request->input('new_equipment');
        $newEquipmentCategories = $request->input('new_equipment_category');

        foreach ($newEquipments as $index => $newEquipment) {
            $equipment = Equipment::whereName($newEquipment)->first();
            if(!$equipment) {
            $equipment = new Equipment();
            $equipment->name = $newEquipment;
            $equipment->is_verified=false;
            $equipment->save();
            }
            $equipmentCategory = new Equipment_category();
            $equipmentCategory->equipment_id = $equipment->id;
            $equipmentCategory->category_id = $newEquipmentCategories[$index];
            $equipmentCategory->save();

            $housingEquipment = new Housing_equipment();
            $housingEquipment->equipment_id = $equipment->id;
            $housingEquipment->category_id =$newEquipmentCategories[$index];
            $housingEquipment->housing_id = $housing->id;
            $housingEquipment->is_verified = false;
            $housingEquipment->save();
        }
    }



    // Debut de Mise à jours pour les catégories existantes pour un logement donné


     if ($request->has('category_id')) {

        foreach ($request->input('category_id') as $index => $categoryId) {
            $housingCategoryId = $housing->id;
            $photoCategoryKey = 'photo_categories' . $categoryId;
            $photoFiles = $request->file($photoCategoryKey);
            $identity_profil_url = '';
            foreach ($photoFiles as $fileId) {
                $photoModel = new File();
                $uploadedPath = $this->fileService->uploadFiles($fileId, 'image/photo_category');

              //  $photoName = uniqid() . '.' . $fileId->getClientOriginalExtension();
              /*  $photoPath = $fileId->move(public_path('image/photo_category'), $photoName);
                if(env('MODE') == 'PRODUCTION'){
                    $photoUrl = url('/image/photo_logement/' . $photoName);
                }
                if(env('MODE') == 'DEVELOPPEMENT'){
                    $ip=env('LOCAL_ADDRESS');
                    $photoUrl = $ip.'/image/photo_logement/' . $photoName;
                }*/

                $photoModel->path = $uploadedPath;

                $photoModel->save();


                $housingCategoryFile = new Housing_category_file();
                $housingCategoryFile->housing_id = $housingCategoryId;
                $housingCategoryFile->category_id = $categoryId;
                $housingCategoryFile->file_id = $photoModel->id;
                $housingCategoryFile->number = $request->input('number_category')[$index];
                $housingCategoryFile->is_verified = true;
                $housingCategoryFile->save();
            }
        }
    }


    // Debut de Mise à jours pour les catégories inexistantes pour un logement donné



    if($request->has('new_categories')&& $request->has('new_categories_numbers')) {

    $newCategories = $request->input('new_categories');
    $newCategoriesNumbers = $request->input('new_categories_numbers');

    foreach ($newCategories as $index => $newCategory) {
        $categoryName = $newCategory;
        $categoryNumber = $newCategoriesNumbers[$index];
        $photoCategoryKey = 'new_category_photos_' . $categoryName;
        $categoryPhotos = $request->file($photoCategoryKey);
        $category = new Category();
        $category->name = $categoryName;
        $category->is_verified = false;
        $category->save();

        foreach ($categoryPhotos as $photoFile) {
            $uploadedPath = $this->fileService->uploadFiles($photoFile, 'image/photo_category');

          /*  $photoPath = $photoFile->move(public_path('image/photo_category'), $photoName);
            if(env('MODE') == 'PRODUCTION')
            {
                    $photoUrl = url('/image/photo_logement/' . $photoName);
            }
            if(env('MODE') == 'DEVELOPPEMENT'){
                    $ip=env('LOCAL_ADDRESS');
                    $photoUrl = $ip.'/image/photo_logement/' . $photoName;
            }
                    */
            $photo = new File();
            $photo->path = $uploadedPath;
            $photo->save();



            $housingCategoryFile = new Housing_category_file();
            $housingCategoryFile->housing_id = $housing->id;
            $housingCategoryFile->category_id = $category->id;
            $housingCategoryFile->file_id = $photo->id;
            $housingCategoryFile->number = $categoryNumber;
            $housingCategoryFile->is_verified = false;
            $housingCategoryFile->save();
        }
      }
    }

        $housing->step = 8;
        $housing->save();

        $data =["housing_id" => $housingId];

            return (new ServiceController())->apiResponse(200,$data, 'Etape 8 terminée avec succès');

        }}  } catch(\Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }

}
    private function validateUniqueEquipmentCategories(array $equipmentHousing, array $categoryEquipmentHousing): array
    {
        $seenPairs = [];
        $errors = [];

        if (count($equipmentHousing) !== count($categoryEquipmentHousing)) {
            return ['error' => 'Les tableaux doivent avoir la même longueur.'];
        }

        foreach ($equipmentHousing as $index => $equipment) {
            $category = $categoryEquipmentHousing[$index];
            $pair = [$equipment, $category];

            if (in_array($pair, $seenPairs)) {
                $errors[] = "Le couple (equipment: $equipment, category: $category) se répète à l'index $index.";
            } else {
                $seenPairs[] = $pair;
            }
        }

        return $errors;
    }




    private function deleteHousingData($housingId) {
        // Start the transaction
        DB::transaction(function () use ($housingId) {
            // Step 1: Get all equipment_ids from housing_equipment
            $equipmentIds = DB::table('housing_equipments')
                            ->where('housing_id', $housingId)
                            ->pluck('equipment_id');

            // Step 2: Delete entries from housing_equipment
            DB::table('housing_equipments')
                ->where('housing_id', $housingId)
                ->delete();

            // Step 3: Delete non-verified equipment
            DB::table('equipment')
                ->whereIn('id', $equipmentIds)
                ->where('is_verified', false)
                ->delete();

            // Step 4: Get all category_ids and file_ids from housing_category_file
            $categoryFileData = DB::table('housing_category_files')
                                ->where('housing_id', $housingId)
                                ->get(['category_id', 'file_id']);

            // Step 5: Delete entries from housing_category_file
            DB::table('housing_category_files')
                ->where('housing_id', $housingId)
                ->delete();

            // Step 6: Delete non-verified categories and associated files
            foreach ($categoryFileData as $data) {
                $categoryId = $data->category_id;
                $fileId = $data->file_id;

                // Delete non-verified category
                DB::table('categories')
                    ->where('id', $categoryId)
                    ->where('is_verified', false)
                    ->delete();

                // Delete associated file
                DB::table('files')
                    ->where('id', $fileId)
                    ->delete();
            }

        });
    }


/**
 * @OA\Post(
 *     path="/api/logement/store_step_13/{housingId}",
 *     summary="Étape 13: Ajouter le prix d'un logement",
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
 *                 required={"price"},
 *                 @OA\Property(
 *                     property="price",
 *                     description="Prix du logement",
 *                     type="number",
 *                     format="float"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Étape 13 terminée avec succès",
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
 *         description="Logement non trouvé ou prix invalide",
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
 *     @OA\Response(
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
public function addHousing_step_13(Request $request, $housingId) {
    try {
        $housing = Housing::whereId($housingId)->first();
        if (!$housing) {
            return (new ServiceController())->apiResponse(404, [], 'Logement non trouvé');
        }

       $errorcheckOwner= $this->checkOwner($housingId);
        if($errorcheckOwner){
            return $errorcheckOwner;
        }
        $validationResponse =$this->validateStepOrder(13, $housingId);
        if ($validationResponse) {
            return $validationResponse;
        }
        // Validation des champs requis
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric'
        ]);

        $message = [];
        if ($validator->fails()) {
            $message[] = $validator->errors();
            return (new ServiceController())->apiResponse(505, [], $message);
        }

        $price = $request->input('price');
        if ($price <= 0) {
            return (new ServiceController())->apiResponse(404, [], 'Le prix doit être supérieur à 0.');
        }



        $housing->price = $price;
        $housing->step = 13;
        $housing->save();

        $data = ["housing_id" => $housing->id];

        return (new ServiceController())->apiResponse(200, $data, 'Étape 13 terminée avec succès');

    } catch(\Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}

/**
 * @OA\Post(
 *     path="/api/logement/store_step_15/{housingId}",
 *     summary="Étape 15: Ajouter des informations sur la réservation d'un logement",
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
 *                 required={"minimum_duration", "time_before_reservation"},
 *                 @OA\Property(
 *                     property="minimum_duration",
 *                     description="Durée minimale de réservation en heures (doit être supérieur à 0)",
 *                     type="integer",
 *                     example=1
 *                 ),
 *                 @OA\Property(
 *                     property="time_before_reservation",
 *                     description="Temps requis avant la réservation en heures (peut être 0)",
 *                     type="integer",
 *                     example=24
 *                 ),
 *                 @OA\Property(
 *                     property="departure_instruction",
 *                     description="Instructions de départ (optionnel)",
 *                     type="string",
 *                     example="Veuillez laisser les clés sur la table de la cuisine."
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Étape 15 terminée avec succès",
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
 *         description="Logement non trouvé ou données invalides",
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
 *     @OA\Response(
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


public function addHousing_step_15(Request $request, $housingId) {
    try {
        $housing = Housing::whereId($housingId)->first();
        if (!$housing) {
            return (new ServiceController())->apiResponse(404, [], 'Logement non trouvé');
        }

       $errorcheckOwner= $this->checkOwner($housingId);
        if($errorcheckOwner){
            return $errorcheckOwner;
        }
        $validationResponse =$this->validateStepOrder(15, $housingId);
        if ($validationResponse) {
            return $validationResponse;
        }
        // Validation des champs requis
        $validator = Validator::make($request->all(), [
            'minimum_duration' => 'required|numeric',
            'time_before_reservation' => 'required|numeric',
            'departure_instruction' => 'nullable|string'
        ]);

        $message = [];
        if ($validator->fails()) {
            $message[] = $validator->errors();
            return (new ServiceController())->apiResponse(505, [], $message);
        }


        $minimumDuration = $request->input('minimum_duration');
        $timeBeforeReservation = $request->input('time_before_reservation');

        if ($minimumDuration <= 0) {
            return (new ServiceController())->apiResponse(404, [], 'La durée minimale doit être supérieure à 0.');
        }

        if ($timeBeforeReservation < 0) {
            return (new ServiceController())->apiResponse(404, [], 'Le temps avant la réservation ne peut pas être négatif.');
        }




        $housing->minimum_duration = $minimumDuration;
        $housing->time_before_reservation = $timeBeforeReservation;
        if ($request->has('departure_instruction'))
        {
        $housing->departure_instruction = $request->input('departure_instruction');
        }
        $housing->step = 15;
        $housing->save();

        $data = ["housing_id" => $housing->id];

        return (new ServiceController())->apiResponse(200, $data, 'Étape 15 terminée avec succès');

    } catch(\Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}



/**
 * @OA\Post(
 *     path="/api/logement/store_step_16/{housingId}",
 *     summary="Étape 16: Ajouter des réductions basées sur le nombre de nuits pour un logement",
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
 *                 required={"reduction_night_number", "reduction_value_night_number"},
 *                 @OA\Property(
 *                     property="reduction_night_number",
 *                     description="Tableau des nombres de nuits pour les réductions (doivent être des entiers supérieurs à zéro)",
 *                     type="array",
 *                     @OA\Items(type="integer", example=1)
 *                 ),
 *                 @OA\Property(
 *                     property="reduction_value_night_number",
 *                     description="Tableau des valeurs des réductions pour les nombres de nuits (doivent être des nombres non négatifs)",
 *                     type="array",
 *                     @OA\Items(type="number", format="float", example=10.5)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Étape 16 terminée avec succès",
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
 *         description="Logement non trouvé ou données invalides",
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
 *     @OA\Response(
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
public function addHousing_step_16(Request $request, $housingId) {
    try {
        $housing = Housing::whereId($housingId)->first();
        if (!$housing) {
            return (new ServiceController())->apiResponse(404, [], 'Logement non trouvé');
        }

        $errorcheckOwner = $this->checkOwner($housingId);
        if ($errorcheckOwner) {
            return $errorcheckOwner;
        }

        $validationResponse = $this->validateStepOrder(16, $housingId);
        if ($validationResponse) {
            return $validationResponse;
        }

        // Validation des champs requis
        $validator = Validator::make($request->all(), [
            'reduction_night_number' => 'nullable|array',
            'reduction_value_night_number' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return (new ServiceController())->apiResponse(505, [], $validator->errors());
        }

        // Récupération des champs avec vérification d'existence
        $nightNumbers = $request->input('reduction_night_number', []);
        $values = $request->input('reduction_value_night_number', []);

        // Validation de la taille des tableaux seulement s'ils existent
        if (!empty($nightNumbers) && !empty($values) && count($nightNumbers) !== count($values)) {
            return (new ServiceController())->apiResponse(404, [], 'Les tailles des tableaux de réductions ne correspondent pas.');
        }

        // Validation des doublons dans reduction_night_number
        if (!empty($nightNumbers) && count($nightNumbers) !== count(array_unique($nightNumbers))) {
            return (new ServiceController())->apiResponse(404, [], 'Les nombres de nuits contiennent des doublons.');
        }

        // Validation des nombres de nuits
        foreach ($nightNumbers as $nightNumber) {
            if (intval($nightNumber) <= 0) {
                return (new ServiceController())->apiResponse(404, [], 'Les nombres de nuits doivent être des entiers supérieurs à zéro.');
            }
        }

        // Validation des valeurs des réductions
        foreach ($values as $value) {
            if (floatval($value) <= 0) {
                return (new ServiceController())->apiResponse(404, [], 'Les valeurs des réductions doivent être des nombres non négatifs ou non nuls.');
            }
        }

        // Suppression des réductions existantes
        $reductionsDeleted = Reduction::where('housing_id', $housingId)->delete();

        // Sauvegarde des nouvelles réductions
        foreach ($nightNumbers as $index => $nightNumber) {
            $reduction = new Reduction();
            $reduction->night_number = intval($nightNumber);
            $reduction->value = floatval($values[$index]);
            $reduction->housing_id = $housing->id;
            $reduction->is_encours = true;
            $reduction->save();
        }

        $data = ["housing_id" => $housing->id];
        $housing->step = 16;
        $housing->save();

        return (new ServiceController())->apiResponse(200, $data, 'Étape 16 terminée avec succès');

    } catch (\Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}

/**
 * @OA\Post(
 *     path="/api/logement/store_step_17/{housingId}",
 *     summary="Étape 17: Ajouter une promotion à un logement",
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
 *         required=false,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="promotion_date_debut",
 *                     description="Date de début de la promotion",
 *                     type="string",
 *                     format="date"
 *                 ),
 *                 @OA\Property(
 *                     property="promotion_date_fin",
 *                     description="Date de fin de la promotion",
 *                     type="string",
 *                     format="date"
 *                 ),
 *                 @OA\Property(
 *                     property="promotion_number_of_reservation",
 *                     description="Nombre de réservations pour la promotion",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="promotion_value",
 *                     description="Valeur de la promotion",
 *                     type="number"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Étape 17 terminée avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 description="Message de succès",
 *                 type="string"
 *             ),
 *             @OA\Property(
 *                 property="housing_id",
 *                 description="ID du logement",
 *                 type="integer"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Tous les champs de promotion doivent être présents ou absents",
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
 *         response=404,
 *         description="Logement non trouvé, Rôle d'admin non trouvé ou erreur de validation de la promotion",
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
 *         description="Erreur interne du serveur",
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




 public function addHousing_step_17(Request $request, $housingId)
 {
     try {
              $housing = Housing::find($housingId);
        if (!$housing) {
            return (new ServiceController())->apiResponse(404, [], 'Logement non trouvé.');
        }
       $errorcheckOwner= $this->checkOwner($housingId);
        if($errorcheckOwner){
            return $errorcheckOwner;
        }
        $validationResponse =$this->validateStepOrder(13, $housingId);
        if ($validationResponse) {
            return $validationResponse;
        }



         // Initialiser les messages d'erreur
         $messages = [];

         // Vérifier si un des champs de promotion est présent
         $promotionFields = ['promotion_date_debut', 'promotion_date_fin', 'promotion_number_of_reservation', 'promotion_value'];
         $promotionProvided = array_filter($promotionFields, fn($field) => $request->has($field));

         // Si un champ est présent, tous les autres doivent l'être aussi
         if (!empty($promotionProvided) && count($promotionProvided) !== count($promotionFields)) {
             return (new ServiceController())->apiResponse(400, ["err"=>$request], 'Tous les champs de promotion doivent être présents ou absents.');
         }

         // Valider les champs de promotion s'ils sont présents
         if (!empty($promotionProvided)) {
             $promotionDateDebut = $request->input('promotion_date_debut');
             $promotionDateFin = $request->input('promotion_date_fin');
             $promotionNumberOfReservation = $request->input('promotion_number_of_reservation');
             $promotionValue = $request->input('promotion_value');

             if (!strtotime($promotionDateDebut)) {
                 return (new ServiceController())->apiResponse(404, ["err"=>$request], 'La date de début de la promotion doit être une date valide.');
             }
             if (!strtotime($promotionDateFin)) {
                 return (new ServiceController())->apiResponse(404, ["err"=>$request], 'La date de fin de la promotion doit être une date valide.');
             }
             if (strtotime($promotionDateFin) < strtotime($promotionDateDebut)) {
                 return (new ServiceController())->apiResponse(404, ["err"=>$request], 'La date de fin de la promotion doit être après ou égale à la date de début.');
             }
             if (intval($promotionNumberOfReservation)<= 0) {
                 return (new ServiceController())->apiResponse(404, ["err"=>$request], 'Le nombre de réservations doit être un entier supérieur à zéro.');
             }
             if (floatval($promotionValue) <= 0) {
                 return (new ServiceController())->apiResponse(404, ["err"=>$request], 'La valeur de la promotion doit être un nombre non négatif et non nul.');
             }

             $dateToday = new DateTime();

             $debutDate = new DateTime($request->promotion_date_debut);
             if ($debutDate->format('Y-m-d') < $dateToday->format('Y-m-d')) {
                return (new ServiceController())->apiResponse(404, [], 'La date de début est déjà passée.');
            }

             // Supprimer les promotions existantes pour ce logement
             Promotion::where('housing_id', $housingId)->delete();
             $dateToday = new DateTime();
            $debutDate = new DateTime($request->promotion_date_debut);
            $isEncours = $debutDate->format('Y-m-d') === $dateToday->format('Y-m-d');

             // Ajouter la nouvelle promotion
             $promotion = new Promotion();
             $promotion->date_debut = $promotionDateDebut;
             $promotion->date_fin = $promotionDateFin;
             $promotion->number_of_reservation = intval($promotionNumberOfReservation);
             $promotion->value = floatval($promotionValue);
             $promotion->housing_id = $housing->id;
             $promotion->is_encours = $isEncours;
             $promotion->save();
         }

         // Mettre à jour le logement à l'étape 17
         $housing->step = 17;
         $housing->is_updated=0;
        $housing->is_actif=1;
        $housing->is_destroy=0;
        $housing->is_finished=1;
         $housing->save();

         // Notifications
         $userId = auth()->user()->id;
         $notificationName = "Félicitation! Vous venez d'ajouter un nouveau logement sur la plateforme. Le logement ne sera visible sur le site qu'après validation de l'administrateur.";

        //  $notification = new Notification([
        //      'name' => $notificationName,
        //      'user_id' => $userId,
        //  ]);
        //  $notification->save();

         $mail = [
             'title' => "Ajout d'un logement",
             'body' => "Félicitation! Vous venez d'ajouter un nouveau logement sur la plateforme. Le logement ne sera visible sur le site qu'après validation de l'administrateur."
         ];


         dispatch( new SendRegistrationEmail(Auth::user()->email, $mail['body'], $mail['title'], 2));


         $adminRole = DB::table('rights')->where('name', 'admin')->first();

         if (!$adminRole) {
             return (new ServiceController())->apiResponse(404, [], 'Le rôle d\'admin n\'a pas été trouvé.');
         }

         $adminUsers = User::whereHas('user_right', function ($query) use ($adminRole) {
             $query->where('right_id', $adminRole->id);
         })->get();

         foreach ($adminUsers as $adminUser) {

             $mail = [
                 'title' => "Notification d'ajout d'un logement",
                 'body' => "Un nouveau logement vient d'être ajouté sur le site par un hôte."
             ];

            dispatch( new SendRegistrationEmail($adminUser->email, $mail['body'], $mail['title'], 2));
         }

         return (new ServiceController())->apiResponse(200, ["housing_id" => $housing->id], 'Étape 17 terminée avec succès');

     } catch (\Exception $e) {
         return (new ServiceController())->apiResponse(500, [], $e->getMessage());
     }
 }



 public function validateStepOrder($currentStep, $housingId)
    {
        $housing = Housing::find($housingId);

        if($housing->step != 17){
            $housing->is_finished = 0;
            $housing->save();
        }


        // if ($housing->step < $currentStep - 1) {
        //     return (new ServiceController())->apiResponse(404, [], 'Vous devez compléter l\'étape ' . ($currentStep - 1) . ' avant de passer à l\'étape ' . $currentStep . '.');
        // }

        return null;
    }

   /**
 * @OA\Post(
 *     path="/api/logement/store_step_8/{housingId}",
 *     summary="Add or update housing step 8",
 *   security={
 *         {"bearerAuth": {}}
 *     },
 *     description="Add or update housing information for step 8 including categories and pieces.",
 *     operationId="addHousingStep8",
 *     tags={"Ajout de logement"},
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         description="ID of the housing to update",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="categories",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(
 *                             property="id",
 *                             type="integer"
 *                         ),
 *                         @OA\Property(
 *                             property="name",
 *                             type="string"
 *                         ),
 *                         @OA\Property(
 *                             property="equipments",
 *                             type="array",
 *                             @OA\Items(
 *                                 type="object",
 *                                 @OA\Property(
 *                                     property="equipments->equipmentsId",
 *                                     type="array",
 *                                     @OA\Items(type="integer")
 *                                 ),
 *                                 @OA\Property(
 *                                     property="equipments->newEquipementName",
 *                                     type="string"
 *                                 )
 *                             )
 *                         ),
 *                         @OA\Property(
 *                             property="photos",
 *                             type="array",
 *                             @OA\Items(
 *                                 type="string",
 *                                 format="binary"
 *                             )
 *                         )
 *                     )
 *                 ),
 *                 @OA\Property(
 *                     property="pieces",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(
 *                             property="id",
 *                             type="integer"
 *                         ),
 *                         @OA\Property(
 *                             property="name",
 *                             type="string"
 *                         ),
 *                         @OA\Property(
 *                             property="nomnbre",
 *                             type="integer",
 *                             description="Number of pieces or quantity (use `quantity` if applicable)"
 *                         ),
 *                         @OA\Property(
 *                             property="quantity",
 *                             type="integer",
 *                             description="Number of pieces or quantity (use `nomnbre` if applicable)"
 *                         ),
 *                         @OA\Property(
 *                             property="photos",
 *                             type="array",
 *                             @OA\Items(
 *                                 type="string",
 *                                 format="binary"
 *                             )
 *                         )
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Step 8 completed successfully",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="housing_id",
 *                 type="integer"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Resource not found",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="error",
 *                 type="string"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="error",
 *                 type="string"
 *             )
 *         )
 *     )
 * )
 */
public function addHousing_step_8(Request $request, $housingId){

    try {


        $housing = Housing::whereId($housingId)->first();
        if (!$housing) {
            return (new ServiceController())->apiResponse(404, [], 'Logement non trouvé');
        }
        
         

        $errorcheckOwner = $this->checkOwner($housingId);

        if ($errorcheckOwner) {
            return $errorcheckOwner;
        }
        $validationResponse = $this->validateStepOrder(8, $housingId);
        if ($validationResponse) {
            return $validationResponse;
        }

        $this->deleteHousingData($housingId);
        $storedCategories = [];
        $storedPieces = [];
        // Validation des catégories
        foreach ($request->categories as $categorie) {
            // $categorie = json_decode($categorieA, true);
            // return $categorie['id'];
            if (!Category::find($categorie['id'])) {
                return (new ServiceController())->apiResponse(404, [], 'Pièce non trouvée');
            }

            if (!isset($categorie['nombre']) || !$categorie['nombre']) {
                return (new ServiceController())->apiResponse(404, [], "Nombre de la pièce " . Category::whereId($categorie['id'])->first()->name . " non renseigné");
            }
            $categorie['nombre']=intval ($categorie['nombre']);                 
            if (!is_int($categorie['nombre'])) {
                return (new ServiceController())->apiResponse(404, [], "Le nombre de la pièce " . Category::whereId($categorie['id'])->first()->name . " doit être un entier");
            }
            
            // return  count($categorie['equipments'][0]['equipmentsId']);

            if (!$categorie['equipments'][0]['equipmentsId'] || count($categorie['equipments'][0]['equipmentsId']) == 0) {
                return (new ServiceController())->apiResponse(404, [], "Renseigner au moins un équipement s'il vous plaît pour la pièce ".Category::find($categorie['id'])->name);
            }

            $items = $categorie['equipments'][0]['equipmentsId'];
            $uniqueItems = array_unique($items);

            if (count($uniqueItems) < count($items)) {
                return (new ServiceController())->apiResponse(404, [], "Vous ne pouvez pas ajouter deux équipements existants avec le même id.");
            }
            
            foreach ($categorie['equipments'][0]['equipmentsId'] as $equipmentId) {
                $equipmentId=intval($equipmentId);  
                if (!is_int($equipmentId)) {
                    return (new ServiceController())->apiResponse(404, [], "Les équipements doivent être des entiers, cela concerne le logement ".Category::find($categorie['id'])->name);
                }

                $equipment = Equipment::find($equipmentId);
                if (!$equipment) {
                    return (new ServiceController())->apiResponse(404, [], 'Équipement non trouvé pour le logement '.Category::find($categorie['id'])->name);
                }

                $EquipmentCategorieExists = Equipment_category::where('equipment_id', $equipmentId)
                    ->where('category_id', $categorie['id'])->exists();

                if (!$EquipmentCategorieExists) {
                    return (new ServiceController())->apiResponse(404, [], "Revoyez les id de catégorie et équipement que vous renvoyez. L'équipement ".Equipment::find($equipmentId)->name ." n'est pas associé à la pièce " . Category::find($categorie['id'])->name);
                }
            }

            if (isset($categorie['equipments'][0]['newEquipementName']) && count($categorie['equipments'][0]['newEquipementName']) > 0) {
                $items = $categorie['equipments'][0]['newEquipementName'];
                $uniqueItems = array_unique($items);

                if (count($uniqueItems) < count($items)) {
                    return (new ServiceController())->apiResponse(404, [], "Vous ne pouvez pas ajouter deux nouveaux équipements avec le même nom à la pièce ". Category::find($categorie['id'])->name);
                }

                foreach ($categorie['equipments'][0]['newEquipementName'] as $equipmentName) {
                    $EquipmentExists = Equipment::whereName($equipmentName)->exists();
                    if ($EquipmentExists) {
                        return (new ServiceController())->apiResponse(404, [], "Un autre équipement ayant le même nom existe déjà dans la base de données ou a été exclu. $equipmentName existe déjà comme nom d'équipement");
                    }
                }
            }

            if (!isset($categorie['photos']) || count($categorie['photos']) == 0) {
                return (new ServiceController())->apiResponse(404, [], "Veuillez ajouter des photos à la pièce " . Category::whereId($categorie['id'])->first()->name);
            }
        }

        // Validation des pièces
        if ($request->has('pieces')) {

            $nouvellePieces = $request->pieces;

            $names = [];
            foreach ($nouvellePieces as $piece) {
                // return $piece;
                // $piece = json_decode($piece, true);

                $name = $piece['name'];
                if (in_array($name, $names)) {
                    return (new ServiceController())->apiResponse(404, [], "Le nom de la pièce $name est dupliqué.");
                } else {
                    $names[] = $name;
                }
            }

            foreach ($nouvellePieces as $piece) {
                // $piece = json_decode($piece, true);

                if (Category::whereName($piece['name'])->exists()) {
                    return (new ServiceController())->apiResponse(404, [], "Une autre catégorie ayant le même nom existe déjà dans la base de données ou a été exclue. " . $piece['name'] . " existe déjà comme nom de pièce");
                }

                if (!isset($piece['nombre']) || !$piece['nombre']) {
                    return (new ServiceController())->apiResponse(404, [], "Nombre de la pièce " . $piece['name'] . " non renseigné");
                }
                $piece['nombre']=intval ($piece['nombre']);                 

                if (!is_int($piece['nombre'])) {
                    return (new ServiceController())->apiResponse(404, [], "Le nombre de  pièce " . $piece['name'] . " doit être un entier");
                }

                if (!isset($piece['equipments']['equipmentsId']) || count($piece['equipments']['equipmentsId']) == 0) {
                    return (new ServiceController())->apiResponse(404, [], "Renseigner au moins un équipement s'il vous plaît à la pièce ". $piece['name'] );
                }

                $items = $piece['equipments']['equipmentsId'];
                $uniqueItems = array_unique($items);

                if (count($uniqueItems) < count($items)) {
                    return (new ServiceController())->apiResponse(404, [], "Vous ne pouvez pas ajouter deux équipements existants avec le même id à la pièce ".$piece['name'] );
                }
                $piece['nombre']=intval ($piece['nombre']);                 

                foreach ($piece['equipments']['equipmentsId'] as $equipmentId) {
                    $equipmentId=intval ($equipmentId);    
                    if (!is_int($equipmentId)) {
                        return (new ServiceController())->apiResponse(404, [], "Les ids équipements de la pièce ".$piece['name']." doivent être des entiers");
                    }

                    $equipment = Equipment::find($equipmentId);
                    if (!$equipment) {
                        return (new ServiceController())->apiResponse(404, [], ' Un équipement non trouvé pour la pièce '. $piece['name']);
                    }
                }

                if (!isset($piece['photos']) || count($piece['photos']) == 0) {
                    return (new ServiceController())->apiResponse(404, [], "Veuillez ajouter des photos à la pièce " . $piece['name']);
                }
            }
        }

        // Mise à jour des éléments pour une catégorie existante
       

        foreach ($request->categories as $categorie) {
                
            // $categorie = json_decode($categorieA, true);
            $categorieModel = Category::find($categorie['id']);
            // Mise à jour pour les équipements existants
            foreach ($categorie['equipments'][0]['equipmentsId'] as $equipmentId) {
                $equipment = Equipment::find($equipmentId);
                if ($equipment) {
                    $housingEquipment = new Housing_equipment();
                    $housingEquipment->equipment_id = $equipmentId;
                    $housingEquipment->category_id = $categorie['id'];
                    $housingEquipment->housing_id = $housing->id;
                    $housingEquipment->is_verified = true;
                    $housingEquipment->save();
                    $equipmentIds[] = $equipmentId;
                }
            }

            // Mise à jour pour les équipements inexistants
            
            if (isset($categorie['equipments'][0]['newEquipementName'])) {
            foreach ($categorie['equipments'][0]['newEquipementName'] as $newEquipment) {
               
                // return $newEquipment;
                $equipment = Equipment::whereName($newEquipment)->first();
                if (!$equipment) {
                    $equipment = new Equipment();
                    $equipment->name = $newEquipment;
                    $equipment->is_verified = false;
                    $equipment->save();
                    
                }
                $equipmentCategory = new Equipment_category();
                $equipmentCategory->equipment_id = $equipment->id;
                $equipmentCategory->category_id = $categorie['id'];
                $equipmentCategory->save();

                $housingEquipment = new Housing_equipment();
                $housingEquipment->equipment_id = $equipment->id;
                $housingEquipment->category_id = $categorie['id'];
                $housingEquipment->housing_id = $housing->id;
                $housingEquipment->is_verified = false;
                $housingEquipment->save();
            }
            
            // Enregistrement des photos
            foreach ($categorie['photos'] as $fileId) {
                
                $photoModel = new File();
                $uploadedPath = $this->fileService->uploadFiles($fileId, 'image/photo_category');

               /* $photoName = uniqid() . '.' . $fileId->getClientOriginalExtension()  ;
                $photoPath = $fileId->move(public_path('image/photo_category'), $photoName);
                $photoUrl = (env('MODE') == 'PRODUCTION') ? url('/image/photo_logement/' . $photoName) : env('LOCAL_ADDRESS') . '/image/photo_logement/' . $photoName;
                */

                $photoModel->path = $uploadedPath;
                $photoModel->save();
                $housingCategoryFile = new Housing_category_file();
                $housingCategoryFile->housing_id = $housing->id;
                $housingCategoryFile->category_id = $categorie['id'];
                $housingCategoryFile->file_id = $photoModel->id;
                $housingCategoryFile->number = count($categorie['photos']);
                $housingCategoryFile->is_verified = true;
                $housingCategoryFile->save();
                

            }
           
        }
       }
        
        // Mise à jour des éléments pour une catégorie inexistante
        foreach ($request->pieces as $piece) {
            // $piece = json_decode($pieceA, true);
            $existCategorie = Category::whereName($piece['name'])->first();

            if (!$existCategorie) {
                $newcategory = new Category();
                $newcategory->name = $piece['name'];
                $newcategory->is_verified = false;
                $newcategory->save();
            } else {
                $newcategory = $existCategorie;
            }
            

            // Mise à jour pour les équipements existants
            foreach ($piece['equipments']['equipmentsId'] as $equipmentId) {
                $equipment = Equipment::find($equipmentId);
                if ($equipment) {
                    $housingEquipment = new Housing_equipment();
                    $housingEquipment->equipment_id = $equipmentId;
                    $housingEquipment->category_id = $newcategory->id;
                    $housingEquipment->housing_id = $housing->id;
                    $housingEquipment->is_verified = true;
                    $housingEquipment->save();
                }
            }

            // Enregistrement des photos
            foreach ($piece['photos'] as $fileId) {
                $photoModel = new File();
                $uploadedPath = $this->fileService->uploadFiles($fileId, 'image/photo_category');
/*
                $photoName = uniqid() . '.' . $fileId->getClientOriginalExtension();
                $photoPath = $fileId->move(public_path('image/photo_category'), $photoName);
                $photoUrl = (env('MODE') == 'PRODUCTION') ? url('/image/photo_logement/' . $photoName) : env('LOCAL_ADDRESS') . '/image/photo_logement/' . $photoName;
*/

                $photoModel->path = $uploadedPath;
                $photoModel->save();

                $housingCategoryFile = new Housing_category_file();
                $housingCategoryFile->housing_id = $housing->id;
                $housingCategoryFile->category_id = $newcategory->id;
                $housingCategoryFile->file_id = $photoModel->id;
                $housingCategoryFile->number = count($piece['photos']);
                $housingCategoryFile->is_verified = true;
                $housingCategoryFile->save();
            }
        }
        $housing->step = 8;
        $housing->save();
        //$stocke=  (new AdminHousingController())->showHousingDetailForValidationForadmin($housing->id);    

        $data = ["housing_id" => $housing->id];

        return (new ServiceController())->apiResponse(200, $data, 'Étape 8 terminée avec succès');

    } catch (\Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}


}


