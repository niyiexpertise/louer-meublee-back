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
    return $request->user();
});

// Gestion des type de logement
Route::prefix('housingtype')->group(function () {
    Route::get('/index', [HousingTypeController::class, 'index']);
    Route::post('/store', [HousingTypeController::class, 'store']);
    Route::get('/show/{id}', [HousingTypeController::class, 'show']);
    Route::put('/update/{id}', [HousingTypeController::class, 'update']);
    Route::delete('/destroy/{id}', [HousingTypeController::class, 'destroy']);
    Route::put('/block/{id}', [HousingTypeController::class, 'block']);
    Route::put('unblock/{id}', [HousingTypeController::class, 'unblock']);
});

// Gestion des type de sejour
Route::prefix('typestays')->group(function () {
    Route::get('/index', [TypeStayController::class, 'index']);
    Route::post('/store', [TypeStayController::class, 'store']);
    Route::get('/show/{id}', [TypeStayController::class, 'show']);
    Route::put('/update/{id}', [TypeStayController::class, 'update']);
    Route::delete('/destroy/{id}', [TypeStayController::class, 'destroy']);
    Route::put('/block/{id}', [TypeStayController::class, 'block']);
    Route::put('/unblock/{id}', [TypeStayController::class, 'unblock']);
});

//Gestion  des critères de note .

Route::prefix('criteria')->group(function () {
    Route::get('/index', [CriteriaController::class, 'index']);
    Route::post('/store', [CriteriaController::class, 'store']);
    Route::get('/show/{id}', [CriteriaController::class, 'show']);
    Route::put('/update/{id}', [CriteriaController::class, 'update']);
    Route::delete('/destroy/{id}', [CriteriaController::class, 'destroy']);
    Route::put('/block/{id}', [CriteriaController::class, 'block']);
    Route::put('/unblock/{id}', [CriteriaController::class, 'unblock']);
});


//Gestion des équipements.


Route::prefix('equipment')->group(function () {
    Route::get('/index', [EquipementController::class, 'index']);
    Route::get('/indexEquipmentCategories', [EquipementController::class, 'indexEquipmentCategories']);
    Route::post('/store', [EquipementController::class, 'store']);
    Route::get('/show/{id}', [EquipementController::class, 'show']);
    Route::get('/showCategories/{id}', [EquipementController::class, 'showCategories']);
    Route::put('/update/{id}', [EquipementController::class, 'update']);
    Route::delete('/destroy/{id}', [EquipementController::class, 'destroy']);
    Route::put('/block/{id}', [EquipementController::class, 'block']);
    Route::put('/unblock/{id}', [EquipementController::class, 'unblock']);
});

//Gestion des catégories.

Route::prefix('category')->group(function () {
    Route::get('/indexCategorieEquipments', [CategorieController::class, 'indexCategorieEquipments']);
    Route::get('/index', [CategorieController::class, 'index']);
    Route::post('/store', [CategorieController::class, 'store']);
    Route::get('/showEquipments/{id}', [CategorieController::class, 'showEquipments']);
    Route::get('/show/{id}', [CategorieController::class, 'show']);
    Route::put('/update/{id}', [CategorieController::class, 'update']);
    Route::delete('/destroy/{id}', [CategorieController::class, 'destroy']);
    Route::put('/block/{id}', [CategorieController::class, 'block']);
    Route::put('/unblock/{id}', [CategorieController::class, 'unblock']);
});

//Gestion des préférences.

Route::prefix('preference')->group(function () {
    Route::get('/index', [PreferenceController::class, 'index']);
    Route::post('/store', [PreferenceController::class, 'store']);
    Route::get('/show/{id}', [PreferenceController::class, 'show']);
    Route::put('/update/{id}', [PreferenceController::class, 'update']);
    Route::delete('/destroy/{id}', [PreferenceController::class, 'destroy']);
    Route::put('/block/{id}', [PreferenceController::class, 'block']);
    Route::put('/unblock/{id}', [PreferenceController::class, 'unblock']);
});

