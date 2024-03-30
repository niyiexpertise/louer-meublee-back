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

//Gestion des types de role possible.

Route::prefix('role')->group(function () {
    Route::get('/index', [RoleController::class, 'index']);
    Route::post('/store', [RoleController::class, 'store']);
    Route::get('/show/{id}', [RoleController::class, 'show']);
    Route::put('/update/{id}', [RoleController::class, 'update']);
    Route::delete('/destroy/{id}', [RoleController::class, 'destroy']);
    Route::put('/block/{id}', [RoleController::class, 'block']);
    Route::put('/unblock/{id}', [RoleController::class, 'unblock']);
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
    Route::post('/store', [EquipementController::class, 'store']);
    Route::get('/show/{id}', [EquipementController::class, 'show']);
    Route::put('/update/{id}', [EquipementController::class, 'update']);
    Route::delete('/destroy/{id}', [EquipementController::class, 'destroy']);
    Route::put('/block/{id}', [EquipementController::class, 'block']);
    Route::put('/unblock/{id}', [EquipementController::class, 'unblock']);
});

//Gestion des catégories.

Route::prefix('category')->group(function () {
    Route::get('/index', [CategorieController::class, 'index']);
    Route::post('/store', [CategorieController::class, 'store']);
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
});






