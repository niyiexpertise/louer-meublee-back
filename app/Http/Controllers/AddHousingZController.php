<?php

namespace App\Http\Controllers;
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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Exception;
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

/**
 * @OA\Post(
 *     path="/api/logement/store_step_8/{housingId}",
 *     summary="Ajouter des équipements et des catégories à un logement",
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
 *                 required={
 *                     "equipment_housing",
 *                     "category_equipment_housing",
 *                     "category_id",
 *                     "number_category"
 *                 },
 *                 @OA\Property(
 *                     property="equipment_housing",
 *                     description="Liste des ID d'équipements existants pour le logement",
 *                     type="array",
 *                     @OA\Items(type="integer")
 *                 ),
 *                 @OA\Property(
 *                     property="category_equipment_housing",
 *                     description="Liste des ID de catégories associées aux équipements",
 *                     type="array",
 *                     @OA\Items(type="integer")
 *                 ),
 *                 @OA\Property(
 *                     property="category_id",
 *                     description="Liste des ID de catégories existantes pour le logement",
 *                     type="array",
 *                     @OA\Items(type="integer")
 *                 ),
 *                 @OA\Property(
 *                     property="number_category",
 *                     description="Liste des nombres associés à chaque catégorie",
 *                     type="array",
 *                     @OA\Items(type="integer")
 *                 ),
 *                 @OA\Property(
 *                     property="new_categories",
 *                     description="Liste des nouvelles catégories à ajouter",
 *                     type="array",
 *                     nullable=true,
 *                     @OA\Items(type="string")
 *                 ),
 *                 @OA\Property(
 *                     property="new_categories_numbers",
 *                     description="Liste des nombres associés aux nouvelles catégories",
 *                     type="array",
 *                     nullable=true,
 *                     @OA\Items(type="integer")
 *                 ),
 *                 @OA\Property(
 *                     property="new_equipment",
 *                     description="Liste des nouveaux équipements à ajouter",
 *                     type="array",
 *                     nullable=true,
 *                     @OA\Items(type="string")
 *                 ),
 *                 @OA\Property(
 *                     property="new_equipment_category",
 *                     description="Liste des catégories associées aux nouveaux équipements",
 *                     type="array",
 *                     nullable=true,
 *                     @OA\Items(type="integer")
 *                 ),
 *                 @OA\Property(
 *                     property="photo_categories{id}",
 *                     description="Photos des catégories existantes, où {id} est remplacé par l'ID de la catégorie",
 *                     type="array",
 *                     nullable=true,
 *                     @OA\Items(type="string", format="binary")
 *                 ),
 *                 @OA\Property(
 *                     property="new_category_photos_{name}",
 *                     description="Photos des nouvelles catégories, où {name} est remplacé par le nom de la nouvelle catégorie",
 *                     type="array",
 *                     nullable=true,
 *                     @OA\Items(type="string", format="binary")
 *                 )
 *             ),
 *             example={
 *                 "equipment_housing": {1, 2, 3},
 *                 "category_equipment_housing": {10, 20, 30},
 *                 "category_id": {100, 200},
 *                 "number_category": {2, 3},
 *                 "new_categories": {"Salle de jeux", "Bureau"},
 *                 "new_categories_numbers": {1, 1},
 *                 "new_equipment": {"Projecteur", "Scanner"},
 *                 "new_equipment_category": {100, 200},
 *                 "photo_categories100": {"cat100_photo1.jpg", "cat100_photo2.jpg"},
 *                 "new_category_photos_Salle_de_jeux": {"salle_photo1.jpg", "salle_photo2.jpg"}
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Étape 8 terminée avec succès",
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
 *         description="Données invalides ou logement non trouvé",
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
 *         description="Erreur de validation",
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


 public function  addHousing_step_8(Request $request,$housingId){
        try {

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

            $housing = Housing::whereId($housingId)->first();

            if(!$housing){
                return (new ServiceController())->apiResponse(404,[], 'Logement non trouvé');
            }
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
                return (new ServiceController())->apiResponse(404,[], "Renseigner au moin un une pièce svp");

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
                     return (new ServiceController())->apiResponse(404,[], "Vous ne pouvez ajouter deux nouvelles catégories avec le même nom.");
                }
               $category = Category::all();
               foreach($category as $e)
               {
                // return !($categoryName == $e->name && Category::whereName($categoryName)->first()->isverified == 1);
                if($categoryName == $e->name && Category::whereName($categoryName)->first()->is_verified == 1)
                    {
                    return (new ServiceController())->apiResponse(404,[], "Une autre catégorie ayant le même nom existe déjà dans la base de donnée ou a été exclu.$categoryName existe déjà comme nom de pièce");
                    }

                    // return !($categoryName == $e->name && Category::whereName($categoryName)->first()->isverified == 0);
                if($categoryName == $e->name && Category::whereName($categoryName)->first()->is_verified == 0)
                    {
                        $existCategoryFile = Housing_category_file::where('housing_id',$housingId)->where('category_id',Category::whereName($categoryName)->first()->id)->first();
                        if(File::whereId($existCategoryFile->file_id)->exits()){
                            File::whereId($existCategoryFile->file_id)->delete();
                        }
                       
                        $existCategoryFile->delete();
                        Category::whereName($categoryName)->delete();
                    }
               }
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
                    if($equipmentId == $e->name && Equipment::whereName($equipmentId)->first()->is_verified == 1){
                    return (new ServiceController())->apiResponse(404,[], "Un autre équipement ayant le même nom existe déjà dans la base de donnée ou a été exclu.$e->name existe déjà comme nom d'équiment");
                    }
                    
                    if($equipmentId == $e->name && Equipment::whereName($equipmentId)->first()->is_verified == 0){
                        Equipment::whereName($equipmentId)->delete();
                    }
                }
            }

            // $uniqueItems = array_unique($request->input('new_equipment'));
            // if (count($uniqueItems) < count($request->input('new_equipment'))) {
            //     return (new ServiceController())->apiResponse(404,[], "Le tableau contient des éléments dupliqués.");
            // }

            $errors = $this->validateUniqueEquipmentCategories($request->new_equipment, $request->new_equipment_category);

            if (!empty($errors)) {
                return (new ServiceController())->apiResponse(505,[],$errors);
            }


       }

       foreach(Housing_equipment::where('housing_id',$housingId)->get() as $existEquipment){
        $existEquipment->delete();
        }

       $fileID= [];
       foreach(Housing_category_file::where('housing_id',$housingId)->get() as $existEquipment){
        //    return 1;
            $fileID[] = $existEquipment->file_id;

            foreach ($fileID as $file){
                File::whereId($file)->delete();
            }
        }

        // return $fileID;

       foreach(Housing_category_file::where('housing_id',$housingId)->get() as $existEquipment){
        $existEquipment->delete();
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
            } else {
          }
        }
    }
    // Debut de Mise à jours pour les équipements inexistants associés aux pièces pour un logement donné
    if ($request->has('new_equipment') && $request->has('new_equipment_category')) {
        $newEquipments = $request->input('new_equipment');
        $newEquipmentCategories = $request->input('new_equipment_category');

        foreach ($newEquipments as $index => $newEquipment) {
            if(Equipment::whereName($newEquipment)->exists()){
                return (new ServiceController())->apiResponse(404,[], "Vous ne pouvez pas ajoutez deux nouveaux équipements avec le même nom");
            }
            $equipment = new Equipment();
            $equipment->name = $newEquipment;
            $equipment->is_verified=false;
            $equipment->save();


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
            foreach ($photoFiles as $fileId) {
                $photoModel = new File();
                $photoName = uniqid() . '.' . $fileId->getClientOriginalExtension();
                $photoPath = $fileId->move(public_path('image/photo_category'), $photoName);
                if(env('MODE') == 'PRODUCTION'){
                    $photoUrl = url('/image/photo_logement/' . $photoName);
                }
                if(env('MODE') == 'DEVELOPPEMENT'){
                    $ip=env('LOCAL_ADDRESS');
                    $photoUrl = $ip.'/image/photo_logement/' . $photoName;
                }
                $photoModel->path = $photoUrl;
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
            $photoName = uniqid() . '.' . $photoFile->getClientOriginalExtension();
            $photoPath = $photoFile->move(public_path('image/photo_category'), $photoName);
            if(env('MODE') == 'PRODUCTION')
            {
                    $photoUrl = url('/image/photo_logement/' . $photoName);
            }
            if(env('MODE') == 'DEVELOPPEMENT'){
                    $ip=env('LOCAL_ADDRESS');
                    $photoUrl = $ip.'/image/photo_logement/' . $photoName;
            }
            $photo = new File();
            $photo->path = $photoUrl;
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

        } catch(\Exception $e) {
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


}
