<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationEmail;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use App\Models\Charge;
use App\Models\Housing;
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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File as F;
use App\Models\Category;
use App\Models\Housing_charge;
use App\Models\Review_reservation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmail;
use App\Mail\NotificationEmailwithoutfile;
use App\Models\Favoris;
use App\Models\Setting;
use App\Models\UserVisiteHousing;
use App\Services\FileService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class HousingController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService = null)
    {
        $this->fileService = $fileService ?: new FileServiceController();
    }

 public function addHousing(Request $request)
 {
        $userId = Auth::id();
     if($request->has('housing_id')){
        $housingId = $request->housing_id;
        $exist = Housing::where('id',$housingId)->first();
        $housing = Housing::where('id', $housingId )->get();

        if($housing->isEmpty()) {
            return response()->json(['message' => " L\'ID du logement(housing_id) spécifié n\'existe pas"], 404);
        }
        $exist->delete();
    }

     $validator = Validator::make($request->all(), [
        'housing_id' => 'nullable',
        'housing_type_id' => 'required|integer|exists:housing_types,id',
        'property_type_id' => 'required|integer|exists:property_types,id',
        'name' => 'required|string|max:255|unique:housings',
        'description' => 'required|string',
        'number_of_bed' => 'required|integer|min:1',
        'number_of_traveller' => 'required|integer|min:1',
        'sit_geo_lat' => 'required|numeric',
        'sit_geo_lng' => 'required|numeric',
        'country' => 'required|string',
        'address' => 'required|string',
        'city' => 'required|string',
        'department' => 'required|string',
        'is_camera' => 'required|boolean',
        'is_accepted_animal' => 'required|boolean',
        'is_animal_exist' => 'required|boolean',
        'interior_regulation' => 'required|string',
        'telephone' => 'required|string|max:20',
        'code_pays' => 'required|string|max:5',
        'arrived_independently' => 'required|string',
        'is_instant_reservation' => 'required|boolean',
        'minimum_duration' => 'required|integer|min:1',
        'time_before_reservation' => 'required|integer|min:0',
        'cancelation_condition' => 'required|string',
        'departure_instruction' => 'required|string',
        'surface' => 'required|numeric|min:1',
        'price' => 'required|numeric|min:1',
        'is_accept_arm' => 'required|boolean',
        'is_accept_smoking' => 'required|boolean',
        'is_accept_chill' => 'required|boolean',
        'is_accept_noise' => 'required|boolean',
        'is_accept_alccol' => 'required|boolean',
        'is_accept_anulation' => 'required|boolean',
        'profile_photo_id' => 'nullable|integer',
        'photos' => 'nullable|array',
        'photos.*' => 'file|image|max:2048',
        'preferences' => 'nullable|array',
        'preferences.*' => 'integer',
        'Hotecharges' => 'nullable|array',
        'Hotecharges.*' => 'integer',
        'Travelercharges' => 'nullable|array',
        'Travelercharges.*' => 'integer',
        'Travelerchargesvalue' => 'nullable|array',
        'Travelerchargesvalue.*' => 'numeric|min:0',
        'reduction_night_number' => 'nullable|array',
        'reduction_date_debut' => 'nullable|array',
        'reduction_date_fin' => 'nullable|array',
        'reduction_value_night_number' => 'nullable|array',
        'promotion_date_debut' => 'nullable|date',
        'promotion_date_fin' => 'nullable|date',
        'promotion_number_of_reservation' => 'nullable|integer|min:1',
        'promotion_value' => 'nullable|numeric|min:0',
        'equipment_housing' => 'nullable|array',
        'equipment_housing.*' => 'integer',
        'category_equipment_housing' => 'nullable|array',
        'category_equipment_housing.*' => 'integer',
        'category_id' => 'nullable|array',
        'category_id.*' => 'integer',
        'number_category' => 'nullable|array',
        'photo_categories.*' => 'nullable|file|image|max:2048',
        'new_equipment' => 'nullable|array',
        'new_equipment.*' => 'string',
        'new_equipment_category' => 'nullable|array',
        'new_equipment_category.*' => 'integer',
        'new_categories' => 'nullable|array',
        'new_categories_numbers' => 'nullable|array',
      'new_category_photos_.*' => 'nullable|array',
        'new_category_photos_.*.*' => 'file|image|max:2048',
    ]);




    if ($validator->fails()) {
        return response()->json(['message' => 'Les données fournies ne sont pas valides.', 'errors' => $validator->errors()], 200);
    }

    if ($request->has('Hotecharges')) {

        if($request->has('Hotechargesvalue')){

                    foreach ($request->Hotecharges as $HotechargesId) {
                        $HotechargesExists = Charge::where('id', $HotechargesId)->exists();

                        if (!$HotechargesExists) {
                            return response()->json(['message' => 'Revoyez les id de charges que vous renvoyez;précisement la variable HoteCharge.'], 200);
                            }

                    }

               } else{
                    return response()->json(['message' => 'Renseigner svp les valeurs de chaque charge. si elle ne sont renseigné,mettez comme valeur 0 pour chacun(Indicatif pour font end).'], 200);
                 }

       }
       //debut validation traveler charge

       if ($request->has('Travelercharges')) {

        if($request->has('Travelerchargesvalue')){
                   if (count($request->input('Travelercharges')) == count($request->input('Travelerchargesvalue')) ) {
                    foreach ($request->Travelercharges as $TravelerchargesId) {
                        $TravelerchargesExists = Charge::where('id', $TravelerchargesId)->exists();

                        if (!$TravelerchargesExists) {
                            return response()->json(['message' => 'Revoyez les id de charges que vous renvoyez;précisement la variable TravelerCharge.'], 200);
                            }

                    }

                                 }   else{
                                                return response()->json(['message' => 'Le nombre de valeurs de charges Traveler ne correspond pas au nombre de charges.'], 200);
                                        }
               } else{
                    return response()->json(['message' => 'Renseigner svp les valeurs de chaque charge. si elle ne sont renseigné,mettez comme valeur 0 pour chacun(Indicatif pour font end).'], 200);
                 }

       }
        //endvalidation
        //validation equipment_housing

        // if ($request->has('category_equipment_housing') && $request->has('equipment_housing') && count($request->input('category_equipment_housing')) !== count($request->input('equipment_housing'))) {
        //     return response()->json(['message' => 'La taille de la variable <<category_equipment_housing>> doit être égale à la taille de <<equipment_housing>>'], 200);
        // }

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
        //endvalidation





    //validation new equipment
    // if ($request->has('new_equipment') && $request->has('new_equipment_category') && count($request->input('new_equipment')) !== count($request->input('new_equipment_category'))) {
    //     return response()->json(['message' => 'La taille de la variable <<new_equipment>> doit être égale à la taille de <<new_equipment_category>>'], 200);
    // }

    if ($request->has('new_equipment')) {

        if($request->has('new_equipment_category')){
                   if (count($request->input('new_equipment')) == count($request->input('new_equipment_category')) ) {
                    foreach ($request->new_equipment as $index=> $equipmentId) {
                        $CategorieExists = Category::where('id', $request->input('new_equipment_category')[$index])
                                        ->exists();

                        if (!$CategorieExists) {
                            return response()->json(['message' => "Revoyez les id de catégorie que vous renvoyez.La catégorie". $request->new_equipment_category[$index]." n existe pas"], 200);
                            }
                            $equipments = Equipment::where('is_deleted',false)->where('is_blocked',false)->get();
                            foreach($equipments as $e){
                                if($equipmentId == $e->name){
                                    return response()->json(['message' => "Un autre equipement ayant le même nom existe déjà dans la base de donnée"], 200);

                                }
                            }

                    }

                                 }   else{
                                                return response()->json(['message' => 'La taille de la variable <<new_equipment>> doit être égale à la taille de <<new_equipment_category>>'], 200);
                                        }
               } else{
                    return response()->json(['message' => 'Renseigner les catégories des nouveaux équipements s il vous plaît).'], 200);
                 }

       }

    //endvalidation
    //validation reduction
    // if ($request->has('reduction_night_number') && (count($request->input('reduction_night_number')) !== count($request->input('reduction_date_debut')) ||
    //     count($request->input('reduction_date_debut')) !== count($request->input('reduction_date_fin')) ||
    //     count($request->input('reduction_date_fin')) !== count($request->input('reduction_value_night_number')))) {
    //     return response()->json(['message' => 'Les données de réduction sont incohérentes.'], 200);
    // }

    if ($request->has('reduction_night_number')) {

        if($request->has('reduction_value_night_number') && $request->has('reduction_date_debut') && $request->has('reduction_date_fin')){
                   if (count($request->input('reduction_night_number')) == count($request->input('reduction_value_night_number'))) {


                                 }   else{
                                 return response()->json(['message' => 'La taille de la variable <<reduction_night_number>> doit être égale à la taille de <<reduction_value_night_number>>'], 200);
                        }
                        if (count($request->input('reduction_night_number')) == count($request->input('reduction_date_debut'))) {


                        }   else{
                        return response()->json(['message' => 'La taille de la variable <<reduction_night_number>> doit être égale à la taille de <<reduction_date_debut>>'], 200);
               }
               if (count($request->input('reduction_night_number')) == count($request->input('reduction_date_fin'))) {


               }   else{
               return response()->json(['message' => 'La taille de la variable <<reduction_night_number>> doit être égale à la taille de <<reduction_date_fin>>'], 200);
      }
               } else{
                    return response()->json(['message' => 'Renseigner les valeurs ou les dates début ou les dates de fin de la réduction s il vous plaît).'], 200);
                 }

       }

    //endvalidation

    //validation promotion


    if ($request->has('promotion_number_of_reservation')) {

        if($request->has('promotion_value')){

               } else{
                    return response()->json(['message' => 'Renseigner la valeur de la promotion.'], 200);
        }

        if($request->has('promotion_date_fin')){

        } else{
                            return response()->json(['message' => 'Renseigner la date de fin de la promotion.'], 200);
                }

           if($request->has('promotion_date_debut')){

           } else{
         return response()->json(['message' => 'Renseigner la date de fin de la promotion.'], 200);
           }

       }

        //endpromotion
          //validation categorie

        //   if ($request->has('category_id') && $request->has('number_category') && count($request->input('category_id')) !== count(($request->input('number_category'))) ) {
        //     return response()->json([
        //         'message' => 'Le nombre de catégories ne correspond pas au nombre d\'entrées du tableau "number_category".'
        //     ], 200);
        if ($request->has('category_id')) {

            if($request->has('number_category')){
                       if (count($request->input('category_id')) == count($request->input('number_category'))) {


                                     }   else{
                                     return response()->json(['message' => 'La taille de la variable <<category_id>> doit être égale à la taille de <<number_category>>'], 200);
                            }
                            foreach($request->input('category_id') as $categoryId){
                                $existCategorie = Category::whereId($categoryId)->first();
                                if(!$existCategorie){
                                    return response()->json(['message' => "La categorie ayant pour id $categoryId n'existe pas"], 200);
                                }

                            }

                   } else{
                        return response()->json(['message' => 'Renseigner les valeurs de number_category.'], 200);
                     }

                     foreach ($request->input('category_id') as $index => $categoryId) {
                        $photoCategoryKey = 'photo_categories' . $categoryId;
                        if (!$request->hasFile($photoCategoryKey)) {
                            return response()->json([
                                'message' => "Aucune photo trouvée pour la catégorie $categoryId."
                            ], 200);
                        }
                        // return(($request->file("photo_categories1")));
                        if(count($request->file($photoCategoryKey)) == 0){
                                    return response()->json([
                                        'message' => " Il doit y avoir au moins une photo pour la catégorie "
                                    ], 200);
                        }
                      }

           }


    //endvalidation
    //validation new category
    if ($request->has('new_categories') && $request->has('new_categories_numbers') && count($request->input('new_categories')) !== count(($request->input('new_categories_numbers'))) ) {
        return response()->json([
            'message' => 'Le nombre de catégories ne correspond pas au nombre d\'entrées du tableau "new_categories_numbers".'
        ], 200);
    }

    if ($request->has('new_categories')) {

        if($request->has('new_categories_numbers')){
                   if (count($request->input('new_categories')) == count($request->input('new_categories_numbers'))) {

                                 }   else{
                                 return response()->json(['message' => 'La taille de la variable <<new_categories>> doit être égale à la taille de <<new_categories_numbers>>'], 200);
                        }
                        foreach($request->input('new_categories') as $categoryName){
                            $category = Category::where('is_deleted',false)->where('is_blocked',false)->get();
                            foreach($category as $e){
                                if($categoryName == $e->name){
                                    return response()->json(['message' => "Une autre catégorie ayant le même nom existe déjà dans la base de donnée"], 200);

                                }

                        }}

               } else{
                    return response()->json(['message' => 'Renseigner les valeurs de new_categories_numbers.'], 200);
                 }




        foreach ($request->input('new_categories') as $index => $new_categoriesName) {
            $photoCategoryKey = 'new_category_photos_' . $new_categoriesName;
            if (!$request->hasFile($photoCategoryKey)) {
                return response()->json([
                    'message' => "Aucune photo trouvée pour la catégorie $new_categoriesName."
                ], 200);
            }
            if(count($request->file($photoCategoryKey)) == 0){
                return response()->json([
                    'message' => " Il doit y avoir au moins une photo pour la catégorie "
                ], 200);
    }
          }
    }

    //endvalidation



     $housing = new Housing();
     $housing->housing_type_id = $request->input('housing_type_id');
     $housing->property_type_id = $request->input('property_type_id');
     $housing->name = $request->input('name');
     $housing->description = $request->input('description');
     $housing->number_of_bed = $request->input('number_of_bed');
     $housing->number_of_traveller = $request->input('number_of_traveller');
     $housing->sit_geo_lat = $request->input('sit_geo_lat');
     $housing->sit_geo_lng = $request->input('sit_geo_lng');
     $housing->country = $request->input('country');
     $housing->address = $request->input('address');
     $housing->city = $request->input('city');
     $housing->department = $request->input('department');
     $housing->is_camera = $request->input('is_camera');
     $housing->is_accepted_animal = $request->input('is_accepted_animal');
     $housing->is_animal_exist = $request->input('is_animal_exist');
     $housing->interior_regulation = $request->input('interior_regulation');
     $housing->telephone = $request->input('telephone');
     $housing->code_pays = $request->input('code_pays');
     $housing->status ="Unverified";
     $housing->arrived_independently = $request->input('arrived_independently');
     $housing->is_instant_reservation = $request->input('is_instant_reservation');
     $housing->minimum_duration = $request->input('minimum_duration');
     $housing->time_before_reservation = $request->input('time_before_reservation');
     $housing->cancelation_condition = $request->input('cancelation_condition');
     $housing->departure_instruction = $request->input('departure_instruction');
     $housing->user_id = $userId;
     $housing->surface = $request->input('surface');
     $housing->price = $request->input('price');
     $housing->is_updated=0;
     $housing->is_actif=1;
     $housing->is_destroy=0;
     $housing->is_finished=1;
     $housing->is_accept_arm= $request->input('is_accept_arm');
     $housing->is_accept_smoking= $request->input('is_accept_smoking');
     $housing->is_accept_chill= $request->input('is_accept_chill');
     $housing->is_accept_noise= $request->input('is_accept_noise');
     $housing->is_accept_alccol= $request->input('is_accept_alccol');
     $housing->is_accept_anulation = $request->input('is_accept_anulation');
     if ( $request->input('is_accept_anulation')) {
        $housing->delai_partiel_remboursement = $request->input('delai_partiel_remboursement');
        $housing->delai_integral_remboursement = $request->input('delai_integral_remboursement');
        $housing->valeur_integral_remboursement = $request->input('valeur_integral_remboursement');
        $housing->valeur_partiel_remboursement = $request->input('valeur_partiel_remboursement');
    }
     $housing->save();

     if ($request->hasFile('photos')) {
        $identity_profil_url = '';
         foreach ($request->file('photos') as $index => $photo) {
            $identity_profil_url = $this->fileService->uploadFiles($photo, 'image/iconeCharge', 'extensionImageVideo');;
            if ($identity_profil_url['fails']) {
                return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
            }
             $ip='http://192.168.100.158:8000';
            // $photoUrl = $ip.'/image/photo_logement/' . $photoName;
             $type = $photo->getClientOriginalExtension();
             $photoModel = new photo();
             $photoModel->path = $identity_profil_url['result'];
             $photoModel->extension = $type;
             if ($index == $request->input('profile_photo_id')) {
                 $photoModel->is_couverture = true;
             }
             $photoModel->housing_id = $housing->id;;
             $photoModel->save();
         }
     }
     if ($request->has('preferences')) {
        foreach ($request->input('preferences') as $preference) {
            $housingPreference = new housing_preference();
            $housingPreference->housing_id = $housing->id;
            $housingPreference->preference_id = $preference;
            $housingPreference->is_verified = true;
            $housingPreference->save();
     }
    }
     if ($request->has('Hotecharges')) {
        foreach ($request->input('Hotecharges') as $index => $charge) {
            $housingCharge = new Housing_charge();
            $housingCharge->housing_id = $housing->id;
            $housingCharge->charge_id = $charge;
            $housingCharge->is_mycharge= true;

            $housingCharge->save();
        }
     }
     if ($request->has('Travelercharges')) {
        foreach ($request->input('Travelercharges') as $index => $charge) {
            $housingCharge = new Housing_charge();
            $housingCharge->housing_id = $housing->id;
            $housingCharge->charge_id = $charge;
            $housingCharge->is_mycharge= false;
            $housingCharge->valeur=$request->input('Travelerchargesvalue')[$index];
            $housingCharge->save();
        }
     }
     if ($request->has('reduction_night_number')) {
        foreach ($request->input('reduction_night_number') as $index => $nightNumber) {
            $reduction = new reduction();
            $reduction->night_number = $nightNumber;
            $reduction->date_debut =$request->input('reduction_date_debut')[$index];
            $reduction->date_fin = $request->input('reduction_date_fin')[$index];
            $reduction->value = $request->input('reduction_value_night_number')[$index];
            $reduction->housing_id = $housing->id;
            $reduction->is_encours = false;
        $reduction->save();
        }
    }
    if ($request->has('promotion_number_of_reservation')) {
        $promotion = new promotion();
        $promotion->date_debut =$request->input('promotion_date_debut');
        $promotion->date_fin = $request->input('promotion_date_fin');
        $promotion->number_of_reservation = $request->input('promotion_number_of_reservation');
        $promotion->value = $request->input('promotion_value');
        $promotion->housing_id = $housing->id;
        $promotion->is_encours = false;
        $promotion->save();
    }

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

    if ($request->has('new_equipment') && $request->has('new_equipment_category')) {
        $newEquipments = $request->input('new_equipment');
        $newEquipmentCategories = $request->input('new_equipment_category');

        foreach ($newEquipments as $index => $newEquipment) {
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
    if ($request->has('category_id')) {
    foreach ($request->input('category_id') as $index => $categoryId) {
        $housingCategoryId = $housing->id;
        $photoCategoryKey = 'photo_categories' . $categoryId;
        $photoFiles = $request->file($photoCategoryKey);

        $identity_profil_url = '';
        foreach ($photoFiles as $fileId) {
            $photoModel = new File();
            $identity_profil_url = $this->fileService->uploadFiles($fileId, 'image/photo_category', 'extensionImageVideo');;
            if ($identity_profil_url['fails']) {
                return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
            }
                        $ip='http://192.168.100.158:8000';
                 //$photoUrl =$ip.'/image/photo_category/' . $photoName;
            $photoModel->path = $identity_profil_url['result'];
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
    if($request->has('new_categories')&& $request->has('new_categories_numbers')) {
    $newCategories = $request->input('new_categories') ?? [];
    $newCategoriesNumbers = $request->input('new_categories_numbers') ?? [];

    foreach ($newCategories as $index => $newCategory) {
        $categoryName = $newCategory;
        $categoryNumber = $newCategoriesNumbers[$index];
        $photoCategoryKey = 'new_category_photos_' . $categoryName;
        $categoryPhotos = $request->file($photoCategoryKey) ?? [];
        $category = new Category();
        $category->name = $categoryName;
        $category->is_verified = false;
        $category->save();

        $identity_profil_url = '';
        foreach ($categoryPhotos as $photoFile) {
            $identity_profil_url = $this->fileService->uploadFiles($photoFile, 'image/photo_category', 'extensionImageVideo');;
            if ($identity_profil_url['fails']) {
                return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
            }
                        $ip='http://192.168.100.158:8000';
                        //$photoUrl =$ip.'/image/photo_category/' . $photoName;
            $photo = new File();
            $photo->path = $identity_profil_url['result'];
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

     $notificationName="Félicitation!Vous venez d'ajouter un nouveau logement sur la plateforme.Le logement ne sera visible sur le site qu'après validation de l'administrateur";

     $notification = new Notification([
        'name' => $notificationName,
        'user_id' => $userId,
       ]);
       $mail = [
        'title' => "Ajout d'un logement",
        'body' => "Félicitation! Vous venez d'ajouter un nouveau logement sur la plateforme.Le logement ne sera visible sur le site qu'aprés validation de l'administrateur."
       ];

       Mail::to(auth()->user()->email)->send(new NotificationEmailwithoutfile($mail));
     $adminRole = DB::table('rights')->where('name', 'admin')->first();

             if (!$adminRole) {
                 return response()->json(['message' => 'Le rôle d\'admin n\'a pas été trouvé.'], 404);
             }

     $adminUsers = User::whereHas('user_right', function ($query) use ($adminRole) {
        $query->where('right_id', $adminRole->id);
    })
    ->get();

    foreach ($adminUsers as $adminUser) {
        $notification = new Notification();
        $notification->user_id = $adminUser->id;
        $notification->name = "Un nouveau logement vient d'être ajouté sur le site par un hôte.";
        $notification->save();

        $mail = [
            'title' => "Notification d'ajout  d'un logement",
            'body' => "Un nouveau logement vient d'être ajouté sur le site par un hôte."
           ];

           Mail::to($adminUser->email)->send(new NotificationEmailwithoutfile($mail));

       }

     return response()->json(['message' => 'Logement ajoute avec succes'], 201);

}



 /**
 * @OA\Get(
 *   path="/api/logement/index/ListeDesPhotosLogementAcceuil/{id}",
 *   tags={"Housing"},
 *   summary="Liste des photos d'un logement",
 *   description="Récupère la liste des photos associées à un logement spécifié par son ID.",
 *   @OA\Parameter(
 *     name="id",
 *     in="path",
 *     required=true,
 *     description="ID du logement",
 *     @OA\Schema(type="integer")
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Liste des photos du logement récupérée avec succès",
 *     @OA\JsonContent(
 *       @OA\Property(property="data", type="object",
 *         @OA\Property(property="id_housing", type="integer", example="1"),
 *         @OA\Property(property="photos_logement", type="array",
 *           @OA\Items(
 *             @OA\Property(property="id_photo", type="integer", example="1"),
 *             @OA\Property(property="path", type="string", example="http://exemple.com/photos/1"),
 *             @OA\Property(property="extension", type="string", example="jpg"),
 *             @OA\Property(property="is_couverture", type="boolean", example=true)
 *           )
 *         ),
 *         @OA\Property(property="categories", type="array",
 *           @OA\Items(
 *             @OA\Property(property="category_id", type="integer", example="1"),
 *             @OA\Property(property="category_name", type="string", example="Catégorie"),
 *             @OA\Property(property="photos_category", type="array",
 *               @OA\Items(
 *                 @OA\Property(property="file_id", type="integer", example="1"),
 *                 @OA\Property(property="path", type="string", example="http://exemple.com/photos/1")
 *               )
 *             )
 *           )
 *         )
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Le logement spécifié n'existe pas",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Le logement spécifié n'existe pas")
 *     )
 *   )
 * )
 */
public function ListeDesPhotosLogementAcceuil($id)
{
    $listing = Housing::with([
        'photos',
        'housingCategoryFiles.file',
    ])->find($id);

    if (!$listing) {
        return response()->json(['message' => 'Logement non trouvé.'], 404);
    }
    $photo_general = [];
    $photo_logement=[];
    if ($listing->photos->isNotEmpty()) {
        foreach ($listing->photos as $photo) {
             if($photo->is_verified){

                $photo_id = uniqid();
                $photo_info = [
                    'photo_unique_id' => $photo_id,
                    'id_photo' => $photo->id,
                    'path' => url($photo->path),
                    'extension' => $photo->extension,
                    'is_couverture' => $photo->is_couverture,
                ];
                $photo_logement[] = $photo_info;
                   $photo_general[] = $photo_info;
             }

        }
    }

    $categories = [];


    if ($listing->housingCategoryFiles->isNotEmpty()) {
        $unverifiedCategoryFiles = $listing->housingCategoryFiles->filter(function ($file) {
            return $file->is_verified == 1;
        });

        $groupedCategoryFiles = $unverifiedCategoryFiles->groupBy('category.name');

        $photo_general = [];
        foreach ($groupedCategoryFiles as $categoryName => $categoryFiles) {
            $category_photos = [];

            foreach ($categoryFiles as $categoryFile) {
                if (isset($categoryFile->file)) {
                    $photo_id = uniqid();
                    $photo_info = [
                        'photo_unique_id' => $photo_id,
                        'file_id' => $categoryFile->file->id,
                        'path' => url($categoryFile->file->path),
                    ];

                    $category_photos[] = $photo_info;
                    $photo_general[] = $photo_info;
                }
            }

            if ($categoryFiles->isNotEmpty()) {
                $firstCategoryFile = $categoryFiles->first();

                $categories[] = [
                    'category_id' => $firstCategoryFile->category_id,
                    'category_name' => $firstCategoryFile->category->name,
                        'category_icone' => $firstCategoryFile->category->icone,
                           'number' => $firstCategoryFile->number,
                    'photos_category' => $category_photos,
                ];
            }
        }
    }

    $data = [
        'id_housing' => $listing->id,
        'photos_logement' => $photo_logement,
        'categories' => $categories,
        'photo_general' => $photo_general,
    ];

    return response()->json(['data' => $data], 200);
}

 /**
 * @OA\Post(
 *   path="/api/logement/index/ListeDesLogementsAcceuil",
 *   tags={"Housing"},
 *  @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=false,
     *         description="ID of the user connected",
     *         @OA\Schema(type="integer")
     *     ),
     *  @OA\Parameter(
 *     name="page",
 *     in="query",
 *     required=false ,
 *     description="Le numéro de la page pour la pagination",
 *     @OA\Schema(type="integer", example=1)
 *   ),
 *   summary="Liste des logements pour l'accueil et pour l'admin en même temps.",
 *   description="Récupère la liste des logements disponibles et vérifiés pour l'accueil.c'est cette route qui vous envoit les logements à afficher sur le site ",
 *    @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="user_id",
 *                 type="integer",
 *                 description="ID de l'utilisateur, facultatif",
 *                 example=123
 *             )
 *         )
 *     ),
 *   @OA\Response(
 *     response=200,
 *     description="Liste des logements récupérée avec succès",
 *     @OA\JsonContent(
 *       @OA\Property(property="data", type="array",
 *         @OA\Items(
 *           @OA\Property(property="id_housing", type="integer", example="1"),
 *           @OA\Property(property="housing_type_id", type="integer", example="1"),
 *           @OA\Property(property="housing_type_name", type="string", example="Appartement"),
 *           @OA\Property(property="property_type_id", type="integer", example="1"),
 *           @OA\Property(property="property_type_name", type="string", example="Location"),
 *           @OA\Property(property="user_id", type="integer", example="1"),
 *           @OA\Property(property="name_housing", type="string", example="Bel appartement"),
 *           @OA\Property(property="description", type="string", example="Description de l'appartement"),
 *           @OA\Property(property="number_of_bed", type="integer", example="2"),
 *           @OA\Property(property="number_of_traveller", type="integer", example="4"),
 *           @OA\Property(property="sit_geo_lat", type="string", example="48.858844"),
 *           @OA\Property(property="sit_geo_lng", type="string", example="2.294351"),
 *           @OA\Property(property="country", type="string", example="France"),
 *           @OA\Property(property="address", type="string", example="10 Rue de Rivoli"),
 *           @OA\Property(property="city", type="string", example="Paris"),
 *           @OA\Property(property="department", type="string", example="Île-de-France"),
 *           @OA\Property(property="is_camera", type="boolean", example=false),
 *           @OA\Property(property="is_accepted_animal", type="boolean", example=true),
 *           @OA\Property(property="is_animal_exist", type="boolean", example=true),
 *           @OA\Property(property="is_disponible", type="boolean", example=true),
 *           @OA\Property(property="interior_regulation", type="string", example="Regulations..."),
 *           @OA\Property(property="telephone", type="string", example="0123456789"),
 *           @OA\Property(property="code_pays", type="string", example="FR"),
 *           @OA\Property(property="status", type="string", example="verified"),
 *           @OA\Property(property="arrived_independently", type="boolean", example=true),
 *           @OA\Property(property="is_instant_reservation", type="boolean", example=true),
 *           @OA\Property(property="maximum_duration", type="integer", example="30"),
 *           @OA\Property(property="minimum_duration", type="integer", example="1"),
 *           @OA\Property(property="time_before_reservation", type="integer", example="2"),
 *           @OA\Property(property="cancelation_condition", type="string", example="Conditions..."),
 *           @OA\Property(property="departure_instruction", type="string", example="Instructions..."),
 *           @OA\Property(property="is_deleted", type="boolean", example=false),
 *           @OA\Property(property="is_blocked", type="boolean", example=false),
 *           @OA\Property(property="photos_logement", type="array",
 *             @OA\Items(
 *               @OA\Property(property="id_photo", type="integer", example="1"),
 *               @OA\Property(property="path", type="string", example="http://exemple.com/photos/1"),
 *               @OA\Property(property="extension", type="string", example="jpg"),
 *               @OA\Property(property="is_couverture", type="boolean", example=true)
 *             )
 *           ),
 *           @OA\Property(property="user", type="object",
 *             @OA\Property(property="id", type="integer", example="1"),
 *             @OA\Property(property="lastname", type="string", example="Doe"),
 *             @OA\Property(property="firstname", type="string", example="John"),
 *             @OA\Property(property="telephone", type="string", example="0123456789"),
 *             @OA\Property(property="code_pays", type="string", example="FR"),
 *             @OA\Property(property="email", type="string", example="john@example.com"),
 *             @OA\Property(property="country", type="string", example="France"),
 *             @OA\Property(property="file_profil", type="string", example="http://exemple.com/photo_profil/1"),
 *             @OA\Property(property="city", type="string", example="Paris"),
 *             @OA\Property(property="address", type="string", example="10 Rue de Rivoli"),
 *             @OA\Property(property="sexe", type="string", example="male"),
 *             @OA\Property(property="postal_code", type="string", example="75001"),
 *             @OA\Property(property="is_admin", type="boolean", example=false),
 *             @OA\Property(property="is_traveller", type="boolean", example=true),
 *             @OA\Property(property="is_hote", type="boolean", example=false)
 *           )
 *         )
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Aucun logement disponible trouvé",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Aucun logement disponible trouvé")
 *     )
 *   )
 * )
 */


 public function ListeDesLogementsAcceuil(Request $request)
{
    $page = intval($request->query('page', 1));
    $perPage = Setting::first()->pagination_logement_acceuil;

    // Étape 1: Récupérer les logements sponsorisés actifs
    $today = date('Y-m-d');
    $sponsoredHousings = DB::table('housing_sponsorings')
        ->where('is_actif', true)
        ->where('is_deleted', false)
        ->where('date_debut', '<=', $today)
        ->where('date_fin', '>=', $today)
        ->orderBy(DB::raw('prix * nombre'), 'asc')
        ->pluck('housing_id')
        ->toArray();

    // Récupérer les logements sponsorisés pour cette page
    $sponsoredListings = Housing::whereIn('id', $sponsoredHousings)
        ->where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('is_finished', 1)
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

    // Compter les logements sponsorisés récupérés
    $sponsoredCount = $sponsoredListings->count();

    // Liste finale des logements à retourner
    $listings = collect();

    // Si on n'a pas assez de logements sponsorisés pour la page actuelle
    if ($sponsoredCount < $perPage) {
        $remaining = $perPage - $sponsoredCount; // Nombre de logements manquants

        // Récupérer les logements non sponsorisés seulement si on a des places restantes
        $nonSponsoredListings = Housing::whereNotIn('id', $sponsoredHousings)
            ->where('status', 'verified')
            ->where('is_deleted', 0)
            ->where('is_blocked', 0)
            ->where('is_updated', 0)
            ->where('is_actif', 1)
            ->where('is_destroy', 0)
            ->where('is_finished', 1)
            ->skip(($page - 1) * $perPage - count($sponsoredHousings)) // Calculer le skip sur les non-sponsorisés uniquement
            ->take($remaining)
            ->get();

        // Fusionner les logements sponsorisés et non sponsorisés dans la liste finale
        $listings = $sponsoredListings->merge($nonSponsoredListings);
    } else {
        // Sinon, retourner uniquement les logements sponsorisés si suffisants
        $listings = $sponsoredListings;
    }

    // Étape 2: Vérification de l'utilisateur
    $userId = intval($request->query('id'));
    if ($request->query('id') && $userId <= 0) {
        return (new ServiceController())->apiResponse(404, [], "L'id qui doit servir à récupérer l'utilisateur connecté doit être positif");
    }

    // Étape 3: Formatter les données
    $data = $this->formatListingsData($listings, $userId);

    // Enregistrement des visites sur le site
    $controllervisitesite = App::make('App\Http\Controllers\UserVisiteSiteController');
    if ($request->has('user_id')) {
        $user_id = $request->input('user_id');
        $insertvisite = $controllervisitesite->recordSiteVisit($user_id);
    } else {
        $insertvisite = $controllervisitesite->recordSiteVisit();
    }

    // Retourner la réponse JSON avec les données formatées
    return response()->json(['data' => $data], 200);
}






/**
 * @OA\Post(
 *     path="/api/logement/ShowDetailLogementAcceuil",
 *     tags={"Housing"},
 *     summary="Liste des détails possibles d'un logement donné côté acceuil",
 *     description="Récupère les détails d'un logement spécifié par son ID. Prend les paramètres au format JSON dans le corps de la requête.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"housing_id"},
 *             @OA\Property(
 *                 property="housing_id",
 *                 type="integer",
 *                 description="ID du logement à afficher",
 *                 example=1
 *             ),
 *             @OA\Property(
 *                 property="user_id",
 *                 type="integer",
 *                 description="ID de l'utilisateur, facultatif",
 *                 example=123
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Détails du logement récupérés avec succès",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Le logement spécifié n'existe pas",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Le logement spécifié n'existe pas")
 *         )
 *     )
 * )
 */

 public function ShowDetailLogementAcceuil(Request $request)
 {


       $id= $request->input('housing_id');
       $user_id= $request->input('user_id');
          $housing = Housing::where('id', $id)->get();

        if($housing->isEmpty()) {
            return response()->json(['message' => " L\'ID du logement spécifié n\'existe pas"], 404);
        }
        (new PromotionController())->actionRepetitif($id);

     $listing = Housing::with([
         'photos',
         'housing_preference.preference',
         'reductions',
         'promotions',
         'housingCategoryFiles.category',
         'user',
         'housingType',
         'housingEquipments'
     ])->find($id);

     $equipments_by_category = $listing->housingEquipments
     ->where('is_verified', 1)
     ->groupBy('category.name')
     ->map(function ($categoryEquipment, $categoryName) {
         return [
             'category_id' => $categoryEquipment->first()->category_id,
             'category_name' => $categoryName,
             'equipments' => $categoryEquipment->map(function ($equipment) {
                 return [
                     'equipment_id' => $equipment->equipment_id,
                     'name' => $equipment->equipment->name,
                     'icone' => $equipment->equipment->icone,
                 ];
             }),
         ];
     })
     ->values();

     $hoteCharge_id = [];
     $travelerCharge_id = [];
     $housingCharges = Housing_charge::where('housing_id', $id)->get();
    //  if ($housingCharges->isEmpty()) {
    //      return response()->json(['message' => 'Aucune charge associé à ce logement'], 404);
    //  }

     foreach ($housingCharges as $housingCharge) {
         $charge = Charge::find($housingCharge->charge_id);
         if ($housingCharge->is_mycharge == true) {
             $hoteCharge_id[] = [
                 'id_housing_charge' => $housingCharge->id,
                 'housing_id' => $housingCharge->housing_id,
                 'id_charge' => $charge->id,
                 'charge_name' => $charge->name,
                 'charge_icone' => $charge->icone,
                 'is_mycharge' => $housingCharge->is_mycharge,
                  'valeur_charge' => $housingCharge->valeur
             ];
         }else{
             $travelerCharge_id[] = [
                 'id_housing_charge' => $housingCharge->id,
                 'housing_id' => $housingCharge->housing_id,
                 'id_charge' => $charge->id,
                 'charge_name' => $charge->name,
                 'charge_icone' => $charge->icone,
                 'is_mycharge' => $housingCharge->is_mycharge,
                'valeur_charge' => $housingCharge->valeur
             ];
         }
     }

     $userStatistique=$this->getHousingStatisticAcceuil($id);
     $promotion=$this->getCurrentPromotion($id)??[];
     $reduction=$this->getCurrentReductions($id)??[];
     $controllerreviewreservation = App::make('App\Http\Controllers\ReviewReservationController');
     $controllervitehousing = App::make('App\Http\Controllers\UserVisiteHousingController');
     $checkAuth=App::make('App\Http\Controllers\LoginController');



     $note_commentaire= $controllerreviewreservation->LogementAvecMoyenneNotesCritereEtCommentairesAcceuil($id);
      $insertvisite=$controllervitehousing->recordHousingVisit($id,$user_id);

     $data = [
         'id_housing' => $listing->id,
         'housing_type_id' => $listing->housing_type_id,
         'housing_type_name' => $listing->housingType->name,
         'housing_type_icone' => $listing->housingType->icone,
         'property_type_id' => $listing->property_type_id,
         'property_type_name' => $listing->propertyType->name,
         'property_type_icone' => $listing->propertyType->icone,
         'user_id' => $listing->user_id,
         'name_housing' => $listing->name,
         'description' => $listing->description,
         'number_of_bed' => $listing->number_of_bed,
         'number_of_traveller' => $listing->number_of_traveller,
         'sit_geo_lat' => $listing->sit_geo_lat,
         'sit_geo_lng' => $listing->sit_geo_lng,
         'country' => $listing->country,
         'address' => $listing->address,
         'city' => $listing->city,
         'department' => $listing->department,
         'is_camera' => $listing->is_camera,
         'is_accepted_animal' => $listing->is_accepted_animal,
         'is_animal_exist' => $listing->is_animal_exist,
         'is_disponible' => $listing->is_disponible,
         'interior_regulation' => $listing->interior_regulation,
         'interior_regulation_pdf' => $listing->interior_regulation_pdf,
        //  'telephone' => $listing->telephone,
         'code_pays' => $listing->code_pays,
         'surface' => $listing->surface,
         'price' => $listing->price,
         'status' => $listing->status,
         'arrived_independently' => $listing->arrived_independently,
         'is_instant_reservation' => $listing->is_instant_reservation,
         'minimum_duration' => $listing->minimum_duration,
         'time_before_reservation' => $listing->time_before_reservation,
         'cancelation_condition' => $listing->cancelation_condition,
         'departure_instruction' => $listing->departure_instruction,
         'is_accept_arm' => $listing->is_accept_arm,
         'is_accept_noise' => $listing->is_accept_noise,
         'is_accept_smoking' => $listing->is_accept_smoking,
         'is_accept_chill' => $listing->is_accept_smoking,
         'is_accept_alcool' => $listing->is_accept_alccol,
         'is_deleted' => $listing->is_deleted,
         'is_blocked' => $listing->is_blocked,
         'created_at' => $listing->created_at,
         'is_accept_anulation'=> $listing->is_accept_anulation,
         'delai_partiel_remboursement'=> $listing->delai_partiel_remboursement,
         'delai_integral_remboursement'=> $listing->delai_integral_remboursement,
         'valeur_integral_remboursement'=> $listing->valeur_integral_remboursement,
         'valeur_partiel_remboursement'=> $listing->valeur_partiel_remboursement,
         "housing_note" => (new ReviewReservationController())->LogementAvecMoyenneNotesCritereEtCommentairesAcceuil($listing->id)->original['data']['overall_average'] ?? 'non renseigné',


         'photos_logement' => $listing->photos->map(function ($photo) {
            if($photo->is_verified){
                return [
                    'id_photo' => $photo->id,
                    'path' => $photo->path,
                    'extension' => $photo->extension,
                    'is_couverture' => $photo->is_couverture,
                ];
            }

         }),
         'user' => [
             'id' => $listing->user->id,
             'lastname' => $listing->user->lastname,
             'firstname' => $listing->user->firstname,
            //  'telephone' => $listing->user->telephone,
             'code_pays' => $listing->user->code_pays,
            //  'email' => $listing->user->email,
             'country' => $listing->user->country,
             'file_profil' => $listing->user->file_profil,
             'city' => $listing->user->city,
             'address' => $listing->user->address,
             'sexe' => $listing->user->sexe,
             'postal_code' => $listing->user->postal_code,
             "date_enregistrement_de_hote" => $listing->user->created_at,
         ],

         'housing_preference' => $listing->housing_preference->filter(function ($preference) {
            return $preference->is_verified;
            })->map(function ($housingpreference) {
            return [
                 'id' => $housingpreference->id,
                 'preference_id' => $housingpreference->preference_id,
                 'preference_name' => $housingpreference->preference->name,
                 'icone' => $housingpreference->preference->icone,
            ];
          }),

         'reductions' =>$reduction->original['data']??[],

         'promotions' => [$promotion->original['data']]??[],

         'categories' => $listing->housingCategoryFiles->where('is_verified', 1)->groupBy('category.name')->map(function ($categoryFiles, $categoryName) {
            return [
                'category_id' => $categoryFiles->first()->category_id,
                'category_name' => $categoryFiles->first()->category->name,
                'icone' => $categoryFiles->first()->category->icone,
                'number' => $categoryFiles->first()->number,
                'photos_category' => $categoryFiles->map(function ($categoryFile) {
                    return [
                        'file_id' => $categoryFile->file->id,
                        'path' => $categoryFile->file->path,
                    ];
                }),
            ];
        })->values(),

        'equipments_by_category' => $equipments_by_category,
        'charges' => [
            'charge_hote' => $hoteCharge_id,
            'charge_traveler' => $travelerCharge_id
        ],

        'User_statistique' => $userStatistique->original,
        'note_commentaire'=> $note_commentaire->original['data']

     ];

     return response()->json(['data' => $data]);
 }

/**
 * @OA\Get(
 *     path="/api/logement/filterby/typehousing/{housingTypeId}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par type de logement",
 *     description="Récupère la liste des logements filtrée par le type de logement spécifié.",
 *     operationId="listingsFilteredByHousingType",
 *    @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Nom du pays",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="housingTypeId",
 *         in="path",
 *         description="ID du type de logement",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le type spécifié"
 *     )
 * )
 */

 public function  ListeDesLogementsAcceuilFilterByTypehousing(Request $request ,$id)
    {

        if(!$request->page){
            return (new ServiceController())->apiResponse(404, [], "Le numéro de page est obligatoire");
        }

        $page = intval($request->query('page', 1));
        $perPage = Setting::first()->pagination_logement_acceuil;
        // $listings = Housing::where('status', 'verified')
        // ->where('is_deleted', 0)
        // ->where('is_blocked', 0)
        // ->where('housing_type_id', $id)
        // ->where('is_updated', 0)
        // ->where('is_actif', 1)
        // ->where('is_destroy', 0)
        // ->where('is_finished', 1)
        // ->paginate($perPage, ['*'], 'page', $page);

        $today = date('Y-m-d');
        $sponsoredHousings = DB::table('housing_sponsorings')
            ->where('is_actif', true)
            ->where('is_deleted', false)
            ->where('date_debut', '<=', $today)
            ->where('date_fin', '>=', $today)
            ->orderBy(DB::raw('prix * nombre'), 'asc')
            ->pluck('housing_id')
            ->toArray();

            $sponsoredListings = Housing::whereIn('id', $sponsoredHousings)
            ->where('status', 'verified')
            ->where('is_deleted', 0)
            ->where('is_blocked', 0)
            ->where('is_updated', 0)
            ->where('is_actif', 1)
            ->where('is_destroy', 0)
            ->where('housing_type_id', $id)
            ->where('is_finished', 1)
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();
    
            $sponsoredCount = $sponsoredListings->count();
    
            $listings = collect();
    
            if ($sponsoredCount < $perPage) {
                $remaining = $perPage - $sponsoredCount; 

                $nonSponsoredListings = Housing::whereNotIn('id', $sponsoredHousings)
                    ->where('status', 'verified')
                    ->where('is_deleted', 0)
                    ->where('is_blocked', 0)
                    ->where('is_updated', 0)
                    ->where('is_actif', 1)
                    ->where('is_destroy', 0)
                    ->where('housing_type_id', $id)
                    ->where('is_finished', 1)
                    ->skip(($page - 1) * $perPage - count($sponsoredHousings))
                    ->take($remaining)
                    ->get();
        
                $listings = $sponsoredListings->merge($nonSponsoredListings);
            } else {
                $listings = $sponsoredListings;
            }

        $data = $this->formatListingsData($listings);

        return response()->json(['data' => $data],200);
    }


 /**
 * @OA\Get(
 *     path="/api/logement/filterby/typeproperty/{PropertyTypeId}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par type de proprieté",
 *     description="Récupère la liste des logements filtrée par le type de propriété spécifié.",
 *     operationId="listingsFilteredByPropertyType",
 *  @OA\Parameter(
 *     name="page",
 *     in="query",
 *     required=false,
 *     description="Le numéro de la page pour la pagination",
 *     @OA\Schema(type="string", example=1)
 *   ),
 *     @OA\Parameter(
 *         name="PropertyTypeId",
 *         in="path",
 *         description="ID du type de propriété",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le type spécifié"
 *     )
 * )
 */
    public function  ListeDesLogementsAcceuilFilterByTypeproperty(Request $request, $id)
    {

        if(!$request->page){
            return (new ServiceController())->apiResponse(404, [], "Le numéro de page est obligatoire");
        }
        $page = intval($request->query('page', 1));
        $perPage = Setting::first()->pagination_logement_acceuil;
        $today = date('Y-m-d');
        $sponsoredHousings = DB::table('housing_sponsorings')
            ->where('is_actif', true)
            ->where('is_deleted', false)
            ->where('date_debut', '<=', $today)
            ->where('date_fin', '>=', $today)
            ->orderBy(DB::raw('prix * nombre'), 'asc')
            ->pluck('housing_id')
            ->toArray();

        $sponsoredListings = Housing::whereIn('id', $sponsoredHousings)
        ->where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('property_type_id', $id)
        ->where('is_finished', 1)
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

        $sponsoredCount = $sponsoredListings->count();

        $listings = collect();

        if ($sponsoredCount < $perPage) {
            $remaining = $perPage - $sponsoredCount;
            $nonSponsoredListings = Housing::whereNotIn('id', $sponsoredHousings)
                ->where('status', 'verified')
                ->where('is_deleted', 0)
                ->where('is_blocked', 0)
                ->where('is_updated', 0)
                ->where('is_actif', 1)
                ->where('is_destroy', 0)
                ->where('property_type_id', $id)
                ->where('is_finished', 1)
                ->skip(($page - 1) * $perPage - count($sponsoredHousings)) 
                ->take($remaining)
                ->get();
    
            $listings = $sponsoredListings->merge($nonSponsoredListings);
        } else {
            $listings = $sponsoredListings;
        }

        $data = $this->formatListingsData($listings);

        $data = $this->formatListingsData($listings);

        return response()->json(['data' => $data],200);
    }


 /**
 * @OA\Get(
 *     path="/api/logement/filterby/country/{country}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par Pays",
 *     description="Récupère la liste des logements filtrée par le pays spécifié.",
 *   @OA\Parameter(
 *     name="page",
 *     in="query",
 *     required=false,
 *     description="Le numéro de la page pour la pagination",
 *     @OA\Schema(type="string", example=1)
 *   ),
 *     @OA\Parameter(
 *         name="country",
 *         in="path",
 *         description="Nom du pays",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée par pays",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le type spécifié"
 *     )
 * )
 */
    public function  ListeDesLogementsFilterByCountry(Request $request,$country)
    {

        if(!$request->page){
            return (new ServiceController())->apiResponse(404, [], "Le numéro de page est obligatoire");
        }

        $page = intval($request->query('page', 1));
        $perPage = Setting::first()->pagination_logement_acceuil;

        $today = date('Y-m-d');
        $sponsoredHousings = DB::table('housing_sponsorings')
            ->where('is_actif', true)
            ->where('is_deleted', false)
            ->where('date_debut', '<=', $today)
            ->where('date_fin', '>=', $today)
            ->orderBy(DB::raw('prix * nombre'), 'asc')
            ->pluck('housing_id')
            ->toArray();

        $sponsoredListings = Housing::whereIn('id', $sponsoredHousings)
        ->where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('country', $country)
        ->where('is_finished', 1)
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

        $sponsoredCount = $sponsoredListings->count();

        $listings = collect();

        if ($sponsoredCount < $perPage) {
            $remaining = $perPage - $sponsoredCount;
            $nonSponsoredListings = Housing::whereNotIn('id', $sponsoredHousings)
                ->where('status', 'verified')
                ->where('is_deleted', 0)
                ->where('is_blocked', 0)
                ->where('is_updated', 0)
                ->where('is_actif', 1)
                ->where('is_destroy', 0)
                ->where('country', $country)
                ->where('is_finished', 1)
                ->skip(($page - 1) * $perPage - count($sponsoredHousings)) 
                ->take($remaining)
                ->get();
    
            $listings = $sponsoredListings->merge($nonSponsoredListings);
        } else {
            $listings = $sponsoredListings;
        }

        $data = $this->formatListingsData($listings);
        return response()->json(['data' => $data],200);

      }

/**
 * @OA\Get(
 *     path="/api/logement/filterby/preference/{preference_id}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par preference",
 *     description="Récupère la liste des logements filtrée par le id preference spécifié.",
 *   @OA\Parameter(
 *     name="page",
 *     in="query",
 *     required=false,
 *     description="Le numéro de la page pour la pagination",
 *     @OA\Schema(type="string", example=1)
 *   ),
 *     @OA\Parameter(
 *         name="preference_id",
 *         in="path",
 *         description="Id du preference",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée par préférence",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le type spécifié"
 *     )
 * )
 */
public function  ListeDesLogementsAcceuilFilterByPreference(Request $request,$preferenceId)
    {

        if(!$request->page){
            return (new ServiceController())->apiResponse(404, [], "Le numéro de page est obligatoire");
        }
        $page = intval($request->query('page', 1));
        $perPage = Setting::first()->pagination_logement_acceuil;

        $today = date('Y-m-d');
        $sponsoredHousings = DB::table('housing_sponsorings')
            ->where('is_actif', true)
            ->where('is_deleted', false)
            ->where('date_debut', '<=', $today)
            ->where('date_fin', '>=', $today)
            ->orderBy(DB::raw('prix * nombre'), 'asc')
            ->pluck('housing_id')
            ->toArray();

        $sponsoredListings = Housing::whereIn('id', $sponsoredHousings)
        ->where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->whereHas('housing_preference', function ($query) use ($preferenceId) {
            $query->where('preference_id', $preferenceId);
        })
        ->where('is_finished', 1)
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

        $sponsoredCount = $sponsoredListings->count();

        $listings = collect();

        if ($sponsoredCount < $perPage) {
            $remaining = $perPage - $sponsoredCount;
            $nonSponsoredListings = Housing::whereNotIn('id', $sponsoredHousings)
                ->where('status', 'verified')
                ->where('is_deleted', 0)
                ->where('is_blocked', 0)
                ->where('is_updated', 0)
                ->where('is_actif', 1)
                ->where('is_destroy', 0)
                ->whereHas('housing_preference', function ($query) use ($preferenceId) {
                    $query->where('preference_id', $preferenceId);
                })
                ->where('is_finished', 1)
                ->skip(($page - 1) * $perPage - count($sponsoredHousings)) 
                ->take($remaining)
                ->get();
    
            $listings = $sponsoredListings->merge($nonSponsoredListings);
        } else {
            $listings = $sponsoredListings;
        }

        $data = $this->formatListingsData($listings);
        return response()->json(['data' => $data],200);
    }
/**
 * @OA\Get(
 *     path="/api/logement/filterby/city/{city}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par ville",
 *     description="Récupère la liste des logements filtrée par villespécifié.",
 *  @OA\Parameter(
 *     name="page",
 *     in="query",
 *     required=false,
 *     description="Le numéro de la page pour la pagination",
 *     @OA\Schema(type="string", example=1)
 *   ),
 *     @OA\Parameter(
 *         name="city",
 *         in="path",
 *         description="Nom de la ville",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée par ville",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le type spécifié"
 *     )
 * )
 */
 public function  ListeDesLogementsFilterByCity(Request $request, $city)
    {
        if(!$request->page){
            return (new ServiceController())->apiResponse(404, [], "Le numéro de page est obligatoire");
        }
        $page = intval($request->query('page', 1));
        $perPage = Setting::first()->pagination_logement_acceuil;

        $today = date('Y-m-d');
        $sponsoredHousings = DB::table('housing_sponsorings')
            ->where('is_actif', true)
            ->where('is_deleted', false)
            ->where('date_debut', '<=', $today)
            ->where('date_fin', '>=', $today)
            ->orderBy(DB::raw('prix * nombre'), 'asc')
            ->pluck('housing_id')
            ->toArray();

        $sponsoredListings = Housing::whereIn('id', $sponsoredHousings)
        ->where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('city', $city)
        ->where('is_finished', 1)
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

        $sponsoredCount = $sponsoredListings->count();

        $listings = collect();

        if ($sponsoredCount < $perPage) {
            $remaining = $perPage - $sponsoredCount;
            $nonSponsoredListings = Housing::whereNotIn('id', $sponsoredHousings)
                ->where('status', 'verified')
                ->where('is_deleted', 0)
                ->where('is_blocked', 0)
                ->where('is_updated', 0)
                ->where('is_actif', 1)
                ->where('is_destroy', 0)
                ->where('city', $city)
                ->where('is_finished', 1)
                ->skip(($page - 1) * $perPage - count($sponsoredHousings)) 
                ->take($remaining)
                ->get();
    
            $listings = $sponsoredListings->merge($nonSponsoredListings);
        } else {
            $listings = $sponsoredListings;
        }

        $data = $this->formatListingsData($listings);
        return response()->json(['data' => $data],200);
 }

/**
 * @OA\Get(
 *     path="/api/logement/filterby/department/{departement}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par departement",
 *     description="Récupère la liste des logements filtrée par departement spécifié.",
 *    @OA\Parameter(
 *     name="page",
 *     in="query",
 *     required=false,
 *     description="Le numéro de la page pour la pagination",
 *     @OA\Schema(type="string", example=1)
 *   ),
 *     @OA\Parameter(
 *         name="departement",
 *         in="path",
 *         description="Nom du departement",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée par departement",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le type spécifié"
 *     )
 * )
 */
 public function  ListeDesLogementsFilterByDepartement(Request $request,$department)
    {

        if(!$request->page){
            return (new ServiceController())->apiResponse(404, [], "Le numéro de page est obligatoire");
        }

        $page = intval($request->query('page', 1));
        $perPage = Setting::first()->pagination_logement_acceuil;

        $today = date('Y-m-d');
        $sponsoredHousings = DB::table('housing_sponsorings')
            ->where('is_actif', true)
            ->where('is_deleted', false)
            ->where('date_debut', '<=', $today)
            ->where('date_fin', '>=', $today)
            ->orderBy(DB::raw('prix * nombre'), 'asc')
            ->pluck('housing_id')
            ->toArray();

        $sponsoredListings = Housing::whereIn('id', $sponsoredHousings)
        ->where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('department', $department)
        ->where('is_finished', 1)
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

        $sponsoredCount = $sponsoredListings->count();

        $listings = collect();

        if ($sponsoredCount < $perPage) {
            $remaining = $perPage - $sponsoredCount;
            $nonSponsoredListings = Housing::whereNotIn('id', $sponsoredHousings)
                ->where('status', 'verified')
                ->where('is_deleted', 0)
                ->where('is_blocked', 0)
                ->where('is_updated', 0)
                ->where('is_actif', 1)
                ->where('is_destroy', 0)
                ->where('department', $department)
                ->where('is_finished', 1)
                ->skip(($page - 1) * $perPage - count($sponsoredHousings)) 
                ->take($remaining)
                ->get();
    
            $listings = $sponsoredListings->merge($nonSponsoredListings);
        } else {
            $listings = $sponsoredListings;
        }
        $data = $this->formatListingsData($listings);
        return response()->json(['data' => $data],200);
 }
/**
 * @OA\Get(
 *     path="/api/logement/filterby/nbtraveler/{nbtraveler}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par nbtraveler",
 *     description="Récupère la liste des logements filtrée par nbtraveler spécifié.",
 *  @OA\Parameter(
 *     name="page",
 *     in="query",
 *     required=false,
 *     description="Le numéro de la page pour la pagination",
 *     @OA\Schema(type="string", example=1)
 *   ),
 *     @OA\Parameter(
 *         name="nbtraveler",
 *         in="path",
 *         description="Nombre de voyageur",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée par nbtraveler",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le type spécifié"
 *     )
 * )
 */
 public function  ListeDesLogementsAcceuilFilterNbtravaller(Request $request, $nbtravaler)
    {

        if(!$request->page){
            return (new ServiceController())->apiResponse(404, [], "Le numéro de page est obligatoire");
        }
        $page = intval($request->query('page', 1));
        $perPage = Setting::first()->pagination_logement_acceuil;

        $today = date('Y-m-d');
        $sponsoredHousings = DB::table('housing_sponsorings')
            ->where('is_actif', true)
            ->where('is_deleted', false)
            ->where('date_debut', '<=', $today)
            ->where('date_fin', '>=', $today)
            ->orderBy(DB::raw('prix * nombre'), 'asc')
            ->pluck('housing_id')
            ->toArray();

        $sponsoredListings = Housing::whereIn('id', $sponsoredHousings)
        ->where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('number_of_traveller','<=',$nbtravaler)
        ->where('is_finished', 1)
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

        $sponsoredCount = $sponsoredListings->count();

        $listings = collect();

        if ($sponsoredCount < $perPage) {
            $remaining = $perPage - $sponsoredCount;
            $nonSponsoredListings = Housing::whereNotIn('id', $sponsoredHousings)
                ->where('status', 'verified')
                ->where('is_deleted', 0)
                ->where('is_blocked', 0)
                ->where('is_updated', 0)
                ->where('is_actif', 1)
                ->where('is_destroy', 0)
                ->where('number_of_traveller','<=', $nbtravaler)
                ->where('is_finished', 1)
                ->skip(($page - 1) * $perPage - count($sponsoredHousings)) 
                ->take($remaining)
                ->get();
    
            $listings = $sponsoredListings->merge($nonSponsoredListings);
        } else {
            $listings = $sponsoredListings;
        }

        $data = $this->formatListingsData($listings);

        return response()->json(['data' => $data],200);
    }



    /**
 * @OA\Get(
 *     path="/api/logement/filterby/destination/{location}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par destination (pays, ville ou département)",
 *     description="Récupère la liste des logements filtrée par pays, ville ou département spécifié.",
 *     @OA\Parameter(
 *         name="location",
 *         in="path",
 *         description="Pays, ville ou département à filtrer",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             example="Paris"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Le numéro de la page pour la pagination",
 *         required=false,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée par pays, ville ou département",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="title", type="string", example="Charmant appartement à Paris"),
 *                     @OA\Property(property="price", type="number", format="float", example=120.50),
 *                     @OA\Property(property="status", type="string", example="verified")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour la destination spécifiée"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Accès interdit"
 *     )
 * )
 */


    public function ListeDesLogementsFilterByDestination(Request $request, $location)
{
    if(!$request->page){
        return (new ServiceController())->apiResponse(404, [], "Le numéro de page est obligatoire");
    }

    $page = intval($request->query('page', 1));
    $perPage = Setting::first()->pagination_logement_acceuil;


    $today = date('Y-m-d');
        $sponsoredHousings = DB::table('housing_sponsorings')
            ->where('is_actif', true)
            ->where('is_deleted', false)
            ->where('date_debut', '<=', $today)
            ->where('date_fin', '>=', $today)
            ->orderBy(DB::raw('prix * nombre'), 'asc')
            ->pluck('housing_id')
            ->toArray();

        $sponsoredListings = Housing::whereIn('id', $sponsoredHousings)
        ->where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where(function($q) use ($location) {
            $q->where('country', $location)
              ->orWhere('city', $location)
              ->orWhere('department', $location);
        })
        ->where('is_finished', 1)
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

        $sponsoredCount = $sponsoredListings->count();

        $listings = collect();

        if ($sponsoredCount < $perPage) {
            $remaining = $perPage - $sponsoredCount;
            $nonSponsoredListings = Housing::whereNotIn('id', $sponsoredHousings)
                ->where('status', 'verified')
                ->where('is_deleted', 0)
                ->where('is_blocked', 0)
                ->where('is_updated', 0)
                ->where('is_actif', 1)
                ->where('is_destroy', 0)
                ->where(function($q) use ($location) {
                    $q->where('country', $location)
                      ->orWhere('city', $location)
                      ->orWhere('department', $location);
                })
                ->where('is_finished', 1)
                ->skip(($page - 1) * $perPage - count($sponsoredHousings)) 
                ->take($remaining)
                ->get();
    
            $listings = $sponsoredListings->merge($nonSponsoredListings);
        } else {
            $listings = $sponsoredListings;
        }


    $data = $this->formatListingsData($listings);



    return response()->json(['data' => $data], 200);

    return (new ServiceController())->apiResponse(403,[],'Liste des logements filtrés par destination');
}


    /**
 * @OA\Get(
 *     path="/api/logement/filterby/nightpricemax/{price}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par prix de nuit(Maximum)",
 *     description="Récupère la liste des logements filtrée par un prix de nuit inférieur au montant spécifié.",
 *   @OA\Parameter(
 *     name="page",
 *     in="query",
 *     required=false,
 *     description="Le numéro de la page pour la pagination",
 *     @OA\Schema(type="string", example=1)
 *   ),
 *     @OA\Parameter(
 *         name="price",
 *         in="path",
 *         description="Prix de nuit maximum",
 *         required=true,
 *         @OA\Schema(
 *             type="number",
 *             format="float"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée par prix de nuit",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le prix spécifié"
 *     )
 * )
 */
public function getListingsByNightPriceMax(Request $request, $price)
{
    if(!$request->page){
        return (new ServiceController())->apiResponse(404, [], "Le numéro de page est obligatoire");
    }
    $page = intval($request->query('page', 1));
    $perPage = Setting::first()->pagination_logement_acceuil;

        $today = date('Y-m-d');
        $sponsoredHousings = DB::table('housing_sponsorings')
            ->where('is_actif', true)
            ->where('is_deleted', false)
            ->where('date_debut', '<=', $today)
            ->where('date_fin', '>=', $today)
            ->orderBy(DB::raw('prix * nombre'), 'asc')
            ->pluck('housing_id')
            ->toArray();

        $sponsoredListings = Housing::whereIn('id', $sponsoredHousings)
        ->where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('price', '<=', $price)
        ->where('is_finished', 1)
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

        $sponsoredCount = $sponsoredListings->count();

        $listings = collect();

        if ($sponsoredCount < $perPage) {
            $remaining = $perPage - $sponsoredCount;
            $nonSponsoredListings = Housing::whereNotIn('id', $sponsoredHousings)
                ->where('status', 'verified')
                ->where('is_deleted', 0)
                ->where('is_blocked', 0)
                ->where('is_updated', 0)
                ->where('is_actif', 1)
                ->where('is_destroy', 0)
                ->where('price', '<=', $price)
                ->where('is_finished', 1)
                ->skip(($page - 1) * $perPage - count($sponsoredHousings)) 
                ->take($remaining)
                ->get();
    
            $listings = $sponsoredListings->merge($nonSponsoredListings);
        } else {
            $listings = $sponsoredListings;
        }

    $data = $this->formatListingsData($listings);

    return response()->json(['data' => $data], 200);
}

   /**
 * @OA\Get(
 *     path="/api/logement/filterby/nightpricemin/{price}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par prix de nuit (Minimum)",
 *     description="Récupère la liste des logements filtrée par un prix de nuit supérieur au montant spécifié.",
 *  @OA\Parameter(
 *     name="page",
 *     in="query",
 *     required=false,
 *     description="Le numéro de la page pour la pagination",
 *     @OA\Schema(type="string", example=1)
 *   ),
 *     @OA\Parameter(
 *         name="price",
 *         in="path",
 *         description="Prix de nuit minimum",
 *         required=true,
 *         @OA\Schema(
 *             type="number",
 *             format="float"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrés par prix de nuit",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le prix spécifié"
 *     )
 * )
 */
public function getListingsByNightPriceMin(Request $request,$price)
{

    if(!$request->page){
        return (new ServiceController())->apiResponse(404, [], "Le numéro de page est obligatoire");
    }

    $page = intval($request->query('page', 1));
    $perPage = Setting::first()->pagination_logement_acceuil;

        $today = date('Y-m-d');
        $sponsoredHousings = DB::table('housing_sponsorings')
            ->where('is_actif', true)
            ->where('is_deleted', false)
            ->where('date_debut', '<=', $today)
            ->where('date_fin', '>=', $today)
            ->orderBy(DB::raw('prix * nombre'), 'asc')
            ->pluck('housing_id')
            ->toArray();

        $sponsoredListings = Housing::whereIn('id', $sponsoredHousings)
        ->where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('price', '>=', $price)
        ->where('is_finished', 1)
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

        $sponsoredCount = $sponsoredListings->count();

        $listings = collect();

        if ($sponsoredCount < $perPage) {
            $remaining = $perPage - $sponsoredCount;
            $nonSponsoredListings = Housing::whereNotIn('id', $sponsoredHousings)
                ->where('status', 'verified')
                ->where('is_deleted', 0)
                ->where('is_blocked', 0)
                ->where('is_updated', 0)
                ->where('is_actif', 1)
                ->where('is_destroy', 0)
                ->where('price', '>=', $price)
                ->where('is_finished', 1)
                ->skip(($page - 1) * $perPage - count($sponsoredHousings)) 
                ->take($remaining)
                ->get();
    
            $listings = $sponsoredListings->merge($nonSponsoredListings);
        } else {
            $listings = $sponsoredListings;
        }

    $data = $this->formatListingsData($listings);

    return response()->json(['data' => $data], 200);
}

 /**
 * @OA\Get(
 *     path="/api/logement/filterby/hote/{UserID}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par user",
 *     description="Récupère la liste des logements pour un User donné.",
 * @OA\Parameter(
 *     name="page",
 *     in="query",
 *     required=false,
 *     description="Le numéro de la page pour la pagination",
 *     @OA\Schema(type="string", example=1)
 *   ),
 *     @OA\Parameter(
 *         name="UserID",
 *         in="path",
 *         description="Id de User Hote",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements filtrée par Id User Hote",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé pour le type spécifié"
 *     )
 * )
 */
 public function ListeDesLogementsFilterByHote(Request $request, $userId)
    {

        if(!$request->page){
            return (new ServiceController())->apiResponse(404, [], "Le numéro de page est obligatoire");
        }
        $page = intval($request->query('page', 1));
        $perPage = Setting::first()->pagination_logement_acceuil;

        $today = date('Y-m-d');
        $sponsoredHousings = DB::table('housing_sponsorings')
            ->where('is_actif', true)
            ->where('is_deleted', false)
            ->where('date_debut', '<=', $today)
            ->where('date_fin', '>=', $today)
            ->orderBy(DB::raw('prix * nombre'), 'asc')
            ->pluck('housing_id')
            ->toArray();

        $sponsoredListings = Housing::whereIn('id', $sponsoredHousings)
        ->where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('user_id', $userId)
        ->where('is_finished', 1)
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

        $sponsoredCount = $sponsoredListings->count();

        $listings = collect();

        if ($sponsoredCount < $perPage) {
            $remaining = $perPage - $sponsoredCount;
            $nonSponsoredListings = Housing::whereNotIn('id', $sponsoredHousings)
                ->where('status', 'verified')
                ->where('is_deleted', 0)
                ->where('is_blocked', 0)
                ->where('is_updated', 0)
                ->where('is_actif', 1)
                ->where('is_destroy', 0)
                ->where('user_id', $userId)
                ->where('is_finished', 1)
                ->skip(($page - 1) * $perPage - count($sponsoredHousings)) 
                ->take($remaining)
                ->get();
    
            $listings = $sponsoredListings->merge($nonSponsoredListings);
        } else {
            $listings = $sponsoredListings;
        }

        $data = $this->formatListingsData($listings);

        return response()->json(['data' => $data,'nombre'=>$data->count()],200);
 }

/**
 * @OA\Put(
 *     path="/api/logement/update/sensible/{id}",
 *     tags={"Dashboard hote"},
 *     summary="Modifier les informations sensibles d'un logement",
 *     description="Permet de mettre à jour les informations sensibles d'un logement existant à partir de son ID",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID du logement à mettre à jour",
 *         required=true,
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
 *                 @OA\Property(
 *                     property="interior_regulation",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="telephone",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="code_pays",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="arrived_independently",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="cancelation_condition",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="departure_instruction",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="surface",
 *                     type="number"
 *                 ),
 *                 @OA\Property(
 *                     property="price",
 *                     type="number"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Les informations du logement ont été mises à jour avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Les informations du logement ont été mises à jour avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Le logement à mettre à jour n'existe pas",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Le logement à mettre à jour n'existe pas")
 *         )
 *     )
 * )
 */


 public function updateSensibleHousing(Request $request, $id)
 {
     $userId = Auth::id();
     $housing = Housing::find($id);

     if (!$housing) {
         return response()->json(['message' => 'Le logement spécifié n\'existe pas'], 404);
     }

     (new PromotionController())->actionRepetitif($id);

     $validatedData = $request->validate([
         'interior_regulation' => 'required',
         'telephone' => 'required',
         'code_pays' => 'required',
         'arrived_independently' => 'required',
         'cancelation_condition' => 'required',
         'departure_instruction' => 'required',
         'surface' => 'required',
         'price' => 'required',
     ]);

     if ($housing->user_id != $userId ) {
        return response()->json(['error' => 'Seul le propriétaire du logement peut la modifier '], 403);
    }

     $housing->update($validatedData);
     $housing->is_updated = true;
     $housing->save();

     $notificationText = "Le logement avec ID: $id a été mis à jour. Veuillez valider la mise à jour dès que possible.";


     $right = Right::where('name','admin')->first();
     $adminUsers = User_right::where('right_id', $right->id)->get();
     foreach ($adminUsers as $adminUser) {


     $mail = [
         "title" => "Mise à jour de logement",
         "body" => "Un logement avec ID: $id a été mis à jour. Veuillez valider la mise à jour dès que possible."
     ];

     dispatch( new SendRegistrationEmail($adminUser->email, $mail['body'], $mail['title'], 2));
    }

     return response()->json(['message' => 'Logement mis à jour avec succès'], 200);
 }



/**
 * @OA\Put(
 *     path="/api/logement/update/insensible/{id}",
 *     tags={"Dashboard hote"},
 *     summary="Modifier les informations insensibles d'un logement",
 *     description="Permet de modifier les informations insensibles d'un logement existant à partir de son ID",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID du logement à mettre à jour",
 *         required=true,
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
 *                 @OA\Property(
 *                     property="name",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="description",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="number_of_bed",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="number_of_traveller",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="is_camera",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="is_accepted_animal",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="is_animal_exist",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="is_instant_reservation",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="maximum_duration",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="minimum_duration",
 *                     type="integer"
 *                 ),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Les informations du logement ont été mises à jour avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Les informations du logement ont été mises à jour avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Le logement à mettre à jour n'existe pas",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Le logement à mettre à jour n'existe pas")
 *         )
 *     )
 * )
 */
public function updateInsensibleHousing(Request $request, $id)
{
    $userId = Auth::id();
    $housing = Housing::find($id);
    if (!$housing) {
        return response()->json(['message' => 'Le logement spécifié n\'existe pas'], 404);
    }


    (new PromotionController())->actionRepetitif($id);

    $validatedData = $request->validate([
        'name' => 'required|string',
        'description' => 'required|string',
        'number_of_bed' => 'required|integer',
        'number_of_traveller' => 'required|integer',
        'is_camera' => 'required|integer',
        'is_accepted_animal' => 'required|integer',
        'is_animal_exist' => 'required|integer',
        'is_instant_reservation' => 'required|integer',
        'maximum_duration' => 'required|integer',
        'minimum_duration' => 'required|integer',
    ]);

    if ($housing->user_id != $userId ) {
        return response()->json(['error' => 'Seul le propriétaire du logement peut la modifier'], 403);
    }

    $housing->update($validatedData);
    $housing->is_updated = false;
    $housing->save();

    return response()->json(['message' => 'Logement mis à jour avec succès'], 200);
}




public function formatListingsData($listings,$userId=0)
{
    foreach ($listings as $listing){
        (new PromotionController())->actionRepetitif($listing->id);
    }



    // return DB::table('favoris')->where('user_id', 9)->where('housing_id', 2)->exists() ;

    return $listings->map(function ($listing) use ($userId) {
        return [
            'id_housing' => $listing->id,
            'housing_type_id' => $listing->housing_type_id ?? 'non renseigné',
            'housing_type_name' => $listing->housingType->name ?? 'non renseigné',
            'property_type_id' => $listing->property_type_id ?? 'non renseigné',
            'property_type_name' => $listing->propertyType->name ?? 'non renseigné',
            'user_id' => $listing->user_id ?? 'non renseigné',
            'name_housing' => $listing->name ?? 'non renseigné',
            'description' => $listing->description ?? 'non renseigné',
            'number_of_bed' => $listing->number_of_bed ?? 'non renseigné',
            'number_of_traveller' => $listing->number_of_traveller ?? 'non renseigné',
            'sit_geo_lat' => $listing->sit_geo_lat ?? 'non renseigné',
            'sit_geo_lng' => $listing->sit_geo_lng ?? 'non renseigné',
            'country' => $listing->country ?? 'non renseigné',
            'address' => $listing->address ?? 'non renseigné',
            'city' => $listing->city ?? 'non renseigné',
            'department' => $listing->department ?? 'non renseigné',
            'is_camera' => $listing->is_camera ?? 'non renseigné',
            'is_accepted_animal' => $listing->is_accepted_animal ?? 'non renseigné',
            'is_animal_exist' => $listing->is_animal_exist ?? 'non renseigné',
            'interior_regulation' => $listing->interior_regulation ?? 'non renseigné',
            'telephone' => $listing->telephone ?? 'non renseigné',
            'code_pays' => $listing->code_pays ?? 'non renseigné',
            'surface' => $listing->surface ?? 'non renseigné',
            'price' => $listing->price ?? 'non renseigné',
            'status' => $listing->status ?? 'non renseigné',
            'arrived_independently' => $listing->arrived_independently ?? 'non renseigné',
            'is_instant_reservation' => $listing->is_instant_reservation ?? 'non renseigné',
            'minimum_duration' => $listing->minimum_duration ?? 'non renseigné',
            'time_before_reservation' => $listing->time_before_reservation ?? 'non renseigné',
            'cancelation_condition' => $listing->cancelation_condition ?? 'non renseigné',
            'departure_instruction' => $listing->departure_instruction ?? 'non renseigné',
            'is_accept_arm' => $listing->is_accept_arm ?? 'non renseigné',
            'is_accept_noise' => $listing->is_accept_noise ?? 'non renseigné',
            'is_accept_smoking' => $listing->is_accept_smoking ?? 'non renseigné',
            'is_accept_chill' => $listing->is_accept_chill ?? 'non renseigné',
            'is_accept_alcool' => $listing->is_accept_alcool ?? 'non renseigné',
            'is_deleted' => $listing->is_deleted ?? 'non renseigné',
            'is_blocked' => $listing->is_blocked ?? 'non renseigné',
            'is_accept_anulation' => $listing->is_accept_anulation ?? 'non renseigné',
            'delai_partiel_remboursement' => $listing->delai_partiel_remboursement ?? 'non renseigné',
            'delai_integral_remboursement' => $listing->delai_integral_remboursement ?? 'non renseigné',
            'valeur_integral_remboursement' => $listing->valeur_integral_remboursement ?? 'non renseigné',
            'valeur_partiel_remboursement' => $listing->valeur_partiel_remboursement ?? 'non renseigné',
            'step' => $listing->step ?? 'non renseigné',
            'photos_logement' => $listing->photos->map(function ($photo) {
                if ($photo->is_verified) {
                    return [
                        'id_photo' => $photo->id,
                        'path' => $photo->path ?? 'non renseigné',
                        'extension' => $photo->extension ?? 'non renseigné',
                        'is_couverture' => $photo->is_couverture ?? 'non renseigné',
                    ];
                }
            })->filter(), // Use filter to remove null values if any
            'user' => [
                'id' => $listing->user->id ?? 'non renseigné',
                'lastname' => $listing->user->lastname ?? 'non renseigné',
                'firstname' => $listing->user->firstname ?? 'non renseigné',
                'telephone' => $listing->user->telephone ?? 'non renseigné',
                'code_pays' => $listing->user->code_pays ?? 'non renseigné',
                'email' => $listing->user->email ?? 'non renseigné',
                'country' => $listing->user->country ?? 'non renseigné',
                'file_profil' => $listing->user->file_profil ?? 'non renseigné',
                'city' => $listing->user->city ?? 'non renseigné',
                'address' => $listing->user->address ?? 'non renseigné',
                'sexe' => $listing->user->sexe ?? 'non renseigné',
                'postal_code' => $listing->user->postal_code ?? 'non renseigné',

                'created_at' => $listing->user->created_at ?? 'non renseigné',
            ],
            'categories' => $listing->housingCategoryFiles->where('is_verified', 1)->groupBy('category.name')->map(function ($categoryFiles, $categoryName) {
                return [
                    'category_id' => $categoryFiles->first()->category_id ?? 'non renseigné',
                    'category_name' => $categoryFiles->first()->category->name ?? 'non renseigné',
                    'number' => $categoryFiles->first()->number ?? 'non renseigné',
                    'photos_category' => $categoryFiles->map(function ($categoryFile) {
                        return [
                            'file_id' => $categoryFile->file->id ?? 'non renseigné',
                            'path' => $categoryFile->file->path ?? 'non renseigné',
                        ];
                    }),
                ];
            })->values(),
            "housing_note" => (new ReviewReservationController())->LogementAvecMoyenneNotesCritereEtCommentairesAcceuil($listing->id)->original['data']['overall_average'] ?? 'non renseigné',
           'is_favorite' => $userId != 0 ? DB::table('favoris')->where('user_id', $userId)->where('housing_id', $listing->id)->exists() : false


        ];
    });
}





    /**
 * @OA\Put(
 *      path="/api/logement/{housingId}/hote/disable",
 *      tags={"Dashboard hote"},
 *      security={{"bearerAuth": {}}},
 *      summary="route par laquelle l'hôte désactive son logement donné",
 *      description="Désactive un logement en fonction de son ID. Seul le propriétaire du logement peut le désactiver.",
 *      @OA\Parameter(
 *          name="housingId",
 *          in="path",
 *          required=true,
 *          description="ID du logement à désactiver",
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Succès - Logement désactivé avec succès",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Le logement a été désactivé avec succès")
 *          )
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Erreur - Seul le propriétaire du logement peut le désactiver",
 *          @OA\JsonContent(
 *              @OA\Property(property="error", type="string", example="Seul le propriétaire du logement peut le désactiver")
 *          )
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Erreur - Logement non trouvé",
 *          @OA\JsonContent(
 *              @OA\Property(property="error", type="string", example="Logement non trouvé")
 *          )
 *      )
 * )
 */

    public function disableHousing($housingId)
    {
        $housing = Housing::find($housingId);

        if (!$housing) {
            return response()->json(['error' => 'Logement non trouvé'], 404);
        }

        if ($housing->is_actif == 0) {
            return response()->json(['error' => 'Le logement est déjà desactivé'], 200);
        }

        $user = auth()->user();

        if ($housing->user_id != $user->id) {
            return response()->json(['error' => 'Seul le propriétaire du logement peut le désactiver'], 403);
        }

        $housing->is_actif = 0;
        $housing->save();

        return response()->json(['message' => 'Le logement a été désactivé avec succès'], 200);
    }

    /**
 * @OA\Put(
 *      path="/api/logement/{housingId}/hote/enable",
 *      tags={"Dashboard hote"},
 *      security={{"bearerAuth": {}}},
 *      summary="la route qui permet à l'hôte d'activer un logement",
 *      description="Active un logement en fonction de son ID. Seul le propriétaire du logement peut l'activer.",
 *      @OA\Parameter(
 *          name="housingId",
 *          in="path",
 *          required=true,
 *          description="ID du logement à activer",
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Succès - Logement activé avec succès",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Le logement a été activé avec succès")
 *          )
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Erreur - Seul le propriétaire du logement peut l'activer",
 *          @OA\JsonContent(
 *              @OA\Property(property="error", type="string", example="Seul le propriétaire du logement peut l'activer")
 *          )
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Erreur - Logement non trouvé",
 *          @OA\JsonContent(
 *              @OA\Property(property="error", type="string", example="Logement non trouvé")
 *          )
 *      )
 * )
 */
public function enableHousing($housingId)
{
    $housing = Housing::find($housingId);
    if (!$housing) {
        return response()->json(['error' => 'Logement non trouvé'], 404);
    }

    if ($housing->is_actif) {
        return response()->json(['error' => 'Le logement est déjà activé'], 200);
    }

    $currentUser = auth()->user();
    if ($housing->user_id !== $currentUser->id) {
        return response()->json(['error' => 'Seul le propriétaire du logement peut l\'activer'], 403);
    }

    $housing->is_actif = 1;
    $housing->save();

    return response()->json(['message' => 'Le logement a été activé avec succès'], 200);
}
/**
     * @OA\Delete(
     *     path="/api/logement/destroyHousingHote/{id}",
     *     summary="Suppression d un logement par l' hote",
     *     tags={"Dashboard hote"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the housing",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="housing deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Housing not found"
     *     )
     * )
     */
    public function destroyHousingHote($id){

        try{
            $housing = Housing::find($id);
            if (!$housing) {
                return response()->json(['error' => 'Housing not found.'], 404);
            }
            if(!(Auth::user()->id == $housing->user_id)){
                return response()->json(['error' => 'Vous ne pouvez pas supprimer un logement que vous n avez pas ajouté.'],);
            }
            if ($housing->is_destroy == true) {
                return response()->json(['error' => 'Logement déjà supprimé.'],);
            }
            Housing::whereId($id)->update(['is_destroy' => 1]);
            return response()->json(['data' => 'Logement supprimé avec succès'], 200);
        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }




       /**
         * @OA\Get(
         *     path="/api/logement/getHousingForHote",
         *     summary="Liste des logements d'un hote connecté",
         *     description="Liste des logements d'un hote.C'est avec cette route qu'on affichera les logement pour un hote qui est connecté dans son dashboard",
         *     tags={"Dashboard hote"},
         * security={{"bearerAuth": {}}},
         *     @OA\Response(
         *         response=200,
         *         description="List of housing what be retrieve by hote"
         *
         *     )
         * )
         */
    public function getHousingForHote(){
        try{
            $housings = Housing::where('is_destroy',0)
            ->where('is_deleted',0)
            ->where('is_blocked',0)
            ->where('is_actif',1)
            ->where('user_id',Auth::user()->id)
            ->with('user')
            ->get();
            $data = $this->formatListingsData($housings);
            return response()->json(['data' => $data], 200);
        } catch(Exception $e) {
            return response()->json($e->getMessage());
        }
    }

     /**
         * @OA\Get(
         *     path="/api/logement/getHousingDisabledByHote",
         *     summary="Liste des logements désactivé d'un hote connecté",
         *     description="Liste des désactivé logements d'un hote.C'est avec cette route qu'on affichera les logement pour un hote qui est connecté dans son dashboard",
         *     tags={"Dashboard hote"},
         * security={{"bearerAuth": {}}},
         *     @OA\Response(
         *         response=200,
         *         description="List of housing what be retrieve by hote"
         *
         *     )
         * )
         */
    public function getHousingDisabledByHote(){
        try{
            $housings = Housing::where('is_destroy',0)
            ->where('is_deleted',0)
            ->where('is_blocked',0)
            ->where('is_actif',0)
            ->where('is_finished',1)
            ->where('user_id',Auth::user()->id)
            ->with('user')
            ->get();

            $data = $this->formatListingsData($housings);


            return response()->json(['data' => $data], 200);
        } catch(Exception $e) {
            return response()->json($e->getMessage());
        }
    }

/**
 * @OA\Get(
 *     path="/api/logement/detail/getHousingStatisticAcceuil/{housing_id}",
 *     tags={"Housing"},
 *     summary="Liste de quelques statistiques utiles pour le proprietaire d'un logement donné au niveau de detail logement sur le site",
 *     description="Liste de quelques statistiques utiles pour le proprietaire d'un logement donné au niveau de detail logement sur le site",
 *     @OA\Parameter(
 *         name="housing_id",
 *         in="path",
 *         description="ID du logement à afficher",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste de quelques statistiques utiles pour le proprietaire d'un logement donné au niveau de detail logement sur le site",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Le logement spécifié n'existe pas",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Le logement spécifié n'existe pas")
 *         )
 *     )
 * )
 */
    public function getHousingStatisticAcceuil($housingId) {
        $housing = Housing::with('user')->find($housingId);

        if (!$housing) {
            return response()->json([
                'message' => 'Logement non trouvé'
            ], 404);
        }

        $owner = $housing->user;

        $totalHousingPublished = Housing::where('user_id', $owner->id)->count();

        $totalCommentsForHousing = Review_reservation::whereIn('reservation_id', function($query) use ($housingId) {
            $query->select('id')
                  ->from('reservations')
                  ->where('housing_id', $housingId);
        })->count();

        $allNotesForOwner = Housing::with('reservation.notes')
            ->where('user_id', $owner->id)
            ->get()
            ->flatMap(function ($housing) {
                return $housing->reservation->flatMap(function ($reservation) {
                    return $reservation->notes->pluck('note');
                });
            });

        $globalAverageForOwner = $allNotesForOwner->isEmpty() ? 0 : $allNotesForOwner->avg();

        return response()->json([
            'total_housing_published' => $totalHousingPublished,
            'total_avis_for_housing' => $totalCommentsForHousing,
            'global_average_for_user' => $globalAverageForOwner,

        ]);
    }

   /**
 * @OA\Get(
 *     path="/api/logement/available_at_date",
 *     summary="Liste des logements disponibles à une date donnée",
 *     description="Renvoie la liste des logements disponibles à une date donnée, en tenant compte du délai de 'time before reservation'.",
 *     tags={"Housing"},
 *     @OA\Parameter(
 *         name="date",
 *         in="query",
 *         description="Date pour laquelle vérifier la disponibilité des logements (format: YYYY-MM-DD)",
 *         required=true,
 *         @OA\Schema(type="string", format="date"),
 *     ),
 * @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Numéro de page",
 *         required=true,
 *         @OA\Schema(type="string", format="integer"),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements disponibles à la date donnée",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Appartement Luxueux"),
 *                 @OA\Property(property="address", type="string", example="123 Rue de Paris"),
 *                 @OA\Property(property="city", type="string", example="Paris"),
 *                 @OA\Property(property="country", type="string", example="France"),
 *                 @OA\Property(property="price", type="number", example=120.5),
 *             ),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erreur de format de date",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Format de date invalide. Utilisez YYYY-MM-DD."),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucune donnée trouvée",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Aucun logement trouvé pour la date donnée."),
 *         ),
 *     ),
 * )
 */


 public function getAvailableHousingsAtDate(Request $request)
{
    $date = $request->query('date');

    if (empty($date)) {
        return (new ServiceController())->apiResponse(404, [], 'La date est obligatoire. Veuillez fournir une date au format YYYY-MM-DD.');
    }

    try {
        $targetDate = Carbon::parse($date);
    } catch (Exception $e) {
        return (new ServiceController())->apiResponse(400, [], 'Format de date invalide. Utilisez YYYY-MM-DD.');
    }

    if ($targetDate < Carbon::now()->startOfDay()) {
        return (new ServiceController())->apiResponse(400, [], 'La date ne peut pas être antérieure à la date actuelle.');
    }

    if (!$request->page) {
        return (new ServiceController())->apiResponse(404, [], "Le numéro de page est obligatoire");
    }

    $page = intval($request->query('page', 1));
    $perPage = Setting::first()->pagination_logement_acceuil;
    $today = date('Y-m-d');

    // Récupérer les logements sponsorisés actifs
    $sponsoredHousings = DB::table('housing_sponsorings')
        ->where('is_actif', true)
        ->where('is_deleted', false)
        ->where('date_debut', '<=', $today)
        ->where('date_fin', '>=', $today)
        ->orderBy(DB::raw('prix * nombre'), 'asc')
        ->pluck('housing_id')
        ->toArray();

    // Récupérer les logements sponsorisés
    $sponsoredListings = Housing::whereIn('id', $sponsoredHousings)
        ->where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('is_finished', 1)
        ->get();

    // Récupérer les logements non sponsorisés
    $nonSponsoredListings = Housing::whereNotIn('id', $sponsoredHousings)
        ->where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('is_finished', 1)
        ->get();

    // Fusionner les logements sponsorisés et non sponsorisés en conservant l'ordre
    $allListings = $sponsoredListings->merge($nonSponsoredListings);

    // Pagination manuelle
    $totalListings = $allListings->count();
    $skip = ($page - 1) * $perPage;
    $pagedListings = $allListings->slice($skip, $perPage);

    // Vérifier la disponibilité des logements
    $availableHousings = [];
    foreach ($pagedListings as $housing) {
        $reservations = Reservation::where('housing_id', $housing->id)->get();
        $isAvailable = true;

        foreach ($reservations as $reservation) {
            $reservationEnd = Carbon::parse($reservation->date_of_end);
            $timeBeforeReservation = $housing->time_before_reservation ?? 0;
            $minimumStartDate = $reservationEnd->copy()->addDays($timeBeforeReservation);

            if (($targetDate >= $reservation->date_of_starting && $targetDate <= $reservationEnd) ||
                ($targetDate >= $reservationEnd && $targetDate <= $minimumStartDate)) {
                $isAvailable = false;
                break;
            }
        }

        if ($isAvailable) {
            $availableHousings[] = $housing;
        }
    }

    $formattedData = $this->formatListingsData(collect($availableHousings));

    return response()->json([
        'data' => $formattedData,
        'nombre' => count($formattedData),
        'current_page' => $page,
        'last_page' => ceil($totalListings / $perPage),
        'per_page' => $perPage,
    ], 200);
}


 




/**
 * @OA\Post(
 *     path="/api/logement/add/file/{housingId}",
 *     tags={"Housing Photo"},
 *     summary="Ajouter une ou plusieurs photos à un logement",
 *     description="Ajouter une ou plusieurs photos à un logement",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         required=true,
 *         description="ID du logement",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="photos[]",
 *                     type="array",
 *                     @OA\Items(type="string", format="binary", description="Image de la catégorie (JPEG, PNG, JPG, GIF, taille max : 2048)")
 *                 ),
 *                 required={"photos[]"}
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Photo(s) du logement ajoutée(s) avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Photo(s) du logement ajoutée(s) avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Erreur de validation",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="object", additionalProperties={"type": "string"})
 *         )
 *     )
 * )
 */

 public function addPhotoToHousing(Request $request, string $housingId)
 {

     try {
         $housing = Housing::find($housingId);

         if (!$housing) {
             return response()->json(['error' => 'Housing non trouvé.'], 404);
         }

         $identity_profil_url = '';
         foreach ($request->file('photos') as $index => $photo) {
            $identity_profil_url = $this->fileService->uploadFiles($photo, 'image/photo_logement', 'extensionImageVideo');
            if ($identity_profil_url['fails']) {
                return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
            }
             $type = $photo->getClientOriginalExtension();
             $photoModel = new photo();
             $photoModel->path = $identity_profil_url['result'];
             $photoModel->extension = $type;
             $photoModel->is_verified = false;
             $photoModel->housing_id = $housing->id;
             $photoModel->save();
         }

     $right = Right::where('name','admin')->first();
     $adminUsers = User_right::where('right_id', $right->id)->get();
     foreach ($adminUsers as $adminUser) {
          $mail = [
         "title" => "Ajout d'une/de nouvelle(s) photo(s) à un logement",
        "body" => "Un hote vient d'ajouter une/de nouvelle(s) photo(s) pour le logement {$housing->name}."
     ];

         dispatch( new SendRegistrationEmail($adminUser->user->email, $mail['body'], $mail['title'], 2));
       }
             return response()->json(['data' => 'Photos de logement ajouté avec succès'], 200);

   } catch (Exception $e) {
     return response()->json(['error' => $e->getMessage()], 500);
   }
}


// Fonction retournant la promotion en cours d'un logement donné à un instant t;
public function getCurrentPromotion($housingId)
{
    try {
        $currentDate = Carbon::now();

        $promotion = Promotion::where('housing_id', $housingId)
                              ->where('is_encours', true)
                              ->where('is_deleted', false)
                              ->where('is_blocked', false)
                              ->where('is_actif',true)
                              ->where('date_debut', '<=', $currentDate)
                              ->where('date_fin', '>=', $currentDate)
                              ->first();


        return response()->json([
            'data' => $promotion,
        ], 200);

    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function getCurrentReductions($housingId)
{

    $ongoingReductions = reduction::where('housing_id', $housingId)
        ->where('is_encours', 1)
        ->where('is_deleted', false)
        ->where('is_blocked', false)
        ->where('is_actif',true)
               ->get();

    return response()->json([
        'data' => $ongoingReductions,
    ], 200);
}

    /**
     * @OA\Get(
     *     path="/api/logement/photos/unverified",
     *     summary="Obtenir la liste des photos des logements en attente de validation",
     *     tags={"Housing Photo"},
    *  security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des photos en attente de validation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="ID de la photo"),
     *                 @OA\Property(property="path", type="string", description="Chemin de la photo"),
     *                 @OA\Property(property="extension", type="string", description="Extension de la photo"),
     *                 @OA\Property(property="is_couverture", type="boolean", description="Photo de couverture"),
     *                 @OA\Property(property="housing_id", type="integer", description="ID du logement associé"),
     *                 @OA\Property(property="is_verified", type="boolean", description="Statut de vérification"),
     *                 @OA\Property(property="housing", type="object", description="Détails du logement associé",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="owner", type="object", description="Propriétaire du logement",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="email", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucune photo en attente de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Aucune photo trouvée")
     *         )
     *     )
     * )
     */

public function getUnverifiedPhotos()
{
    $unverifiedPhotos = Photo::where('is_verified', false)
        ->with(['housing.user'])
        ->where('is_deleted', false)
        ->where('is_blocked', false)
        ->get();

    if ($unverifiedPhotos->isEmpty()) {
        return response()->json([
            'message' => 'Aucune photo en attente de validation.',
        ], 404);
    }

    return response()->json([
        'message' => 'Photos en attente de validation récupérées avec succès.',
        'photos' => $unverifiedPhotos,
    ], 200);
}

 /**
     * @OA\Put(
     *     path="/api/logement/photos/validate/{photoId}",
     *     summary="Valider une photo du logement par son ID",
     *     tags={"Housing Photo"},
     *  security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="photoId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID de la photo à valider"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo validée avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="photo", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="is_verified", type="boolean"),
     *                 @OA\Property(property="housing_id", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Photo non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
public function validatePhoto(Request $request, $photoId)
{
    try {
        $photo = Photo::findOrFail($photoId);

        $photo->is_verified = true;
            $photo->save();

        return response()->json([
            'message' => 'Photo validée avec succès.',
            'photo' => $photo,
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'message' => 'Photo non trouvée avec cet ID.',
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Une erreur s\'est produite lors de la validation de la photo.',
        ], 500);
    }
}

/**
 * @OA\Get(
 *     path="/api/logement/available_between_dates",
 *     summary="Liste des logements disponibles entre un intervalle de dates",
 *     description="Renvoie la liste des logements disponibles entre deux dates, en tenant compte du délai de 'time before reservation'.",
 *     tags={"Housing"},
 *     @OA\Parameter(
 *         name="start_date",
 *         in="query",
 *         description="Date de début pour vérifier la disponibilité des logements (format: YYYY-MM-DD)",
 *         required=true,
 *         @OA\Schema(type="string", format="date"),
 *     ),
 *     @OA\Parameter(
 *         name="end_date",
 *         in="query",
 *         description="Date de fin pour vérifier la disponibilité des logements (format: YYYY-MM-DD)",
 *         required=true,
 *         @OA\Schema(type="string", format="date"),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements disponibles entre les dates données",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Appartement Luxueux"),
 *                 @OA\Property(property="address", type="string", example="123 Rue de Paris"),
 *                 @OA\Property(property="city", type="string", example="Paris"),
 *                 @OA\Property(property="country", type="string", example="France"),
 *                 @OA\Property(property="price", type="number", example=120.5),
 *             ),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erreur de format de date",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Format de date invalide. Utilisez YYYY-MM-DD."),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement trouvé entre les dates données",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Aucun logement trouvé entre les dates données."),
 *         ),
 *     ),
 * )
 */
public function getAvailableHousingsBetweenDates(Request $request)
{

    $startDateParam = $request->query('start_date');
    $endDateParam = $request->query('end_date');

    if (empty($startDateParam) || empty($endDateParam)) {
        return response()->json([
            'message' => 'Les dates de début et de fin sont obligatoires. Veuillez fournir les dates au format YYYY-MM-DD.'
        ], 200);
    }

    try {
        $startDate = Carbon::parse($startDateParam);
        $endDate = Carbon::parse($endDateParam);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Format de date invalide. Utilisez YYYY-MM-DD.'
        ], 200);
    }


    if ($startDate > $endDate) {
        return response()->json([
            'message' => 'La date de début ne peut pas être postérieure à la date de fin.'
        ], 200);
    }

    if ($startDate < Carbon::now()->startOfDay()) {
        return response()->json([
            'message' => 'La date de début ne peut pas être antérieure à la date actuelle.'
        ], 200);
    }

    $allHousings = Housing::where('status', 'verified')
    ->where('is_deleted', 0)
            ->where('is_blocked', 0)
    ->where('is_updated', 0)
    ->where('is_actif', 1)
    ->where('is_destroy', 0)
    ->where('is_finished', 1)
            ->get();
    $availableHousings = [];

    foreach ($allHousings as $housing) {
        $reservations = Reservation::where('housing_id', $housing->id)->get();
        $isAvailable = true;

        foreach ($reservations as $reservation) {
            $reservationStart = Carbon::parse($reservation->date_of_starting);
            $reservationEnd = Carbon::parse($reservation->date_of_end);

            $timeBeforeReservation = $housing->time_before_reservation ?? 0;
            $minimumStartDate = $reservationEnd->copy()->addDays($timeBeforeReservation);
            if (($startDate <= $reservationEnd && $endDate >= $reservationStart) ||
                ($endDate >= $reservationEnd && $startDate <= $minimumStartDate)) {
                $isAvailable = false;
                break;
            }
        }

        if ($isAvailable) {
            $availableHousings[] = $housing;
        }
    }

    if (count($availableHousings) === 0) {
        return response()->json([
            'message' => 'Aucun logement trouvé entre les dates données.'
        ], 404);
    }

    $formattedData = $this->formatListingsData(collect($availableHousings));

    return response()->json(['data' => $formattedData], 200);
}

public function getOrDefault($input, $default = 'XX') {
    return empty($input) ? $default : $input;
}





    /**
 * @OA\Get(
 *     path="/api/logement/liste/notFinished",
 *     tags={"Dashboard hote"},
 *  security={{"bearerAuth": {}}},
 *     summary="Liste des logements non rempli complètement par l'hôte connecté",
 *     description="Récupère la liste des logements des logements non rempli complètement par l'hôte connecté.",
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements non rempli complètement par l'hôte connecté",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Liste des logements non rempli complètement par l'hôte connecté"
 *     )
 * )
 */
public function HousingHoteInProgress(){
    $listings = Housing::where('user_id', Auth::user()->id)
        ->where('is_finished', 0)
        ->get();
        $data = $this->formatListingsData($listings);
        return response()->json(['data' => $data,'nombre'=>$data->count()],200);
}


    /**
     * @OA\Post(
     *     path="/api/logement/block/{housinId}",
     *     summary="Bloque un logement",
     * security={{"bearerAuth": {}}},
     *     description="Marque un logement comme bloqué.",
     *     operationId="blockHousing",
     *     tags={"Housing"},
     *     @OA\Parameter(
     *         name="housinId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="ID du logement à bloquer"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Logement bloqué avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Logement bloqué avec succès"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Le logement spécifié n'existe pas",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Le logement spécifié n'existe pas")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Détails de l'erreur")
     *         )
     *     )
     * )
     */
    public function block($housinId){
        try {
            $housing = Housing::find($housinId);
            if (!$housing) {
                return (new ServiceController())->apiResponse(404,[],'Le logement spécifié n\'existe pas');
            }
            if($housing->is_blocked == true){
                return (new ServiceController())->apiResponse(200,[],'Logement déjà bloqué');
            }
            $housing->is_blocked = true;
            $housing->save();
            return (new ServiceController())->apiResponse(200,[],'Logement bloqué avec succès');

        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


    /**
 * @OA\Post(
 *     path="/api/logement/unblock/{housinId}",
 *     summary="Débloque un logement",
 *     description="Marque un logement comme débloqué.",
 *     operationId="unblockHousing",
 *     tags={"Housing"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="housinId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         ),
 *         description="ID du logement à débloquer"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Logement débloqué avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="message", type="string", example="Logement débloqué avec succès"),
 *             @OA\Property(property="data", type="array", @OA\Items())
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Le logement spécifié n'existe pas",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="integer", example=404),
 *             @OA\Property(property="message", type="string", example="Le logement spécifié n'existe pas")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="integer", example=500),
 *             @OA\Property(property="message", type="string", example="Détails de l'erreur")
 *         )
 *     )
 * )
 */


    public function unblock($housinId){
        try {
            $housing = Housing::find($housinId);
            if (!$housing) {
                return response()->json(['message' => 'Le logement spécifié n\'existe pas'], 404);
            }
            if($housing->is_blocked == false){
                return (new ServiceController())->apiResponse(200,[],'Logement déjà débloqué');
            }

            $housing->is_blocked = false;
            $housing->save();
            return (new ServiceController())->apiResponse(200,[],'Logement débloqué avec succès');

        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logement/delete/{housinId}",
     *     summary="Bloque un logement",
     * security={{"bearerAuth": {}}},
     *     description="Marque un logement comme supprimé.",
     *     operationId="deleteHousing",
     *     tags={"Housing"},
     *     @OA\Parameter(
     *         name="housinId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="ID du logement à supprimé"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Logement supprimé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Logement supprimé avec succès"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Le logement spécifié n'existe pas",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Le logement spécifié n'existe pas")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Détails de l'erreur")
     *         )
     *     )
     * )
     */
    public function delete($housinId){
        try {
            $housing = Housing::find($housinId);
            if (!$housing) {
                return response()->json(['message' => 'Le logement spécifié n\'existe pas'], 404);
            }
            if($housing->is_deleted == true){
                return (new ServiceController())->apiResponse(200,[],'Logement déjà supprimé');
            }
            $housing->is_deleted = true;
            $housing->save();
            return (new ServiceController())->apiResponse(200,[],'Logement supprimé avec succès');

        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }



      /**
         * @OA\Get(
         *     path="/api/logement/getHousingForHotesimple",
         *     summary="Liste des logements d'un hote connecté(Information basique)",
         *     description="Liste des logements d'un hote.Route sui donne des informations basiques ",
         *     tags={"Dashboard hote"},
         * security={{"bearerAuth": {}}},
         *     @OA\Response(
         *         response=200,
         *         description="List of housing what be retrieve by hote"
         *
         *     )
         * )
         */
        public function getHousingForHotesimple()
        {
            try {
                $housings = Housing::where('is_destroy', 0)
                    ->where('is_deleted', 0)
                    ->where('is_blocked', 0)
                     ->where('is_finished', 1)
                    ->where('user_id', Auth::user()->id)
                    ->with(['photos' => function ($query) {
                        $query->where('is_couverture', 1)->select('housing_id', 'path');
                    }])
                    ->get(['id', 'name']);

                $result = $housings->map(function ($housing) {
                    return [
                        'housing_id' => $housing->id,
                        'name' => $housing->name,
                            'path' => $housing->photos->isNotEmpty() ? $housing->photos[0]->path : null,
                    ];
                });
          return (new ServiceController())->apiResponse(200,$result,'ok');

            } catch (Exception $e) {
                return (new ServiceController())->apiResponse(500, [], $e->getMessage());

            }
        }


 /**
 * @OA\Get(
 *     path="/api/logement/getHousingSensibleOrInsensibleDetail/{housingId}",
 *     summary="Récupérer les détails d'un logement",
 *     description="Récupérer les détails d'un logement en fonction de l'ID du logement. Les informations sensibles ou non dépendront du paramètre `isSensible`.",
 *     tags={"Housing"},
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         description="ID du logement à récupérer",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="isSensible",
 *         in="query",
 *         description="Spécifie si les informations sensibles doivent être incluses",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Succès - Détails du logement récupérés",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Appartement cosy"),
 *             @OA\Property(property="description", type="string", example="Appartement avec vue sur mer"),
 *             @OA\Property(property="number_of_bed", type="integer", example=2),
 *             @OA\Property(property="number_of_traveller", type="integer", example=4),
 *             @OA\Property(property="is_camera", type="boolean", example=true),
 *             @OA\Property(property="is_accepted_animal", type="boolean", example=true),
 *             @OA\Property(property="is_animal_exist", type="boolean", example=false),
 *             @OA\Property(property="is_instant_reservation", type="boolean", example=true),
 *             @OA\Property(property="maximum_duration", type="integer", example=7),
 *             @OA\Property(property="minimum_duration", type="integer", example=2),
 *             @OA\Property(property="interior_regulation", type="string", example="Pas de bruit après 22h"),
 *             @OA\Property(property="telephone", type="string", example="+33123456789"),
 *             @OA\Property(property="code_pays", type="string", example="FR"),
 *             @OA\Property(property="arrived_independently", type="boolean", example=true),
 *             @OA\Property(property="cancelation_condition", type="string", example="Annulation gratuite 24h avant l'arrivée"),
 *             @OA\Property(property="departure_instruction", type="string", example="Fermer les fenêtres avant de partir"),
 *             @OA\Property(property="surface", type="integer", example=50),
 *             @OA\Property(property="price", type="number", example=120.50)
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Logement non trouvé",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Le logement spécifié n'existe pas")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur interne",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
 *         )
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */
        public function  getHousingSensibleOrInsensibleDetail(Request $request, $housinId){
            try {

                $isSensible = $request->query('isSensible') ;

                // return $isSensible;


                $housing = Housing::whereId($housinId)->first();

                if (!$housing) {
                    return response()->json(['message' => 'Le logement spécifié n\'existe pas'], 404);
                    return (new ServiceController())->apiResponse(200,$result, 'Le logement spécifié n\'existe pas');
                }

                $insensitiveFields = [
                    "name",
                    "description",
                    "number_of_bed",
                    "number_of_traveller",
                    "is_camera",
                    "is_accepted_animal",
                    "is_animal_exist",
                    "is_instant_reservation",
                    "maximum_duration",
                    "minimum_duration"
                ];

                $sensitiveFields = [
                    "interior_regulation",
                    "interior_regulation_pdf",
                    "telephone",
                    "code_pays",
                    "arrived_independently",
                    "cancelation_condition",
                    "departure_instruction",
                    "surface",
                    "price"
                ];

                $data = [];

                $data['id'] = $housing->id;

                if ($isSensible) {
                    $message = 'Données sensibles';
                    foreach ($sensitiveFields as $field) {
                        $data[$field] = $housing->$field ?? 'non renseigné';
                    }
                }else{
                    $message = 'Données insensibles';
                    foreach ($insensitiveFields as $field) {
                        $data[$field] = $housing->$field ?? 'non renseigné';
                    }
                }
                return (new ServiceController())->apiResponse(200,$data,$message);

            } catch (Exception $e) {
                return (new ServiceController())->apiResponse(500, [], $e->getMessage());
            }
        }


        /**
 * @OA\Get(
 *     path="/api/logement/filterby/destination/{location}/available_between_dates",
 *     tags={"Housing"},
 *     summary="Liste des logements disponibles filtrée par destination et période",
 *     description="Récupère la liste des logements disponibles pour une destination donnée et entre deux dates spécifiées.",
 *     @OA\Parameter(
 *         name="location",
 *         in="path",
 *         description="Destination (pays, ville ou département)",
 *         required=true,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="start_date",
 *         in="query",
 *         description="Date de début de la période (au format YYYY-MM-DD)",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             format="date",
 *             example="2024-09-01"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="end_date",
 *         in="query",
 *         description="Date de fin de la période (au format YYYY-MM-DD)",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             format="date",
 *             example="2024-09-10"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Le numéro de la page pour la pagination",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *             example=1
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements disponibles pour la destination et la période spécifiées.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Appartement moderne"),
 *                     @OA\Property(property="city", type="string", example="Paris"),
 *                     @OA\Property(property="country", type="string", example="France"),
 *                     @OA\Property(property="price_per_night", type="number", format="float", example=120.50),
 *                     @OA\Property(property="availability", type="boolean", example=true)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Paramètres invalides ou manquants",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Les dates de début et de fin sont obligatoires. Veuillez fournir les dates au format YYYY-MM-DD."
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun logement disponible trouvé pour la destination et la période spécifiées.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Aucun logement disponible trouvé pour la destination et la période spécifiées."
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
 *                 type="string",
 *                 example="Erreur interne du serveur."
 *             )
 *         )
 *     )
 * )
 */


    public function ListeDesLogementsFilterByDestinationavailable_between_dates(Request $request,$location){
         try {

    if (!$request->page) {
            return (new ServiceController())->apiResponse(404, [], "Le numéro de page est obligatoire");
        }

    $startDateParam = $request->query('start_date');
    $endDateParam = $request->query('end_date');

    if (empty($startDateParam) || empty($endDateParam)) {
        return response()->json([
            'message' => 'Les dates de début et de fin sont obligatoires. Veuillez fournir les dates au format YYYY-MM-DD.'
        ], 400);
    }

    try {
        $startDate = Carbon::parse($startDateParam);
        $endDate = Carbon::parse($endDateParam);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Format de date invalide. Utilisez YYYY-MM-DD.'
        ], 400);
    }

    if ($startDate > $endDate) {
        return response()->json([
            'message' => 'La date de début ne peut pas être postérieure à la date de fin.'
        ], 400);
    }

    if ($startDate < Carbon::now()->startOfDay()) {
        return response()->json([
            'message' => 'La date de début ne peut pas être antérieure à la date actuelle.'
        ], 400);
    }

    $page = intval($request->query('page', 1));
    $perPage = Setting::first()->pagination_logement_acceuil;

    $query = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('is_finished', 1)
        ->where(function($q) use ($location) {
            $q->where('country', $location)
              ->orWhere('city', $location)
              ->orWhere('department', $location);
        });

    $allHousings = $query->get();
    $availableHousings = [];

    foreach ($allHousings as $housing) {
        $reservations = Reservation::where('housing_id', $housing->id)->get();
        $isAvailable = true;

        foreach ($reservations as $reservation) {
            $reservationStart = Carbon::parse($reservation->date_of_starting);
            $reservationEnd = Carbon::parse($reservation->date_of_end);

            $timeBeforeReservation = $housing->time_before_reservation ?? 0;
            $minimumStartDate = $reservationEnd->copy()->addDays($timeBeforeReservation);

            if (($startDate <= $reservationEnd && $endDate >= $reservationStart) ||
                ($endDate >= $reservationEnd && $startDate <= $minimumStartDate)) {
                $isAvailable = false;
                break;
            }
        }

        if ($isAvailable) {
            $availableHousings[] = $housing;
        }
    }

    if (count($availableHousings) === 0) {
        return response()->json([
            'message' => 'Aucun logement disponible trouvé pour la destination et la période spécifiées.'
        ], 404);
    }

    $paginatedHousings = collect($availableHousings)->forPage($page, $perPage);
    $formattedData = $this->formatListingsData($paginatedHousings);

    return response()->json(['data' => $formattedData], 200);

            } catch (Exception $e) {
                return (new ServiceController())->apiResponse(500, [], $e->getMessage());
            }
    }

}
