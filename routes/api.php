<?php

use App\Http\Controllers\AccessibilityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HousingTypeController; 
use App\Http\Controllers\TypeStayController; 
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\PropertyTypeController;
use App\Http\Controllers\EquipementController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\User_preferenceController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\VerificationDocumentController;
use App\Http\Controllers\HousingController;
use App\Http\Controllers\AdminHousingController;
use App\Http\Controllers\FavorisController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\HousingCategoryFileController;
use App\Http\Controllers\HousingEquipmentController;
use App\Http\Controllers\HousingPreferenceController;
use App\Http\Controllers\HousingChargeController;
use App\Http\Controllers\ReviewReservationController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'data' => $request->user(),
        'role'=>$request->user()->getRoleNames()
    ]);
});

//Inscription et Connexion
Route::prefix('users')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
});



/**route nécéssitant l'authentification */

Route::middleware('auth:sanctum')->group(function () {

    //Gestion des catégories.
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('category')->group(function () {
            Route::get('/indexUnverified', [CategorieController::class, 'indexUnverified']);
            Route::get('/VerifiednotBlockDelete', [CategorieController::class, 'VerifiednotBlockDelete']);
            Route::post('/store', [CategorieController::class, 'store']);
            Route::get('/show/{id}', [CategorieController::class, 'show']);
            Route::put('/update/{id}', [CategorieController::class, 'update']);
            Route::delete('/destroy/{id}', [CategorieController::class, 'destroy']);
            Route::put('/block/{id}', [CategorieController::class, 'block']);
            Route::put('/unblock/{id}', [CategorieController::class, 'unblock']);
            Route::put('/makeVerified/{id}', [CategorieController::class, 'makeVerified']);
            Route::get('/VerifiednotBlocknotDelete', [CategorieController::class, 'VerifiednotBlocknotDelete']);
            Route::get('/VerifiedBlocknotDelete', [CategorieController::class, 'VerifiedBlocknotDelete']);
        });
    });
    

    ///Gestion des types de logement
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('housingtype')->group(function () {
            Route::post('/store', [HousingTypeController::class, 'store']);
            Route::get('/show/{id}', [HousingTypeController::class, 'show']);
            Route::put('/updateName/{id}', [HousingTypeController::class, 'updateName']);
            Route::post('/updateIcone/{id}', [HousingTypeController::class, 'updateIcone']);
            Route::put('/update/{id}', [HousingTypeController::class, 'update']);
            Route::delete('/destroy/{id}', [HousingTypeController::class, 'destroy']);
            Route::put('/block/{id}', [HousingTypeController::class, 'block']);
            Route::put('unblock/{id}', [HousingTypeController::class, 'unblock']);
            Route::get('/indexBlock', [HousingTypeController::class, 'indexBlock']);
        });
    });

    // Gestion des type de sejour
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('typestays')->group(function () {
            Route::post('/store', [TypeStayController::class, 'store']);
            Route::get('/show/{id}', [TypeStayController::class, 'show']);
            Route::put('/update/{id}', [TypeStayController::class, 'update']);
            Route::delete('/destroy/{id}', [TypeStayController::class, 'destroy']);
            Route::put('/block/{id}', [TypeStayController::class, 'block']);
            Route::put('/unblock/{id}', [TypeStayController::class, 'unblock']);
        });
    });

    //Gestion  des critères de note .
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('criteria')->group(function () {
            Route::post('/store', [CriteriaController::class, 'store']);
            Route::get('/show/{id}', [CriteriaController::class, 'show']);
            Route::put('/update/{id}', [CriteriaController::class, 'update']);
            Route::delete('/destroy/{id}', [CriteriaController::class, 'destroy']);
            Route::put('/block/{id}', [CriteriaController::class, 'block']);
            Route::put('/unblock/{id}', [CriteriaController::class, 'unblock']);
        });
    });

    //Gestion des types de role possible sous forme crud.
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('role')->group(function () {
            Route::get('/index', [RoleController::class, 'index']);
            Route::post('/store', [RoleController::class, 'store']);
            Route::get('/show/{id}', [RoleController::class, 'show']);
            Route::put('/update/{id}', [RoleController::class, 'update']);
            Route::delete('/destroy/{id}', [RoleController::class, 'destroy']);
            Route::put('/block/{id}', [RoleController::class, 'block']);
            Route::put('/unblock/{id}', [RoleController::class, 'unblock']);
        });
    });

    //Gestion des équipements.
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('equipment')->group(function () {
            Route::get('/indexAdmin', [EquipementController::class, 'indexAdmin']);
            Route::get('/indexBlock', [EquipementController::class, 'indexBlock']);
            Route::get('/VerifiedBlocknotDelete', [EquipementController::class, 'VerifiedBlocknotDelete']);
            Route::get('/VerifiednotBlocknotDelete', [EquipementController::class, 'VerifiednotBlocknotDelete']);
            Route::get('/VerifiednotBlockDelete', [EquipementController::class, 'VerifiednotBlockDelete']);
            Route::post('/store', [EquipementController::class, 'store']);
            Route::get('/show/{id}', [EquipementController::class, 'show']);
            Route::put('/updateName/{id}', [EquipementController::class, 'updateName']);
            Route::put('/updateCategory/{equipmentCategory}', [EquipementController::class, 'updateCategory']);
            Route::post('/updateIcone/{id}', [EquipementController::class, 'updateIcone']);
            Route::delete('/destroy/{id}', [EquipementController::class, 'destroy']);
            Route::put('/block/{id}', [EquipementController::class, 'block']);
            Route::put('/makeVerified/{id}', [EquipementController::class, 'makeVerified']);
            Route::put('/unblock/{id}', [EquipementController::class, 'unblock']);
            Route::get('/indexUnverified', [EquipementController::class, 'indexUnverified']);
        });
    });

    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('users')->group(function () {
            
            Route::post('/assignPermToRole/{role}/{permission}', [AuthController::class, 'assignPermToRole']);
            Route::post('/RevokePermToRole/{role}/{permission}', [AuthController::class, 'RevokePermToRole']);
            Route::get('/getUserRoles/{id}', [AuthController::class, 'getUserRoles']);
            Route::post('/assignRoleToUser/{id}/{role}', [AuthController::class, 'assignRoleToUser']);
            Route::post('/RevokeRoleToUser/{id}/{role}', [AuthController::class, 'RevokeRoleToUser']);
            Route::post('/assignPermToUser/{id}/{permission}', [AuthController::class, 'assignPermToUser']);
            Route::post('/revokePermToUser/{id}/{permission}', [AuthController::class, 'revokePermToUser']);
            Route::get('/getUserPerms/{id}', [AuthController::class, 'getUserPerms']);
            Route::get('/usersWithRole/{role}', [AuthController::class, 'usersWithRole']);
            Route::get('/usersWithRoleCount/{role}', [AuthController::class, 'usersWithRoleCount']);
            Route::get('/usersWithPerm/{permission}', [AuthController::class, 'usersWithPerm']);
            Route::get('/usersWithPermCount/{permission}', [AuthController::class, 'usersWithPermCount']);
            Route::get('/usersWithoutRole/{role}', [AuthController::class, 'usersWithoutRole']);
            Route::get('/usersWithoutRoleCount/{role}', [AuthController::class, 'usersWithoutRoleCount']);
            Route::get('/usersWithoutPerm/{permission}', [AuthController::class, 'usersWithoutPerm']);
            Route::get('/usersWithoutPermCount/{permission}', [AuthController::class, 'usersWithoutPermCount']);
            Route::get('/usersPerms', [AuthController::class, 'usersPerms']);
            Route::get('/rolesPerms/{role}', [AuthController::class, 'rolesPerms']);
            Route::get('/rolesPermsCount/{role}', [AuthController::class, 'rolesPermsCount']);
            Route::post('/switchToHote', [AuthController::class, 'switchToHote']);
            Route::post('/switchToTraveler', [AuthController::class, 'switchToTraveler']);
            Route::get('/usersRoles', [AuthController::class, 'usersRoles']);
        });
    });



    // Gestion des utilisateurs
