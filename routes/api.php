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
use App\Http\Controllers\ChargeController;
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
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\PortfeuilleController;
use App\Http\Controllers\HoteReservationController;
use App\Http\Controllers\PortfeuilleTransactionController;
use App\Http\Controllers\RetraitController;
use App\Http\Controllers\MethodPayementController;
use App\Http\Controllers\MoyenPayementController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ValeurRemboursementController;
use App\Http\Controllers\PayementController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ReductionController;
use App\Http\Controllers\UserVisiteHousingController;
use App\Http\Controllers\UserVisiteSiteController;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [LoginController::class, 'checkAuth']);
    
});

//Inscription et Connexion
Route::prefix('users')->group(function () {
    Route::post('/register', [InscriptionController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LogoutController::class, 'logout']);
    Route::post('/verification_code', [LoginController::class, 'verification_code']);
    Route::post('/new_code/{id}', [LoginController::class, 'new_code']);
    Route::post('/password_recovery_start_step', [LoginController::class, 'password_recovery_start_step']);
    Route::post('/password_recovery_end_step', [LoginController::class, 'password_recovery_end_step']);
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
            Route::put('/updateName/{id}', [CategorieController::class, 'updateName']);
            Route::post('/updateIcone/{id}', [CategorieController::class, 'updateIcone']);
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
            Route::put('/updateName/{id}', [CriteriaController::class, 'updateName']);
            Route::post('/updateIcone/{id}', [CriteriaController::class, 'updateIcone']);
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
        Route::get('/userPreferences', [UserController::class, 'showUserPreferences']);
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
            Route::get('/detail/{userId}', [UserController::class, 'getUserDetails']);
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
            Route::post('/store', [LanguageController::class, 'store']);
            Route::get('/show/{id}', [LanguageController::class, 'show']);
            Route::put('/updateName/{id}', [LanguageController::class, 'updateName']);
            Route::post('/updateIcone/{id}', [LanguageController::class, 'updateIcone']);
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
            Route::post('/add', [User_preferenceController::class, 'AddUserPreferences']);
            Route::get('/userPreferences', [UserController::class, 'showUserPreferences']);
        });

    });


   // Gestion des Notifications
   Route::group(['middleware' => ['role:traveler']], function () {
     Route::prefix('notifications')->group(function () {
        Route::get('/index', [NotificationController::class, 'index']);
        Route::post('/store', [NotificationController::class, 'store']);
        Route::delete('/destroy/{id}', [NotificationController::class, 'destroy']);
        Route::put('/{id}/markread', [NotificationController::class, 'markNotificationAsRead']);
        Route::get('/read', [NotificationController::class, 'getReadNotifications']);
        Route::get('/unread', [NotificationController::class, 'getUnreadNotifications']);
    });
   });
      // Gestion logement côté voyageur.en gros les affichages de manière publics
    Route::group(['middleware' => ['role:traveler']], function () {
        
        Route::prefix('logement')->group(function () {
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
            Route::post('/storeInProgress', [HousingController::class, 'addHousingInProgress']);
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
            Route::post('/add/file/{housingId}', [HousingController::class, 'addPhotoToHousing']);
            //Gestion des equipements du logement
            Route::post('equipment/storeUnexist/{housingId}', [HousingEquipmentController::class, 'storeUnexist']);
            Route::get('/{housingId}/equipements', [HousingEquipmentController::class, 'equipementsHousing']);
            Route::delete('/equipement', [HousingEquipmentController::class, 'DeleteEquipementHousing']);
            Route::post('/equipment/addEquipmentToHousing', [HousingEquipmentController::class, 'addEquipmentToHousing']);
            //Gestion des preferences du logement
            Route::get('/{housingPreferenceId}/preferences', [HousingPreferenceController::class, 'housingPreference']);
            Route::delete('/preference', [HousingPreferenceController::class, 'deletePreferenceHousing']);
            Route::post('/preference/addPreferenceToHousing', [HousingPreferenceController::class, 'addPreferenceToHousing']);
            Route::post('/preference/storeUnexist/{housingId}', [HousingPreferenceController::class, 'storeUnexist']);

            //Gestion des catégories pour un hote qui ajoute déjà un logement
            Route::delete('/category/photo/{photoid}', [HousingCategoryFileController::class, 'deletePhotoHousingCategory']);
            Route::post('/category/default/add', [HousingCategoryFileController::class, 'addHousingCategory']);
            Route::post('/category/default/addNew', [HousingCategoryFileController::class, 'addHousingCategoryNew']);
            Route::delete('/{housingId}/category/{categoryId}/delete', [HousingCategoryFileController::class, 'deleteHousingCategory']);
            Route::post('/{housingId}/category/{categoryId}/photos/add', [HousingCategoryFileController::class, 'addPhotosCategoryToHousing']);
            


            //Gestion des charges
            Route::post('/charge/addChargeToHousing', [HousingChargeController::class, 'addChargeToHousing']);
            Route::get('/charge/listelogementcharge/{housingId}', [HousingChargeController::class, 'listelogementcharge']);
            Route::delete('/charge', [HousingChargeController::class, 'DeleteChargeHousing']);

            //Liste des logements non rempli complètement par l'hôte  
            Route::get('/liste/notFinished', [HousingController::class, 'HousingHoteInProgress']);

        });
    });
   
    // Gestion logement côté admin
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('logement')->group(function () {
             //Gestion des logements en attente de validation ou de mise à jours pour être visible sur le site  coté administrateur 
            Route::get('/withoutvalidate', [AdminHousingController::class, 'indexHousingForValidationForadmin']);
            Route::get('/HousingHoteInProgressForAdmin', [AdminHousingController::class, 'HousingHoteInProgressForAdmin']);
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
            Route::get('/hote_with_many_housing', [AdminHousingController::class, 'hote_with_many_housing']);
            Route::get('/country_with_many_housing', [AdminHousingController::class, 'country_with_many_housing']);
            Route::get('/getHousingDestroyedByHote', [AdminHousingController::class, 'getHousingDestroyedByHote']);
            Route::get('/getTop10HousingByAverageNotes', [AdminHousingController::class, 'getTop10HousingByAverageNotes']);
             //Gestion des categories côté admin
            Route::get('/category/default/invalid', [HousingCategoryFileController::class, 'getCategoryDefaultInvalidHousings']);
            Route::put('/category/default/{housing_id}/{category_id}/validate', [HousingCategoryFileController::class, 'validateDefaultCategoryHousing']);
            Route::get('/category/unexist/invalid', [HousingCategoryFileController::class, 'getCategoryUnexistInvalidHousings']);
            Route::put('/category/unexist/{housing_id}/{category_id}/validate', [HousingCategoryFileController::class, 'validateUnexistCategoryHousing']);
            Route::get('/category/{housing_id}/{category_id}/detail', [HousingCategoryFileController::class, 'getCategoryDetail']);
            Route::get('/category/photo/unverified', [HousingCategoryFileController::class, 'getUnverifiedHousingCategoryFilesWithDetails']);
            Route::put('/category/photo/{id}/validate', [HousingCategoryFileController::class, 'validateHousingCategoryFile']);
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
            //Gestion des photos de logement
            Route::get('/photos/unverified', [HousingController::class, 'getUnverifiedPhotos']); 
            Route::put('/photos/validate/{photoId}', [HousingController::class, 'validatePhoto']); 
           

            
             

        });
        
    });
    //Gestion des reservation
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('reservation')->group(function () {
            // Reviews
            Route::post('/reviews/note/add', [ReviewReservationController::class, 'AddReviewNote']);
            Route::get('/{housingId}/reviews/note/get', [ReviewReservationController::class, 'LogementAvecMoyenneNotesCritereEtCommentairesAcceuil']);
            Route::get('/statistiques_notes/{housing_id}', [ReviewReservationController::class, 'getStatistiquesDesNotes']);
            // Reservation operations
            Route::post('/store', [ReservationController::class, 'storeReservationWithPayment']);
    
            // Hote (Host)
            Route::put('/hote_confirm_reservation/{idReservation}', [ReservationController::class, 'hote_confirm_reservation']);
            Route::put('/hote_reject_reservation/{idReservation}', [ReservationController::class, 'hote_reject_reservation']);
            Route::get('/showDetailOfReservationForHote/{idReservation}', [ReservationController::class, 'showDetailOfReservationForHote']);
            Route::get('/getReservationsByHousingId/{housingId}', [ReservationController::class, 'getReservationsByHousingId']);
            Route::get('/reservationsConfirmedByHost', [HoteReservationController::class, 'reservationsConfirmedByHost']);
            Route::get('/reservationsRejectedByHost', [HoteReservationController::class, 'reservationsRejectedByHost']);
            Route::get('/reservationsCanceledByTravelerForHost', [HoteReservationController::class, 'reservationsCanceledByTravelerForHost']);
            Route::get('/reservationsNotConfirmedYetByHost', [HoteReservationController::class, 'reservationsNotConfirmedYetByHost']);
    
            // Traveler
            Route::put('/traveler_reject_reservation/{idReservation}', [ReservationController::class, 'traveler_reject_reservation']);
            Route::post('/confirmIntegration', [ReservationController::class, 'confirmIntegration']);
    
            // Admin
            Route::get('/housing_with_many_reservation', [AdminReservationController::class, 'housing_with_many_reservation']);
            Route::get('/country_with_many_reservation', [AdminReservationController::class, 'country_with_many_reservation']);
            Route::get('/housing_without_reservation', [AdminReservationController::class, 'housing_without_reservation']);
            Route::get('/getReservationsCountByYear', [AdminReservationController::class, 'getReservationsCountByYear']);
            Route::get('/getAllReservation', [AdminReservationController::class, 'getAllReservation']);
            Route::get('/getUserReservations/{user}', [AdminReservationController::class, 'getUserReservationsForAdmin']);
            Route::get('/showDetailOfReservation/{idReservation}', [AdminReservationController::class, 'showDetailOfReservationForAdmin']);
            Route::get('/topTravelersWithMostReservations', [AdminReservationController::class, 'topTravelersWithMostReservations']);
            Route::get('/getReservationsCountByYearAndMonth', [AdminReservationController::class, 'getReservationsCountByYearAndMonth']);
            Route::get('/getAllReservationCanceledByTravelerForAdmin', [ReservationController::class, 'getAllReservationCanceledByTravelerForAdmin']);
            Route::get('/getAllReservationRejectedForAdmin', [ReservationController::class, 'getAllReservationRejectedForAdmin']);
            Route::get('/getAllReservationConfirmedForAdmin', [ReservationController::class, 'getAllReservationConfirmedForAdmin']);

        });
    });
    
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('portefeuille')->group(function () {
           Route::post('/credit', [PortfeuilleController::class, 'creditPortfeuille']);
           Route::get('/user/transaction', [PortfeuilleTransactionController::class, 'getPortfeuilleDetails']);
           Route::get('/transaction/all', [PortfeuilleTransactionController::class, 'getAllTransactions']);

    


       });
    });


    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('methodPayement')->group(function () {
    
            
            Route::post('/store', [MethodPayementController::class, 'store']);
            Route::get('/index', [MethodPayementController::class, 'index']);
            Route::get('/show/{id}', [MethodPayementController::class, 'show']);
            Route::put('/updateName/{id}', [MethodPayementController::class, 'updateName']);
            Route::post('/updateIcone/{id}', [MethodPayementController::class, 'updateIcone']);
            Route::delete('/destroy/{id}', [MethodPayementController::class, 'destroy']);
            Route::put('/block/{id}', [MethodPayementController::class, 'block']);
            Route::put('/unblock/{id}', [MethodPayementController::class, 'unblock']);
           
        });
    });

    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('retrait')->group(function () {
            //Admin
            Route::get('/ListRetraitWaitingConfirmationByAdmin', [RetraitController::class, 'ListRetraitWaitingConfirmationByAdmin']);
            Route::get('/ListRetraitOfTravelerWaitingConfirmationByAdmin', [RetraitController::class, 'ListRetraitOfTravelerWaitingConfirmationByAdmin']);
            Route::get('/ListRetraitOfHoteWaitingConfirmationByAdmin', [RetraitController::class, 'ListRetraitOfHoteWaitingConfirmationByAdmin']);
            Route::get('/ListRetraitConfirmedByAdmin', [RetraitController::class, 'ListRetraitConfirmedByAdmin']);
            Route::put('/validateRetraitByAdmin/{retraitId}', [RetraitController::class, 'validateRetraitByAdmin']);
            Route::get('/ListRetraitRejectForAdmin', [RetraitController::class, 'ListRetraitRejectForAdmin']);
            Route::put('/rejectRetraitByAdmin/{retraitId}', [RetraitController::class, 'rejectRetraitByAdmin']);
            //another user
            Route::post('/store', [RetraitController::class, 'store']);
            Route::get('/ListRetraitOfUserAuth', [RetraitController::class, 'ListRetraitOfUserAuth']);
            Route::get('/ListRetraitRejectOfUserAuth', [RetraitController::class, 'ListRetraitRejectOfUserAuth']);
        });
    });

    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('moyenPayement')->group(function () {
            Route::get('/ListeMoyenPayement', [MoyenPayementController::class, 'ListeMoyenPayement']);
            Route::get('/ListeMoyenPayementUserAuth', [MoyenPayementController::class, 'ListeMoyenPayementUserAuth']);
            Route::get('/ListeMoyenPayementBlocked', [MoyenPayementController::class, 'ListeMoyenPayementBlocked']);
            Route::get('/ListeMoyenPayementDeleted', [MoyenPayementController::class, 'ListeMoyenPayementDeleted']);
            Route::post('/store', [MoyenPayementController::class, 'store']);
            Route::get('/show/{idMoyenPayement}', [MoyenPayementController::class, 'show']);
            Route::put('/update/{idMoyenPayement}', [MoyenPayementController::class, 'update']);
            Route::delete('/destroy/{idMoyenPayement}', [MoyenPayementController::class, 'destroy']);
            Route::put('/block/{idMoyenPayement}', [MoyenPayementController::class, 'block']);
            Route::put('/unblock/{idMoyenPayement}', [MoyenPayementController::class, 'unblock']);
        });
    });
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('valeurRemboursement')->group(function () {
            Route::post('/store', [ValeurRemboursementController::class, 'store']);
            Route::put('/update/{id}', [ValeurRemboursementController::class, 'update']);
            Route::delete('/destroy/{id}', [ValeurRemboursementController::class, 'destroy']);
            Route::get('/index', [ValeurRemboursementController::class, 'index']);
        });
    });
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('paiement')->group(function () {
            Route::get('/reservation/user', [PayementController::class, 'listPaymentsForUser']);
            Route::get('/reservation/all', [PayementController::class, 'listAllPayments']);
        });
    });
    
    Route::middleware('role:traveler')->group(function() {
        Route::prefix('charge')->group(function() {
            Route::get('index', [ChargeController::class, 'index'])
                ->name('charge.index');
            Route::post('store', [ChargeController::class, 'store'])
                ->name('charge.store');
            Route::put('updateName/{id}', [ChargeController::class, 'updateName'])
                ->name('charge.updateName');
            Route::post('updateIcone/{id}', [ChargeController::class, 'updateIcone'])
                ->name('charge.updateIcone');
            Route::delete('destroy/{id}', [ChargeController::class, 'destroy'])
                ->name('charge.destroy');
        });
    });

    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('promotion')->group(function () {
           Route::post('/add', [PromotionController::class, 'addPromotion']);
           Route::get('/user', [PromotionController::class, 'getUserPromotions']);
           Route::get('/housing/{housingId}', [PromotionController::class, 'getHousingPromotions']);
           Route::get('/all', [PromotionController::class, 'getAllPromotions']);
           Route::delete('/delete/{id}', [PromotionController::class, 'DeletePromotion']);

       });
    });
    Route::group(['middleware' => ['role:traveler']], function () {
        Route::prefix('reduction')->group(function () {
            Route::post('/add', [ReductionController::class, 'addReduction']);
            Route::get('/user', [ReductionController::class, 'getUserReductions']);
            Route::get('/housing/{housingId}', [ReductionController::class, 'getHousingReductions']);
            Route::get('/all', [ReductionController::class, 'getAllReductions']);
            Route::delete('/delete/{id}', [ReductionController::class, 'DeleteReduction']);
        });
    });
    


});

