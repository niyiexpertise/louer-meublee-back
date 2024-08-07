<?php

namespace App\Http\Controllers;
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
use App\Models\UserVisiteHousing;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Exception;
class HousingController extends Controller
{

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
         foreach ($request->file('photos') as $index => $photo) {
             $photoName = uniqid() . '.' . $photo->getClientOriginalExtension();
             $photoPath = $photo->move(public_path('image/photo_logement'), $photoName);
             //$photoUrl = url('/image/photo_logement/' . $photoName);
             $ip='http://192.168.100.158:8000';
             $photoUrl = $ip.'/image/photo_logement/' . $photoName;
             $type = $photo->getClientOriginalExtension();
             $photoModel = new photo();
             $photoModel->path = $photoUrl;
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
        foreach ($photoFiles as $fileId) {
            $photoModel = new File();
            $photoName = uniqid() . '.' . $fileId->getClientOriginalExtension();
            $photoPath = $fileId->move(public_path('image/photo_category'), $photoName);
        //$photoUrl = url('/image/photo_category/' . $photoName);
                        $ip='http://192.168.100.158:8000';
                 $photoUrl =$ip.'/image/photo_category/' . $photoName;
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

        foreach ($categoryPhotos as $photoFile) {
            $photoName = uniqid() . '.' . $photoFile->getClientOriginalExtension();
            $photoPath = $photoFile->move(public_path('image/photo_category'), $photoName);
                        $photoUrl = url('/image/photo_category/' . $photoName);
                        $ip='http://192.168.100.158:8000';
                        $photoUrl =$ip.'/image/photo_category/' . $photoName;
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
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('is_finished', 1)
        ->get();
        $data = $this->formatListingsData($listings);
               
        $controllervisitesite=App::make('App\Http\Controllers\UserVisiteSiteController');
        if ($request->has('user_id') ) {
            $user_id= $request->input('user_id');
            $insertvisite=$controllervisitesite->recordSiteVisit($user_id);
                }else{
                    $insertvisite=$controllervisitesite->recordSiteVisit();
                }
       

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
     $promotion=$this->getCurrentPromotion($id);
     $reduction=$this->getCurrentReductions($id);
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
         'telephone' => $listing->telephone,
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
         'is_accept_anulation'=> $listing->is_accept_anulation,
         'delai_partiel_remboursement'=> $listing->delai_partiel_remboursement,
         'delai_integral_remboursement'=> $listing->delai_integral_remboursement,
         'valeur_integral_remboursement'=> $listing->valeur_integral_remboursement,
         'valeur_partiel_remboursement'=> $listing->valeur_partiel_remboursement,
 
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
             'telephone' => $listing->user->telephone,
             'code_pays' => $listing->user->code_pays,
             'email' => $listing->user->email,
             'country' => $listing->user->country,
             'file_profil' => $listing->user->file_profil,
             'city' => $listing->user->city,
             'address' => $listing->user->address,
             'sexe' => $listing->user->sexe,
             'postal_code' => $listing->user->postal_code,
             'is_admin' => $listing->user->is_admin,
             'is_traveller' => $listing->user->is_traveller,
             'is_hote' => $listing->user->is_hote,
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
 
         'reductions' =>$reduction->original['data'],
 
         'promotions' => $promotion->original['data'],
 
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

 public function  ListeDesLogementsAcceuilFilterByTypehousing($id)
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('housing_type_id', $id)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('is_finished', 1)
        ->get();
    
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
    public function  ListeDesLogementsAcceuilFilterByTypeproperty($id)
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('is_finished', 1)
                ->where('property_type_id', $id)
        ->get();

        $data = $this->formatListingsData($listings);

        return response()->json(['data' => $data],200);
    }

 
 /**
 * @OA\Get(
 *     path="/api/logement/filterby/country/{country}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par Pays",
 *     description="Récupère la liste des logements filtrée par le paysspécifié.",
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
    public function  ListeDesLogementsFilterByCountry($country)
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('is_finished', 1)
        ->where('country', $country)
        ->get();
    
        $data = $this->formatListingsData($listings);
        return response()->json(['data' => $data],200);
      }

/**
 * @OA\Get(
 *     path="/api/logement/filterby/preference/{preference_id}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par preference",
 *     description="Récupère la liste des logements filtrée par le id preference spécifié.",
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
public function  ListeDesLogementsAcceuilFilterByPreference($preferenceId)
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('is_finished', 1)
        ->whereHas('housing_preference', function ($query) use ($preferenceId) {
            $query->where('preference_id', $preferenceId);
        })
        ->get();
    
        $data = $this->formatListingsData($listings);
        return response()->json(['data' => $data],200);
    }
/**
 * @OA\Get(
 *     path="/api/logement/filterby/city/{city}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par ville",
 *     description="Récupère la liste des logements filtrée par villespécifié.",
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
 public function  ListeDesLogementsFilterByCity($city)
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('is_finished', 1)
        ->where('city', $city)
        ->get();
    
        $data = $this->formatListingsData($listings);
        return response()->json(['data' => $data],200);
 }

/**
 * @OA\Get(
 *     path="/api/logement/filterby/departement/{departement}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par departement",
 *     description="Récupère la liste des logements filtrée par departement spécifié.",
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
 public function  ListeDesLogementsFilterByDepartement($department)
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('is_finished', 1)
        ->where('department', $department)
        ->get();
        $data = $this->formatListingsData($listings);
        return response()->json(['data' => $data],200);
 }
/**
 * @OA\Get(
 *     path="/api/logement/filterby/nbtraveler/{nbtraveler}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par nbtraveler",
 *     description="Récupère la liste des logements filtrée par nbtraveler spécifié.",
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
 public function  ListeDesLogementsAcceuilFilterNbtravaller($nbtravaler)
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('is_finished', 1)
        ->where('number_of_traveller', $nbtravaler)
        ->get();
    
        $data = $this->formatListingsData($listings);

        return response()->json(['data' => $data],200);
    }

    /**
 * @OA\Get(
 *     path="/api/logement/filterby/nightpricemax/{price}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par prix de nuit(Maximum)",
 *     description="Récupère la liste des logements filtrée par un prix de nuit inférieur au montant spécifié.",
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
public function getListingsByNightPriceMax($price)
{
    $listings = Housing::where('price', '<=', $price)
    ->where('status', 'verified')
    ->where('is_deleted', 0)
    ->where('is_blocked', 0)
    ->where('is_updated', 0)
    ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('is_finished', 1)
    ->get();

    $data = $this->formatListingsData($listings);

    return response()->json(['data' => $data], 200);
}

   /**
 * @OA\Get(
 *     path="/api/logement/filterby/nightpricemin/{price}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par prix de nuit (Minimum)",
 *     description="Récupère la liste des logements filtrée par un prix de nuit supérieur au montant spécifié.",
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
public function getListingsByNightPriceMin($price)
{
    $listings = Housing::where('price', '>=', $price)
    ->where('status', 'verified')
    ->where('is_deleted', 0)
    ->where('is_blocked', 0)
    ->where('is_updated', 0)
    ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('is_finished', 1)
    ->get();

    $data = $this->formatListingsData($listings);

    return response()->json(['data' => $data], 200);
}

 /**
 * @OA\Get(
 *     path="/api/logement/filterby/hote/{UserID}",
 *     tags={"Housing"},
 *     summary="Liste des logements filtrée par user",
 *     description="Récupère la liste des logements pour un User donné.",
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
 public function ListeDesLogementsFilterByHote($userId)
    {
        $listings = Housing::where('status', 'verified')
        ->where('is_deleted', 0)
        ->where('is_blocked', 0)
        ->where('is_updated', 0)
        ->where('is_actif', 1)
        ->where('is_destroy', 0)
        ->where('user_id', $userId)
        ->where('is_finished', 1)
        ->get();
        $data = $this->formatListingsData($listings);
        return response()->json(['data' => $data,'nombre'=>$data->count()],200);
 }
 
/**
 * @OA\Put(
 *     path="/api/logement/update/sensible/{id}",
 *     tags={"Housing"},
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
 
     $housing->update($validatedData);
 
     $housing->is_updated = true;
     $housing->save();
 
     $notificationText = "Le logement avec ID: $id a été mis à jour. Veuillez valider la mise à jour dès que possible.";
 
     $adminUsers = User::where('is_admin', 1)->get();
 
     foreach ($adminUsers as $adminUser) {
         $notification = new Notification([
             'name' => $notificationText,
             'user_id' => $adminUser->id,
         ]);
         $notification->save();
 
         $mail = [
             'title' => 'Mise à jour de logement',
             'body' => "Un logement avec ID: $id a été mis à jour. Veuillez valider la mise à jour dès que possible.",
         ];

         Mail::to($adminUser->email)->send(new NotificationEmailwithoutfile($mail));
     }
 
     return response()->json(['message' => 'Logement mis à jour avec succès'], 200);
 }
 


/**
 * @OA\Put(
 *     path="/api/logement/update/insensible/{id}",
 *     tags={"Housing"},
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

    $housing->update($validatedData);
    $housing->is_updated = false;
    $housing->save();

    return response()->json(['message' => 'Logement mis à jour avec succès'], 200);
}




public function formatListingsData($listings)
    {
        return $listings->map(function ($listing) {
            return [
                'id_housing' => $listing->id,
                'housing_type_id' => $listing->housing_type_id,
                'housing_type_name' => $listing->housingType->name,
                'property_type_id' => $listing->property_type_id,
                'property_type_name' => $listing->propertyType->name,
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
                'interior_regulation' => $listing->interior_regulation,
                'telephone' => $listing->telephone,
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
                'is_accept_anulation'=> $listing->is_accept_anulation,
                'delai_partiel_remboursement'=> $listing->delai_partiel_remboursement,
                'delai_integral_remboursement'=> $listing->delai_integral_remboursement,
                'valeur_integral_remboursement'=> $listing->valeur_integral_remboursement,
                'valeur_partiel_remboursement'=> $listing->valeur_partiel_remboursement,
                
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
                    'telephone' => $listing->user->telephone,
                    'code_pays' => $listing->user->code_pays,
                    'email' => $listing->user->email,
                    'country' => $listing->user->country,
                    'file_profil' => $listing->user->file_profil,
                    'city' => $listing->user->city,
                    'address' => $listing->user->address,
                    'sexe' => $listing->user->sexe,
                    'postal_code' => $listing->user->postal_code,
                    'is_admin' => $listing->user->is_admin,
                    'is_traveller' => $listing->user->is_traveller,
                    'is_hote' => $listing->user->is_hote,
                ],
                'categories' => $listing->housingCategoryFiles->where('is_verified', 1)->groupBy('category.name')->map(function ($categoryFiles, $categoryName) {
                    return [
                        'category_id' => $categoryFiles->first()->category_id,
                        'category_name' => $categoryFiles->first()->category->name,
                        'number' => $categoryFiles->first()->number,
                        'photos_category' => $categoryFiles->map(function ($categoryFile) {
                            return [
                                'file_id' => $categoryFile->file->id,
                                'path' => $categoryFile->file->path,
                            ];
                        }),
                    ];
                })->values(),
                "housing_note" => (new ReviewReservationController())->LogementAvecMoyenneNotesCritereEtCommentairesAcceuil($listing->id)->original['data']['overall_average']
            ];
        });
    }

    /**
 * @OA\Put(
 *      path="/api/logement/{housingId}/hote/disable",
 *      tags={"Housing"},
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
    
        if ($housing->is_disponible != 1) {
            return response()->json(['error' => 'Le logement est en cours de réservation et ne peut pas être désactivé'], 200);
        }
        if ($housing->is_active == 0) {
            return response()->json(['error' => 'Le logement était déjà desactivé'], 200);
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
 *      tags={"Housing"},
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

    if ($housing->is_active) {
        return response()->json(['error' => 'Le logement est déjà activé'], 200);
    }

    $currentUser = auth()->user();
    if ($housing->user_id !== $currentUser->id) {
        return response()->json(['error' => 'Seul le propriétaire du logement peut l\'activer'], 403);
    }

    $housing->is_active = 1;
    $housing->save();

    return response()->json(['message' => 'Le logement a été activé avec succès'], 200);
}
/**
     * @OA\Delete(
     *     path="/api/logement/destroyHousingHote/{id}",
     *     summary="Suppression d un logement par l' hote",
     *     tags={"Housing"},
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
         *     tags={"Housing"},
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
            ->where('user_id',Auth::user()->id)
            ->with('user')
            ->get();
            return response()->json(['data' => $housings], 200);
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
            'user' => $housing->user
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

 public function getAvailableHousingsAtDate(Request $request) {

    $date = $request->query('date');
    
    if (empty($date)) {
        return response()->json([
            'message' => 'La date est obligatoire. Veuillez fournir une date au format YYYY-MM-DD.'
        ], 200);
    }

    try {
        $targetDate = Carbon::parse($date);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Format de date invalide. Utilisez YYYY-MM-DD.'
        ], 200);
    }

    if ($targetDate < Carbon::now()->startOfDay()) {
        return response()->json([
            'message' => 'La date ne peut pas être antérieure à la date actuelle.'
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

    return response()->json(['data' => $formattedData], 200);
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

         foreach ($request->file('photos') as $index => $photo) {
             $photoName = uniqid() . '.' . $photo->getClientOriginalExtension();
             $photoPath = $photo->move(public_path('image/photo_logement'), $photoName);
             $photoUrl = url('/image/photo_logement/' . $photoName);
             $type = $photo->getClientOriginalExtension();
             $photoModel = new photo();
             $photoModel->path = $photoUrl;
             $photoModel->extension = $type;
             $photoModel->is_verified = false;
             $photoModel->housing_id = $housing->id;
             $photoModel->save();
         }

     $right = Right::where('name','admin')->first();
     $adminUsers = User_right::where('right_id', $right->id)->get();
     foreach ($adminUsers as $adminUser) {
     $notification = new Notification();
     $notification->user_id = $adminUser->user_id;
     $notification->name = "Un hote vient d'ajouter une/de nouvelle(s) photo(s) pour le logement {$housing->name}.";
     $notification->save();

          $mail = [
         "title" => "Ajout d'une/de nouvelle(s) photo(s) à un logement",
        "body" => "Un hote vient d'ajouter une/de nouvelle(s) photo(s) pour le logement {$housing->name}."
     ];
    
         Mail::to($adminUser->user->email)->send(new NotificationEmailwithoutfile($mail) );
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
 *     tags={"Housing"},
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

}