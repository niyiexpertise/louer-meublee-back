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
    Route::group(['middleware' => ['permission:manageCategory']], function () {
        Route::prefix('category')->group(function () {
            Route::get('/index', [CategorieController::class, 'index']);
            Route::post('/store', [CategorieController::class, 'store']);
            Route::get('/show/{id}', [CategorieController::class, 'show']);
            Route::put('/update/{id}', [CategorieController::class, 'update']);
            Route::delete('/destroy/{id}', [CategorieController::class, 'destroy']);
            Route::put('/block/{id}', [CategorieController::class, 'block']);
            Route::put('/unblock/{id}', [CategorieController::class, 'unblock']);
        });
    });

    //Gestion des permissions
    Route::group(['middleware' => ['permission:manageHousingType']], function () {
        Route::prefix('housingtype')->group(function () {
            Route::post('/store', [HousingTypeController::class, 'store']);
            Route::get('/show/{id}', [HousingTypeController::class, 'show']);
            Route::put('/update/{id}', [HousingTypeController::class, 'update']);
            Route::delete('/destroy/{id}', [HousingTypeController::class, 'destroy']);
            Route::put('/block/{id}', [HousingTypeController::class, 'block']);
            Route::put('unblock/{id}', [HousingTypeController::class, 'unblock']);
        });
    });

    // Gestion des type de sejour
    Route::group(['middleware' => ['permission:manageTypeStay']], function () {
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
    Route::group(['middleware' => ['permission:manageCriteria']], function () {
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
    Route::group(['middleware' => ['permission:manageRole']], function () {
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
    Route::group(['middleware' => ['permission:manageEquipment']], function () {
        Route::prefix('equipment')->group(function () {
            Route::get('/index', [EquipementController::class, 'index']);
            Route::post('/store', [EquipementController::class, 'store']);
            Route::get('/show/{id}', [EquipementController::class, 'show']);
            Route::put('/update/{id}', [EquipementController::class, 'update']);
            Route::delete('/destroy/{id}', [EquipementController::class, 'destroy']);
            Route::put('/block/{id}', [EquipementController::class, 'block']);
            Route::put('/unblock/{id}', [EquipementController::class, 'unblock']);
        });
    });

    Route::group(['middleware' => ['role:admin']], function () {
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
Route::group(['middleware' => ['permission:manageUser']], function () {
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
    Route::group(['middleware' => ['permission:manageUserAdmin']], function () {
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
    Route::group(['middleware' => ['permission:managePermission']], function () {
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
    Route::group(['middleware' => ['permission:manageReview']], function () {
        Route::prefix('review')->group(function () {
            Route::post('/store', [ReviewController::class, 'store']);
            Route::get('/show/{id}', [ReviewController::class, 'show']);
            Route::put('/update/{id}', [ReviewController::class, 'update']);
            Route::delete('/destroy/{id}', [ReviewController::class, 'destroy']);
        });
    });

    //Gestion des langues sous formes de CRUD.
    Route::group(['middleware' => ['permission:manageLanguage']], function () {
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
    Route::group(['middleware' => ['permission: managePreference']], function () {
        Route::prefix('preference')->group(function () {
            Route::post('/store', [PreferenceController::class, 'store']);
            Route::get('/show/{id}', [PreferenceController::class, 'show']);
            Route::put('/update/{id}', [PreferenceController::class, 'update']);
            Route::delete('/destroy/{id}', [PreferenceController::class, 'destroy']);
            Route::put('/block/{id}', [PreferenceController::class, 'block']);
            Route::put('/unblock/{id}', [PreferenceController::class, 'unblock']);
        });
    });

    //Gestion des types de propriété.
    Route::group(['middleware' => ['permission:managePropertyType']], function () {
        Route::prefix('propertyType')->group(function () {
            Route::post('/store', [PropertyTypeController::class, 'store']);
            Route::get('/show/{id}', [PropertyTypeController::class, 'show']);
            Route::put('/update/{id}', [PropertyTypeController::class, 'update']);
            Route::delete('/destroy/{id}', [PropertyTypeController::class, 'destroy']);
            Route::put('/block/{id}', [PropertyTypeController::class, 'block']);
            Route::put('/unblock/{id}', [PropertyTypeController::class, 'unblock']);
        });
    });


    //Gestion de la liste des documents
    Route::group(['middleware' => ['permission:manageDocument']], function () {
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
    Route::group(['middleware' => ['permission:manageVerificationDocument']], function () {
        
        Route::prefix('verificationdocument')->group(function () {
            Route::get('/index', [VerificationDocumentController::class, 'index']);
            Route::get('/show/{id}', [VerificationDocumentController::class, 'show']);
            Route::post('/update', [VerificationDocumentController::class, 'changeDocument']);
            Route::post('/hote/valider/all', [VerificationDocumentController::class, 'validateDocuments']);
            Route::post('/hote/valider/one', [VerificationDocumentController::class, 'validateDocument']);
        });
    });

    //Gestion des commissions
    Route::group(['middleware' => ['permission: manageCommission']], function () {
        
        Route::prefix('commission')->group(function () {
            Route::post('/updateCommissionForSpecifiqueUser', [CommissionController::class, 'updateCommissionForSpecifiqueUser']);
            Route::put('/updateCommissionValueByAnother', [CommissionController::class, 'updateCommissionValueByAnother']);
            Route::get('/usersWithCommission/{commission}', [CommissionController::class, 'usersWithCommission']);
        });
    });

  //Gestion des préférences des utilisateurs
    Route::group(['middleware' => ['permission:manageUserPreference']], function () {
    
      
        Route::prefix('users/preference')->group(function () {
            Route::post('/preference/add', [User_preferenceController::class, 'AddUserPreferences']);
            Route::get('/userPreferences', [UserController::class, 'showUserPreferences']);
        });

    });


   // Gestion des Notifications
   Route::group(['middleware' => ['permission:manageNotification']], function () {
     Route::prefix('notifications')->group(function () {
        Route::get('/index', [NotificationController::class, 'index']);
        Route::post('/store', [NotificationController::class, 'store']);
        Route::delete('/destroy/{id}', [NotificationController::class, 'destroy']);
    });
   });

    // Gestion logement côté hôte
    Route::group(['middleware' => ['permission: manageHousing']], function () {
        
        Route::prefix('logement')->group(function () {
            Route::get('/index', [HousingController::class, 'index']);
            Route::post('/store', [HousingController::class, 'addHousing']);
            Route::get('/show/{id}', [HousingController::class, 'show']);
            Route::put('/update/{id}', [HousingController::class, 'update']);
            Route::delete('/destroy/{id}', [HousingController::class, 'destroy']);
            Route::put('/block/{id}', [HousingController::class, 'block']);
            Route::put('/unblock/{id}', [HousingController::class, 'unblock']);

        });
    });
    Route::group(['middleware' => ['permission: managePromotion']], function () {

    });

    Route::group(['middleware' => ['permission: manageReduction']], function () {

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