//Gestion des types de propriété.

Route::prefix('propertyType')->group(function () {
    Route::get('/index', [PropertyTypeController::class, 'index']);
    Route::post('/store', [PropertyTypeController::class, 'store']);
    Route::get('/show/{id}', [PropertyTypeController::class, 'show']);
    Route::put('/update/{id}', [PropertyTypeController::class, 'update']);
    Route::delete('/destroy/{id}', [PropertyTypeController::class, 'destroy']);
    Route::put('/block/{id}', [PropertyTypeController::class, 'block']);
    Route::put('/unblock/{id}', [PropertyTypeController::class, 'unblock']);
});


//Gestion des langues.

Route::prefix('language')->group(function () {
    Route::get('/index', [LanguageController::class, 'index']);
    Route::post('/store', [LanguageController::class, 'store']);
    Route::get('/show/{id}', [LanguageController::class, 'show']);
    Route::put('/update/{id}', [LanguageController::class, 'update']);
    Route::delete('/destroy/{id}', [LanguageController::class, 'destroy']);
    Route::put('/block/{id}', [LanguageController::class, 'block']);
    Route::put('/unblock/{id}', [LanguageController::class, 'unblock']);
});


//Gestion des accessibilitéq.

Route::prefix('accessibility')->group(function () {
    Route::get('/index', [AccessibilityController::class, 'index']);
    Route::post('/store', [AccessibilityController::class, 'store']);
    Route::get('/show/{id}', [AccessibilityController::class, 'show']);
    Route::put('/update/{id}', [AccessibilityController::class, 'update']);
    Route::delete('/destroy/{id}', [AccessibilityController::class, 'destroy']);
    Route::put('/block/{id}', [AccessibilityController::class, 'block']);
    Route::put('/unblock/{id}', [AccessibilityController::class, 'unblock']);
});

// Gestion des commentaires

Route::prefix('review')->group(function () {
    Route::get('/index', [ReviewController::class, 'index']);
    Route::post('/store', [ReviewController::class, 'store']);
    Route::get('/show/{id}', [ReviewController::class, 'show']);
    Route::put('/update/{id}', [ReviewController::class, 'update']);
    Route::delete('/destroy/{id}', [ReviewController::class, 'destroy']);
    Route::put('/block/{id}', [ReviewController::class, 'block']);
    Route::put('/unblock/{id}', [ReviewController::class, 'unblock']);
});

//Gestion des Users.

Route::prefix('users')->group(function () {
    Route::get('/index', [UserController::class, 'index']);
    Route::post('/register', [UserController::class, 'register']);
    Route::get('/show/{id}', [UserController::class, 'show']);
    Route::put('/update/{id}', [UserController::class, 'update']);
    Route::delete('/destroy/{id}', [UserController::class, 'destroy']);
    Route::put('/block/{id}', [UserController::class, 'block']);
    Route::put('/unblock/{id}', [UserController::class, 'unblock']);
    Route::post('/preference/add', [User_preferenceController::class, 'AddUserPreferences']);
    Route::get('/userReviews', [UserController::class, 'userReviews']);
    Route::get('/userLanguages', [UserController::class, 'userLanguages']);
    Route::get('/userPreferences', [UserController::class, 'showUserPreferences']);
    Route::post('/update_profile_photo', [UserController::class, 'updateProfilePhoto']);
    Route::put('/block/{id}', [UserController::class, 'block']);
    Route::put('/unblock/{id}', [UserController::class, 'unblock']);
    Route::get('/pays/{pays}', [UserController::class, 'getUsersByCountry']);
    Route::put('/update_password', [UserController::class, 'updatePassword']);
    Route::get('/travelers', [UserController::class, 'getUsersWithRoletraveler']);
    Route::get('/hotes', [UserController::class, 'getUsersWithRoleHost']);
    Route::get('/admins', [UserController::class, 'getUsersWithRoleAdmin']);
    Route::put('/update', [UserController::class, 'updateUser']);
    Route::get('/result/demande', [VerificationDocumentController::class, 'userVerificationRequests']);


    
    
});