Route::group(['middleware' => ['role:traveler']], function () {
    Route::prefix('users')->group(function () {
        Route::get('/index', [UserController::class, 'index']);
        Route::get('/show/{id}', [UserController::class, 'show']);
        Route::get('/userReviews', [UserController::class, 'userReviews']);
        Route::get('/userLanguages', [UserController::class, 'userLanguages']);
        Route::post('/update_profile_photo', [UserController::class, 'updateProfilePhoto']);
        Route::put('/update_password', [UserController::class, 'updatePassword']);
        Route::put('/update', [UserController::class, 'updateUser']);
        Route::get('/result/demande', [VerificationDocumentController::class, 'userVerificationRequests']);
    });
    Route::get('/document/document_actif', [DocumentController::class, 'document_actif']);
    Route::post('/verificationdocument/store', [VerificationDocumentController::class, 'store']);
    Route::get('/notifications/users', [NotificationController::class, 'getUserNotifications']);
});

    // Gestion des utilisateurs du côté de l'admin
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('users')->group(function () {
            Route::get('/index', [UserController::class, 'index']);
            Route::delete('/destroy/{id}', [UserController::class, 'destroy']);
            Route::put('/block/{id}', [UserController::class, 'block']);
            Route::put('/unblock/{id}', [UserController::class, 'unblock']);
            Route::get('/pays/{pays}', [UserController::class, 'getUsersByCountry']);
            Route::get('/travelers', [UserController::class, 'getUsersWithRoletraveler']);
            Route::get('/hotes', [UserController::class, 'getUsersWithRoleHost']);
            Route::get('/admins', [UserController::class, 'getUsersWithRoleAdmin']);
        });
    });



    // Gestion des permissions sous forme crud
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('permission')->group(function () {
            Route::get('/index', [PermissionController::class, 'index']);
            Route::post('/store', [PermissionController::class, 'store']);
            Route::get('/show/{id}', [PermissionController::class, 'show']);
            Route::put('/update/{id}', [PermissionController::class, 'update']);
            Route::delete('/destroy/{id}', [PermissionController::class, 'destroy']);
            Route::put('/block/{id}', [PermissionController::class, 'block']);
            Route::put('/unblock/{id}', [PermissionController::class, 'unblock']);
        });
    });

    // Gestion des commentaires
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('review')->group(function () {
            Route::post('/store', [ReviewController::class, 'store']);
            Route::get('/show/{id}', [ReviewController::class, 'show']);
            Route::put('/update/{id}', [ReviewController::class, 'update']);
            Route::delete('/destroy/{id}', [ReviewController::class, 'destroy']);
        });
    });

    //Gestion des langues sous formes de CRUD.
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('language')->group(function () {
            Route::get('/index', [LanguageController::class, 'index']);
            Route::post('/store', [LanguageController::class, 'store']);
            Route::get('/show/{id}', [LanguageController::class, 'show']);
            Route::put('/update/{id}', [LanguageController::class, 'update']);
            Route::delete('/destroy/{id}', [LanguageController::class, 'destroy']);
            Route::put('/block/{id}', [LanguageController::class, 'block']);
            Route::put('/unblock/{id}', [LanguageController::class, 'unblock']);
        });
    });


    //Gestion des préférences.
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('preference')->group(function () {
            Route::post('/store', [PreferenceController::class, 'store']);
            Route::get('/indexUnverified', [PreferenceController::class, 'indexUnverified']);
            Route::post('/storeUnexist/{housingId}', [PreferenceController::class, 'storeUnexist']);
            Route::get('/show/{id}', [PreferenceController::class, 'show']);
            Route::put('/updateName/{id}', [PreferenceController::class, 'updateName']);
            Route::post('/updateIcone/{id}', [PreferenceController::class, 'updateIcone']);
            Route::delete('/destroy/{id}', [PreferenceController::class, 'destroy']);
            Route::put('/makeVerified/{id}', [PreferenceController::class, 'makeVerified']);
            Route::put('/block/{id}', [PreferenceController::class, 'block']);
            Route::put('/unblock/{id}', [PreferenceController::class, 'unblock']);
            Route::get('/VerifiedBlocknotDelete', [PreferenceController::class, 'VerifiedBlocknotDelete']);
            Route::get('/VerifiednotBlocknotDelete', [PreferenceController::class, 'VerifiednotBlocknotDelete']);
            Route::get('/VerifiednotBlockDelete', [PreferenceController::class, 'VerifiednotBlockDelete']);
        });
    });

    //Gestion des types de propriété.
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('propertyType')->group(function () {
            Route::post('/store', [PropertyTypeController::class, 'store']);
            Route::get('/show/{id}', [PropertyTypeController::class, 'show']);
            Route::put('/updateName/{id}', [PropertyTypeController::class, 'updateName']);
            Route::post('/updateIcone/{id}', [PropertyTypeController::class, 'updateIcone']);
            Route::delete('/destroy/{id}', [PropertyTypeController::class, 'destroy']);
            Route::put('/block/{id}', [PropertyTypeController::class, 'block']);
            Route::get('/indexBlock', [PropertyTypeController::class, 'indexBlock']);
            Route::put('/unblock/{id}', [PropertyTypeController::class, 'unblock']);
        });
    });


    //Gestion de la liste des documents
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('document')->group(function () {
            Route::get('/index', [DocumentController::class, 'index']);
            Route::post('/store', [DocumentController::class, 'store']);
            Route::get('/show/{id}', [DocumentController::class, 'show']);
            Route::put('/update/{id}', [DocumentController::class, 'update']);
            Route::delete('/destroy/{id}', [DocumentController::class, 'destroy']);
            Route::put('/block/{id}', [DocumentController::class, 'block']);
            Route::put('/unblock/{id}', [DocumentController::class, 'unblock']);
            Route::put('/active/{id}', [DocumentController::class, 'active']);
            Route::put('/inactive/{id}', [DocumentController::class, 'inactive']);
            Route::get('/document_inactif', [DocumentController::class, 'document_inactif']);
        });
    });
        //Gestion de la Verification des documents
    Route::group(['middleware' => ['role:traveler']], function () {
        
        Route::prefix('verificationdocument')->group(function () {
            Route::get('/index', [VerificationDocumentController::class, 'index']);
            Route::get('/show/{id}', [VerificationDocumentController::class, 'show']);
            Route::post('/update', [VerificationDocumentController::class, 'changeDocument']);
            Route::post('/hote/valider/all', [VerificationDocumentController::class, 'validateDocuments']);
            Route::post('/hote/valider/one', [VerificationDocumentController::class, 'validateDocument']);
        });
    });

    //Gestion des commissions
    Route::group(['middleware' => ['role:traveler']], function () {
        
        Route::prefix('commission')->group(function () {
            Route::post('/updateCommissionForSpecifiqueUser', [CommissionController::class, 'updateCommissionForSpecifiqueUser']);
            Route::put('/updateCommissionValueByAnother', [CommissionController::class, 'updateCommissionValueByAnother']);
            Route::get('/usersWithCommission/{commission}', [CommissionController::class, 'usersWithCommission']);
        });
    });

  //Gestion des préférences des utilisateurs
    Route::group(['middleware' => ['role:traveler']], function () {
    
      
        Route::prefix('users/preference')->group(function () {
            Route::post('/preference/add', [User_preferenceController::class, 'AddUserPreferences']);
            Route::get('/userPreferences', [UserController::class, 'showUserPreferences']);
        });

    });


   // Gestion des Notifications
   Route::group(['middleware' => ['role:traveler']], function () {
     Route::prefix('notifications')->group(function () {
        Route::get('/index', [NotificationController::class, 'index']);
        Route::post('/store', [NotificationController::class, 'store']);
        Route::delete('/destroy/{id}', [NotificationController::class, 'destroy']);
    });
   });
      // Gestion logement côté voyageur.en gros les affichages de manière publics
    Route::group(['middleware' => ['role:traveler']], function () {
        
        Route::prefix('logement')->group(function () {
            Route::get('/index/ListeDesLogementsAcceuil', [HousingController::class, 'ListeDesLogementsAcceuil']);
            Route::get('/index/ListeDesPhotosLogementAcceuil/{id}', [HousingController::class, 'ListeDesPhotosLogementAcceuil']);
            Route::get('/ShowDetailLogementAcceuil/{housing_id}', [HousingController::class, 'ShowDetailLogementAcceuil']);
            Route::get('/filterby/typehousing/{id}', [HousingController::class, 'ListeDesLogementsAcceuilFilterByTypehousing']);
            Route::get('/filterby/typeproperty/{id}', [HousingController::class, 'ListeDesLogementsAcceuilFilterByTypeproperty']);
            Route::get('NonDisponible', [HousingController::class, 'ListeDesLogementsNonDisponible']);
            Route::get('Disponible', [HousingController::class, 'ListeDesLogementsDisponible']);
            Route::get('/filterby/country/{country}', [HousingController::class, 'ListeDesLogementsFilterByCountry']);
            Route::get('/filterby/department/{department}', [HousingController::class, 'ListeDesLogementsFilterByDepartement']);
            Route::get('/filterby/city/{city}', [HousingController::class, 'ListeDesLogementsFilterByCity']);
            Route::get('/filterby/hote/{hote_id}', [HousingController::class, 'ListeDesLogementsFilterByHote']);
            Route::get('/filterby/preference/{preference_id}', [HousingController::class, 'ListeDesLogementsAcceuilFilterByPreference']);
            Route::get('/filterby/nbtraveler/{nbtraveler}', [HousingController::class, 'ListeDesLogementsAcceuilFilterNbtravaller']);
            Route::get('/filterby/nightpricemax/{price}', [HousingController::class, 'getListingsByNightPriceMax']);
            Route::get('/filterby/nightpricemin/{price}', [HousingController::class, 'getListingsByNightPriceMin']);
            Route::get('/TypeStay/{housing_id}', [HousingController::class, 'LogementTypeStay']);
            //Gestion des logements en favoris
            Route::post('/addfavorites', [FavorisController::class, 'addToFavorites']);
            Route::delete('/removefromfavorites/{housingId}', [FavorisController::class, 'removeFromFavorites']); 
            Route::get('/favorites', [FavorisController::class, 'getFavorites']);



        }); 
    });

    // Gestion logement côté hôte
    Route::group(['middleware' => ['role:traveler']], function () {
        
        Route::prefix('logement')->group(function () {
            //Gestion des logements (CRUD)
            Route::post('/store', [HousingController::class, 'addHousing']);
            Route::put('/update/sensible/{housingid}', [HousingController::class, 'updateSensibleHousing']);
            Route::put('/update/insensible{housingid}', [HousingController::class, 'updateInsensibleHousing']);
            Route::put('/{housingId}/hote/disable', [HousingController::class, 'disableHousing']);
            Route::put('/{housingId}/hote/enable', [HousingController::class, 'enableHousing']);
            Route::delete('/destroyHousingHote/{id}', [HousingController::class, 'destroyHousingHote']);
            Route::get('/getHousingForHote', [HousingController::class, 'getHousingForHote']);
            //Gestion des photos logement
            Route::post('/updatephoto/{photo_id}', [PhotoController::class, 'updatePhotoHousing']);
            Route::post('/{housingId}/setcoverphoto/{photoId}', [PhotoController::class, 'setCoverPhoto']);
            Route::delete('/photo/{photoId}', [PhotoController::class, 'deletePhotoHousing']);
            //Gestion des equipements du logement
            Route::post('equipment/storeUnexist/{housingId}', [HousingEquipmentController::class, 'storeUnexist']);
            Route::get('/{housingEquipmentId}/equipements', [HousingEquipmentController::class, 'equipementsHousing']);
            Route::delete('/equipement', [HousingEquipmentController::class, 'DeleteEquipementHousing']);
            Route::post('/equipment/addEquipmentToHousing', [HousingEquipmentController::class, 'addEquipmentToHousing']);
            Route::post('/equipment/storeUnexist', [HousingEquipmentController::class, 'storeUnexist']);
            //Gestion des preferences du logement
            Route::get('/{housingPreferenceId}/preferences', [HousingPreferenceController::class, 'housingPreference']);
            Route::delete('/preference', [HousingPreferenceController::class, 'deletePreferenceHousing']);
            Route::post('/preference/addPreferenceToHousing', [HousingPreferenceController::class, 'addPreferenceToHousing']);
            Route::post('/preference/storeUnexist/{housingId}', [HousingPreferenceController::class, 'storeUnexist']);

            //Gestion des catégories pour un hote qui ajoute déjà un logement
            Route::delete('category/photo/{photoid}', [HousingCategoryFileController::class, 'deletePhotoHousingCategory']);
            Route::post('category/default/add', [HousingCategoryFileController::class, 'addHousingCategory']);
            Route::post('category/default/addNew', [HousingCategoryFileController::class, 'addHousingCategoryNew']);

            //Gestion des charges
            Route::post('/charge/addChargeToHousing', [HousingChargeController::class, 'addChargeToHousing']);
            Route::get('/charge/listelogementcharge/{housingId}', [HousingChargeController::class, 'listelogementcharge']);

        });
    });
   
    // Gestion logement côté admin
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('logement')->group(function () {
             //Gestion des logements en attente de validation ou de mise à jours pour être visible sur le site  coté administrateur
            Route::get('/withoutvalidation', [AdminHousingController::class, 'indexHousingForValidationForadmin']);
            Route::get('/withoutupdate', [AdminHousingController::class, 'indexHousingForUpdateForadmin']);
            Route::get('/withoutvalidation/show/{id}', [AdminHousingController::class, 'showHousingDetailForValidationForadmin']);
            Route::put('/validate/one/{id}', [AdminHousingController::class, 'ValidateOneHousing']);
            Route::put('/validate/many/', [AdminHousingController::class, 'ValidateManyHousing']);
            Route::put('/update/one/{id}', [AdminHousingController::class, 'UpdateOneHousing']);

             //Gestion des logements coté administrateur
            Route::get('/index/ListeDesLogementsValideBloque', [AdminHousingController::class, 'ListeDesLogementsValideBloque']);
            Route::get('/index/ListeDesLogementsValideDelete', [AdminHousingController::class, 'ListeDesLogementsValideDelete']);
            Route::delete('/destroy/{id}', [HousingController::class, 'destroy']);
            Route::put('/block/{id}', [HousingController::class, 'block']);
            Route::put('/unblock/{id}', [HousingController::class, 'unblock']);
            Route::get('/index/ListeDesLogementsValideDisable', [AdminHousingController::class, 'ListeDesLogementsValideDisable']);
            Route::get('/getHousingDestroyedByHote', [AdminHousingController::class, 'getHousingDestroyedByHote']);
             //Gestion des categories côté admin
            Route::get('/category/default/invalid', [HousingCategoryFileController::class, 'getCategoryDefaultInvalidHousings']);
            Route::put('/category/default/{housing_id}/{category_id}/validate', [HousingCategoryFileController::class, 'validateDefaultCategoryHousing']);
            Route::get('/category/unexist/invalid', [HousingCategoryFileController::class, 'getCategoryUnexistInvalidHousings']);
            Route::put('/category/unexist/{housing_id}/{category_id}/validate', [HousingCategoryFileController::class, 'validateUnexistCategoryHousing']);
            Route::get('/category/{housing_id}/{category_id}/detail', [HousingCategoryFileController::class, 'getCategoryDetail']);
            //Gestion des équipements côté admin
            Route::get('/equipment/ListHousingEquipmentInvalid/{housingId}', [HousingEquipmentController::class, 'ListHousingEquipmentInvalid']);
            Route::post('/equipment/makeVerifiedHousingEquipment/{housingEquipmentId}', [HousingEquipmentController::class, 'makeVerifiedHousingEquipment']);
            Route::get('/equipment/ListEquipmentForHousingInvalid/{housingId}', [HousingEquipmentController::class, 'ListEquipmentForHousingInvalid']);
            Route::get('/equipment/getHousingEquipmentInvalid', [HousingEquipmentController::class, 'getHousingEquipmentInvalid']);
            Route::get('/equipment/getUnexistEquipmentInvalidForHousing', [HousingEquipmentController::class, 'getUnexistEquipmentInvalidForHousing']);
            //Gestion des preference côté admin
            Route::get('/preference/getHousingPreferenceInvalid', [HousingPreferenceController::class, 'getHousingPreferenceInvalid']);
            Route::get('/preference/getUnexistPreferenceInvalidForHousing', [HousingPreferenceController::class, 'getUnexistPreferenceInvalidForHousing']);
            Route::get('/preference/ListHousingPreferenceInvalid/{housingId}', [HousingPreferenceController::class, 'ListHousingPreferenceInvalid']);
            Route::get('/preference/ListPreferenceForHousingInvalid/{housingId}', [HousingPreferenceController::class, 'ListPreferenceForHousingInvalid']);
            Route::post('/preference/makeVerifiedHousingPreference/{housingPreferenceId}', [HousingPreferenceController::class, 'makeVerifiedHousingPreference']);
                    

            
             

        });
        Route::get('/admin/statistique', [AdminHousingController::class, 'getAdminStatistics']);
    });
    //Gestion des reservation
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('reservation')->group(function () {
            //Gestion des reservations côté hôte
            Route::post('/reviews/note/add', [ReviewReservationController::class, 'AddReviewNote']);

            
        });
        
    });

    Route::group(['middleware' => ['permission:manageReduction']], function () {

    });


});

/**end Route nécéssitant l'authentification*/



/**Route ne nécéssitant pas l'authentification */

Route::get('/propertyType/index', [PropertyTypeController::class, 'index']);
Route::get('/housingtype/index', [HousingTypeController::class, 'index']);
Route::get('/typestays/index', [TypeStayController::class, 'index']);
Route::get('/category/index', [CategorieController::class, 'index']);
Route::get('/criteria/index', [CriteriaController::class, 'index']);
Route::get('/review/index', [ReviewController::class, 'index']);
Route::get('/preference/index', [PreferenceController::class, 'index']);


/** end Route ne nécéssitant pas l'authentification */


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'checkAuth']);
});