/*end Route nécéssitant l'authentification/



/**Route ne nécéssitant pas l'authentification */

Route::get('/propertyType/index', [PropertyTypeController::class, 'index']);
Route::get('/housingtype/index', [HousingTypeController::class, 'index']);
Route::get('/typestays/index', [TypeStayController::class, 'index']);
Route::get('/category/index', [CategorieController::class, 'index']);
Route::get('/criteria/index', [CriteriaController::class, 'index']);
Route::get('/review/index', [ReviewController::class, 'index']);
Route::get('/preference/index', [PreferenceController::class, 'index']);

Route::prefix('logement')->group(function () {
   Route::post('/index/ListeDesLogementsAcceuil', [HousingController::class, 'ListeDesLogementsAcceuil']);
   Route::get('/index/ListeDesPhotosLogementAcceuil/{id}', [HousingController::class, 'ListeDesPhotosLogementAcceuil']);
   Route::post('/ShowDetailLogementAcceuil', [HousingController::class, 'ShowDetailLogementAcceuil']);
   Route::get('/filterby/typehousing/{id}', [HousingController::class, 'ListeDesLogementsAcceuilFilterByTypehousing']);
   Route::get('/filterby/typeproperty/{id}', [HousingController::class, 'ListeDesLogementsAcceuilFilterByTypeproperty']);
   Route::get('/filterby/country/{country}', [HousingController::class, 'ListeDesLogementsFilterByCountry']);
   Route::get('/filterby/department/{department}', [HousingController::class, 'ListeDesLogementsFilterByDepartement']);
   Route::get('/filterby/city/{city}', [HousingController::class, 'ListeDesLogementsFilterByCity']);
   Route::get('/filterby/hote/{hote_id}', [HousingController::class, 'ListeDesLogementsFilterByHote']);
   Route::get('/filterby/preference/{preference_id}', [HousingController::class, 'ListeDesLogementsAcceuilFilterByPreference']);
   Route::get('/filterby/nbtraveler/{nbtraveler}', [HousingController::class, 'ListeDesLogementsAcceuilFilterNbtravaller']);
   Route::get('/filterby/nightpricemax/{price}', [HousingController::class, 'getListingsByNightPriceMax']);
   Route::get('/filterby/nightpricemin/{price}', [HousingController::class, 'getListingsByNightPriceMin']);
   Route::get('/detail/getHousingStatisticAcceuil/{housing_id}', [HousingController::class, 'getHousingStatisticAcceuil']);
   Route::get('/available_at_date', [HousingController::class, 'getAvailableHousingsAtDate']);
   Route::get('/available_between_dates', [HousingController::class, 'getAvailableHousingsBetweenDates']);

 
   

});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('site')->group(function () {
        Route::get('/visit_statistics', [UserVisiteSiteController::class, 'getSiteVisitStatistics']);
        Route::get('/date/visit_statistics', [UserVisiteSiteController::class, 'getSiteVisitStatisticsDate']);
        Route::get('/current_month/visit_statistics', [UserVisiteSiteController::class, 'getCurrentMonthVisitStatistics']);
        Route::get('/current_year/visit_statistics', [UserVisiteSiteController::class, 'getCurrentYearVisitStatistics']);
        Route::get('/yearly/visit_statistics', [UserVisiteSiteController::class, 'getYearlyVisitStatistics']);
    
    });
    Route::get('logement/admin/statistique', [AdminHousingController::class, 'getAdminStatistics']);
    

    
});
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('logement')->group(function () {
    Route::get('{housing_id}/date/visit_statistics', [UserVisiteHousingController::class, 'getVisitStatisticsDate']);

    Route::get('{housing_id}/current_month/visit_statistics', [UserVisiteHousingController::class, 'getCurrentMonthVisitStatistics']);

    Route::get('{housing_id}/current_year/visit_statistics', [UserVisiteHousingController::class, 'getCurrentYearVisitStatistics']);
    
    Route::get('{housing_id}/yearly/visit_statistics', [UserVisiteHousingController::class, 'getYearlyVisitStatistics']);
    Route::get('/{housingId}/visit_statistics', [UserVisiteHousingController::class, 'getHousingVisitStatistics']);


    });
    
    

    
});
 


/** end Route ne nécéssitant pas l'authentification */