//Gestion des types de role possible.

Route::prefix('role')->group(function () {
    Route::get('/index', [RoleController::class, 'index']);
    Route::post('/store', [RoleController::class, 'store']);
    Route::get('/show/{id}', [RoleController::class, 'show']);
    Route::delete('/destroy/{id}', [RoleController::class, 'destroy']);
    Route::put('/block/{id}', [RoleController::class, 'block']);
    Route::put('/unblock/{id}', [RoleController::class, 'unblock']);
});


//Gestion des types de permission possible.

Route::prefix('permission')->group(function () {
    Route::get('/index', [PermissionController::class, 'index']);
    Route::post('/store', [PermissionController::class, 'store']);
    Route::get('/show/{id}', [PermissionController::class, 'show']);
    Route::put('/update/{id}', [PermissionController::class, 'update']);
    Route::delete('/destroy/{id}', [PermissionController::class, 'destroy']);
    Route::put('/block/{id}', [PermissionController::class, 'block']);
    Route::put('/unblock/{id}', [PermissionController::class, 'unblock']);
});

//Gestion des accès des utilisateurs.

// Route::group(['middleware' => ['role:manager']], function () {
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
        Route::get('/rolesPerms/{id}', [AuthController::class, 'rolesPerms']);
        Route::get('/rolesPermsCount/{id}', [AuthController::class, 'rolesPermsCount']);
        Route::post('/switchToHote', [AuthController::class, 'switchToHote']);
        Route::post('/switchToTraveler', [AuthController::class, 'switchToTraveler']);
        // Route::group(['middleware' => ['permission:publish articles']], function () { 
            Route::get('/usersRoles', [AuthController::class, 'usersRoles']);
        // });
    });
// });
// Route::group(['middleware' => ['permission:publish articles']], function () { ... });
// Route::group(['middleware' => ['role_or_permission:publish articles']], function () { ... });



// Gestion des notifications

Route::prefix('notifications')->group(function () {
    Route::get('/index', [NotificationController::class, 'index']);
    Route::post('/store', [NotificationController::class, 'store']);
    Route::delete('/destroy/{id}', [NotificationController::class, 'destroy']);
    Route::get('users', [NotificationController::class, 'getUserNotifications']);
});

//Gestion de la liste des documents à soumettre pour la demande à être hôte
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
    Route::get('/document_actif', [DocumentController::class, 'document_actif']);
    Route::get('/document_inactif', [DocumentController::class, 'document_inactif']);
});

//Verification document
Route::prefix('verificationdocument')->group(function () {
    Route::get('/index', [VerificationDocumentController::class, 'index']);
    Route::post('/store', [VerificationDocumentController::class, 'store']);
    Route::get('/show/{id}', [VerificationDocumentController::class, 'show']);
    Route::post('/update', [VerificationDocumentController::class, 'changeDocument']);
    Route::delete('/destroy/{id}', [VerificationDocumentController::class, 'destroy']);
    Route::post('/hote/valider/all', [VerificationDocumentController::class, 'validateDocuments']);
    Route::post('/hote/valider/one', [VerificationDocumentController::class, 'validateDocument']);
});

//Gestion des commissions

Route::prefix('commission')->group(function () {
    Route::post('/updateCommissionForSpecifiqueUser', [CommissionController::class, 'updateCommissionForSpecifiqueUser']);
    Route::put('/updateCommissionValueByAnother', [CommissionController::class, 'updateCommissionValueByAnother']);
    Route::get('/usersWithCommission/{commission}', [CommissionController::class, 'usersWithCommission']);
});






