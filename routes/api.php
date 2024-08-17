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
use App\Http\Controllers\TypeDemandeController;
use App\Http\Controllers\VerificationDocumentPartenaireController;
use App\Http\Controllers\UserPartenaireController;
use App\Http\Controllers\DashboardPartenaireController;
use App\Http\Controllers\AddHousingController;
use App\Http\Controllers\AddHousingZController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashBoardTravelerController;
use App\Http\Controllers\AuditController;

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

Route::middleware(['auth:sanctum', '2fa'])->group(function () {
    Route::get('/user', [LoginController::class, 'checkAuth']);
    Route::get('/returnAuthCommission', [LoginController::class, 'returnAuthCommission']);
    Route::post('/users/logout', [LogoutController::class, 'logout']);

});

//Inscription et Connexion
Route::prefix('users')->group(function () {
    Route::post('/register', [InscriptionController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/verification_code', [LoginController::class, 'verification_code'])->middleware(['auth:sanctum']);
    Route::post('/new_code/{id}', [LoginController::class, 'new_code']);
    Route::post('/password_recovery_start_step', [LoginController::class, 'password_recovery_start_step']);
    Route::post('/password_recovery_end_step', [LoginController::class, 'password_recovery_end_step']);
});



/**route nécéssitant l'authentification */

Route::middleware(['auth:sanctum', '2fa'])->group(function () {

    //Gestion des catégories.
    Route::prefix('category')->group(function () {

        Route::middleware(['role_or_permission:superAdmin|admin|Managecategory.VerifiednotBlockDelete'])->group(function () {
            Route::get('/VerifiednotBlockDelete', [CategorieController::class, 'VerifiednotBlockDelete'])->name('category.VerifiednotBlockDelete');
        });
        Route::middleware(['role_or_permission:superAdmin|admin|Managecategory.store'])->group(function () {
            Route::post('/store', [CategorieController::class, 'store'])->name('category.store');
        });
        Route::middleware(['role_or_permission:superAdmin|admin|Managecategory.show'])->group(function () {
            Route::get('/show/{id}', [CategorieController::class, 'show'])->name('category.show');
        });

        Route::middleware(['role_or_permission:superAdmin|admin|Managecategory.destroy'])->group(function () {
            Route::delete('/destroy/{id}', [CategorieController::class, 'destroy'])->name('category.destroy');
        });

        Route::middleware(['role_or_permission:superAdmin|admin|Managecategory.block'])->group(function () {
            Route::put('/block/{id}', [CategorieController::class, 'block'])->name('category.block');
        });

        Route::middleware(['role_or_permission:superAdmin|admin|Managecategory.unblock'])->group(function () {
            Route::put('/unblock/{id}', [CategorieController::class, 'unblock'])->name('category.unblock');
        });

        Route::middleware(['role_or_permission:superAdmin|admin|Managecategory.VerifiednotBlocknotDelete'])->group(function () {
            Route::get('/VerifiednotBlocknotDelete', [CategorieController::class, 'VerifiednotBlocknotDelete'])->name('category.VerifiednotBlocknotDelete');
        });

        Route::middleware(['role_or_permission:superAdmin|admin|Managecategory.VerifiedBlocknotDelete'])->group(function () {
            Route::get('/VerifiedBlocknotDelete', [CategorieController::class, 'VerifiedBlocknotDelete'])->name('category.VerifiedBlocknotDelete');
        });

        Route::middleware(['role_or_permission:superAdmin|admin|Managecategory.updateName'])->group(function () {
            Route::put('/updateName/{id}', [CategorieController::class, 'updateName'])->name('category.updateName');
        });


        Route::middleware(['role_or_permission:superAdmin|admin|Managecategory.updateIcone'])->group(function () {
            Route::post('/updateIcone/{id}', [CategorieController::class, 'updateIcone'])->name('category.updateIcone');
        });

        Route::group(['middleware' => ['role_or_permission:superAdmin|Managecategory.makeVerified']], function () {
            Route::put('/makeVerified/{id}', [CategorieController::class, 'makeVerified'])->name('category.makeVerified');
        });

    });




    ///Gestion des types de logement
    Route::prefix('housingtype')->group(function () {
        Route::middleware(['role_or_permission:admin|superAdmin|Managehousingtype.store'])->group(function () {
            Route::post('/store', [HousingTypeController::class, 'store'])->name('housingtype.store');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managehousingtype.show'])->group(function () {
            Route::get('/show/{id}', [HousingTypeController::class, 'show'])->name('housingtype.show');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managehousingtype.updateName'])->group(function () {
            Route::put('/updateName/{id}', [HousingTypeController::class, 'updateName'])->name('housingtype.updateName');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managehousingtype.updateIcone'])->group(function () {
            Route::post('/updateIcone/{id}', [HousingTypeController::class, 'updateIcone'])->name('housingtype.updateIcone');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managehousingtype.update'])->group(function () {
            Route::put('/update/{id}', [HousingTypeController::class, 'update'])->name('housingtype.update');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managehousingtype.destroy'])->group(function () {
            Route::delete('/destroy/{id}', [HousingTypeController::class, 'destroy'])->name('housingtype.destroy');
        });
        Route::middleware(['role_or_permission:admin|superAdmin|Managehousingtype.destroymultiple'])->group(function () {
            Route::delete('/destroymultiple', [HousingTypeController::class, 'destroymultiple'])->name('housingtype.destroymultiple');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managehousingtype.block'])->group(function () {
            Route::put('/block/{id}', [HousingTypeController::class, 'block'])->name('housingtype.block');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managehousingtype.unblock'])->group(function () {
            Route::put('/unblock/{id}', [HousingTypeController::class, 'unblock'])->name('housingtype.unblock');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managehousingtype.indexBlock'])->group(function () {
            Route::get('/indexBlock', [HousingTypeController::class, 'indexBlock'])->name('housingtype.indexBlock');
        });
    });




    //Gestion  des critères de note .
    Route::prefix('criteria')->group(function () {
        Route::middleware(['role_or_permission:admin|superAdmin|Managecriteria.store'])->group(function () {
            Route::post('/store', [CriteriaController::class, 'store'])->name('criteria.store');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managecriteria.show'])->group(function () {
            Route::get('/show/{id}', [CriteriaController::class, 'show'])->name('criteria.show');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managecriteria.updateName'])->group(function () {
            Route::put('/updateName/{id}', [CriteriaController::class, 'updateName'])->name('criteria.updateName');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managecriteria.updateIcone'])->group(function () {
            Route::post('/updateIcone/{id}', [CriteriaController::class, 'updateIcone'])->name('criteria.updateIcone');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managecriteria.destroy'])->group(function () {
            Route::delete('/destroy/{id}', [CriteriaController::class, 'destroy'])->name('criteria.destroy');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managecriteria.block'])->group(function () {
            Route::put('/block/{id}', [CriteriaController::class, 'block'])->name('criteria.block');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managecriteria.unblock'])->group(function () {
            Route::put('/unblock/{id}', [CriteriaController::class, 'unblock'])->name('criteria.unblock');
        });
    });


    //Gestion des types de role possible sous forme crud.
    Route::prefix('role')->name('role.')->group(function () {
        Route::middleware(['role_or_permission:superAdmin|Managerole.index'])->group(function () {
            Route::get('/index', [RoleController::class, 'index'])->name('index');
        });

        Route::middleware(['role_or_permission:superAdmin|Managerole.store'])->group(function () {
            Route::post('/store', [RoleController::class, 'store'])->name('store');
        });

        Route::middleware(['role_or_permission:superAdmin|Managerole.show'])->group(function () {
            Route::get('/show/{id}', [RoleController::class, 'show'])->name('show');
        });

        Route::middleware(['role_or_permission:superAdmin|Managerole.destroy'])->group(function () {
            Route::delete('/destroy/{id}', [RoleController::class, 'destroy'])->name('destroy');
        });
    });



    //Gestion des équipements.
    Route::prefix('equipment')->name('equipment.')->group(function () {
        
        Route::middleware(['role_or_permission:admin|superAdmin|Manageequipment.VerifiedBlocknotDelete'])->group(function () {
            Route::get('/VerifiedBlocknotDelete', [EquipementController::class, 'VerifiedBlocknotDelete'])->name('VerifiedBlocknotDelete');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Manageequipment.VerifiednotBlocknotDelete'])->group(function () {
            Route::get('/VerifiednotBlocknotDelete', [EquipementController::class, 'VerifiednotBlocknotDelete'])->name('VerifiednotBlocknotDelete');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Manageequipment.VerifiednotBlockDelete'])->group(function () {
            Route::get('/VerifiednotBlockDelete', [EquipementController::class, 'VerifiednotBlockDelete'])->name('VerifiednotBlockDelete');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Manageequipment.store'])->group(function () {
            Route::post('/store', [EquipementController::class, 'store'])->name('store');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Manageequipment.show'])->group(function () {
            Route::get('/show/{id}', [EquipementController::class, 'show'])->name('show');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Manageequipment.updateName'])->group(function () {
            Route::put('/updateName/{id}', [EquipementController::class, 'updateName'])->name('updateName');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Manageequipment.updateCategory'])->group(function () {
            Route::put('/updateCategory/{equipmentCategory}', [EquipementController::class, 'updateCategory'])->name('updateCategory');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Manageequipment.updateIcone'])->group(function () {
            Route::post('/updateIcone/{id}', [EquipementController::class, 'updateIcone'])->name('updateIcone');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Manageequipment.destroy'])->group(function () {
            Route::delete('/destroy/{id}', [EquipementController::class, 'destroy'])->name('destroy');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Manageequipment.block'])->group(function () {
            Route::put('/block/{id}', [EquipementController::class, 'block'])->name('block');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Manageequipment.unblock'])->group(function () {
            Route::put('/unblock/{id}', [EquipementController::class, 'unblock'])->name('unblock');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Manageequipment.indexUnverified'])->group(function () {
            Route::get('/indexUnverified', [EquipementController::class, 'indexUnverified'])->name('indexUnverified');
        });
        Route::group(['middleware' => ['role_or_permission:superAdmin|Manageequipment.makeVerified']], function () {
            Route::put('/makeVerified/{id}', [EquipementController::class, 'makeVerified'])->name('equipment.makeVerified');
        });
     });


   //Administration des permissions et role

        Route::middleware(['auth:sanctum', '2fa'])->prefix('users')->group(function () {
            Route::middleware(['role_or_permission:superAdmin|Manageusers.assignPermsToRole'])->group(function () {
                Route::post('/assignPermsToRole/{role}', [AuthController::class, 'assignPermsToRole'])->name('users.assignPermsToRole');
            });

            Route::middleware(['role_or_permission:superAdmin|Manageusers.RevokePermsToRole'])->group(function () {
                Route::post('/RevokePermsToRole/{role}', [AuthController::class, 'RevokePermsToRole'])->name('users.RevokePermsToRole');
            });

            Route::middleware(['role_or_permission:superAdmin|Manageusers.getUserRoles'])->group(function () {
                Route::get('/getUserRoles/{id}', [AuthController::class, 'getUserRoles'])->name('users.getUserRoles');
            });

            Route::middleware(['role_or_permission:superAdmin|Manageusers.assignRoleToUser'])->group(function () {
                Route::post('/assignRoleToUser/{id}/{role}', [AuthController::class, 'assignRoleToUser'])->name('users.assignRoleToUser');
            });

            Route::middleware(['role_or_permission:superAdmin|Manageusers.RevokeRoleToUser'])->group(function () {
                Route::post('/RevokeRoleToUser/{id}/{role}', [AuthController::class, 'RevokeRoleToUser'])->name('users.RevokeRoleToUser');
            });

            Route::middleware(['role_or_permission:superAdmin|Manageusers.assignPermsToUser'])->group(function () {
                Route::post('/assignPermsToUser/{id}', [AuthController::class, 'assignPermsToUser'])->name('users.assignPermsToUser');
            });

            Route::middleware(['role_or_permission:superAdmin|Manageusers.revokePermsToUser'])->group(function () {
                Route::post('/revokePermsToUser/{id}', [AuthController::class, 'revokePermsToUser'])->name('users.revokePermsToUser');
            });

            Route::middleware(['role_or_permission:superAdmin|Manageusers.getUserPerms'])->group(function () {
                Route::get('/getUserPerms/{id}', [AuthController::class, 'getUserPerms'])->name('users.getUserPerms');
            });

            Route::middleware(['role_or_permission:superAdmin|Manageusers.usersWithRole'])->group(function () {
                Route::get('/usersWithRole/{role}', [AuthController::class, 'usersWithRole'])->name('users.usersWithRole');
            });


            Route::middleware(['role_or_permission:superAdmin|Manageusers.usersWithPerm'])->group(function () {
                Route::get('/usersWithPerm/{permission}', [AuthController::class, 'usersWithPerm'])->name('users.usersWithPerm');
            });

            Route::middleware(['role_or_permission:superAdmin|Manageusers.rolesPerms'])->group(function () {
                Route::get('/rolesPerms/{role}', [AuthController::class, 'rolesPerms'])->name('users.rolesPerms');
            });

            Route::middleware(['role_or_permission:superAdmin|Manageusers.rolesPermsCount'])->group(function () {
                Route::get('/rolesPermsCount/{role}', [AuthController::class, 'rolesPermsCount'])->name('users.rolesPermsCount');
            });

            Route::middleware(['role_or_permission:superAdmin|Manageusers.usersCountByRole'])->group(function () {
                Route::get('/usersCountByRole', [AuthController::class, 'usersCountByRole'])->name('users.usersCountByRole');
            });

            Route::middleware(['role_or_permission:superAdmin|Manageusers.usersRoles'])->group(function () {
                Route::get('/usersRoles', [AuthController::class, 'usersRoles'])->name('users.usersRoles');
            });

            Route::post('/switchToAnotherRole/{roleName}', [AuthController::class, 'switchToAnotherRole']);
        });


    // Gestion des utilisateurs du côté voyageur

    Route::prefix('users')->group(function () {

          Route::get('/userReviews', [UserController::class, 'userReviews'])->name('users.userReviews');

            Route::get('/userLanguages', [UserController::class, 'userLanguages'])->name('users.userLanguages');

            Route::post('/update_profile_photo', [UserController::class, 'updateProfilePhoto'])->name('users.updateProfilePhoto');

            Route::put('/update_password', [UserController::class, 'updatePassword'])->name('users.updatePassword');

            Route::put('/update', [UserController::class, 'updateUser'])->name('users.updateUser');

             Route::get('/getUserReservationCount', [UserController::class, 'getUserReservationCount'])->name('users.getUserReservationCount');


            Route::get('/result/demande', [VerificationDocumentController::class, 'userVerificationRequests'])->name('users.userVerificationRequests');

            Route::post('/verificationdocument/store', [VerificationDocumentController::class, 'store'])->name('verificationdocument.store');

            Route::get('/notifications', [NotificationController::class, 'getUserNotifications'])->name('notifications.getUserNotifications');

            Route::post('/verificationdocument/update', [VerificationDocumentController::class, 'changeDocument'])->name('verificationdocument.changeDocument');

            Route::post('/verificationdocumentpartenaire/store', [VerificationDocumentPartenaireController::class, 'store'])->name('verificationdocumentpartenaire.store');
            Route::post('/verificationdocumentpartenaire/update', [VerificationDocumentPartenaireController::class, 'changeDocument'])->name('verificationdocumentpartenaire.changeDocument');
            Route::get('/result/demandepartenaire', [VerificationDocumentPartenaireController::class, 'userVerificationRequests'])->name('users.userVerificationRequestspartenaire');

    });


    // Gestion des utilisateurs du côté de l'admin
    Route::prefix('users')->group(function () {
        Route::middleware(['role_or_permission:superAdmin|admin|Manageusers.index'])->group(function () {
            Route::get('/index', [UserController::class, 'index'])->name('users.index');
        });

        Route::middleware(['role_or_permission:superAdmin|admin|Manageusers.destroy'])->group(function () {
            Route::delete('/destroy/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        Route::middleware(['role_or_permission:superAdmin|admin|Manageusers.block'])->group(function () {
            Route::put('/block/{id}', [UserController::class, 'block'])->name('users.block');
        });

        Route::middleware(['role_or_permission:superAdmin|admin|Manageusers.unblock'])->group(function () {
            Route::put('/unblock/{id}', [UserController::class, 'unblock'])->name('users.unblock');
        });

        Route::middleware(['role_or_permission:superAdmin|admin|Manageusers.getUsersByCountry'])->group(function () {
            Route::get('/pays/{pays}', [UserController::class, 'getUsersByCountry'])->name('users.getUsersByCountry');
        });

        Route::middleware(['role_or_permission:superAdmin|admin|Manageusers.getUsersWithRoletraveler'])->group(function () {
            Route::get('/travelers', [UserController::class, 'getUsersWithRoletraveler'])->name('users.getUsersWithRoletraveler');
        });

        Route::middleware(['role_or_permission:superAdmin|admin|Manageusers.getUsersWithRoleHost'])->group(function () {
            Route::get('/hotes', [UserController::class, 'getUsersWithRoleHost'])->name('users.getUsersWithRoleHost');
        });
        Route::middleware(['role_or_permission:superAdmin|admin|Manageusers.getUsersPartenaire'])->group(function () {
            Route::get('/partenaires', [UserPartenaireController::class, 'getUsersPartenaire'])->name('users.getUsersPartenaire');
        });


        Route::middleware(['role_or_permission:superAdmin|admin|Manageusers.getUsersWithRoleAdmin'])->group(function () {
            Route::get('/admins', [UserController::class, 'getUsersWithRoleAdmin'])->name('users.getUsersWithRoleAdmin');
        });


        Route::get('/detail/{userId}', [UserController::class, 'getUserDetails'])->name('users.getUserDetails');

    });




    // Gestion des permissions sous forme de crud
    Route::prefix('permission')->group(function () {
        Route::middleware(['role_or_permission:superAdmin|Managepermission.index'])->group(function () {
            Route::get('/index', [PermissionController::class, 'index'])->name('permission.index');
        });
        Route::middleware(['role_or_permission:superAdmin|Managepermission.indexbycategorie'])->group(function () {
            Route::get('/indexbycategorie', [PermissionController::class, 'indexbycategorie'])->name('permission.indexbycategorie');
        });

        Route::middleware(['role_or_permission:superAdmin|Managepermission.store'])->group(function () {
            Route::post('/store', [PermissionController::class, 'store'])->name('permission.store');
        });

        Route::middleware(['role_or_permission:superAdmin|Managepermission.show'])->group(function () {
            Route::get('/show/{id}', [PermissionController::class, 'show'])->name('permission.show');
        });
        Route::middleware(['role_or_permission:superAdmin|Managepermission.block'])->group(function () {
            Route::put('/block/{id}', [PermissionController::class, 'block'])->name('permission.block');
        });

        Route::middleware(['role_or_permission:superAdmin|Managepermission.unblock'])->group(function () {
            Route::put('/unblock/{id}', [PermissionController::class, 'unblock'])->name('permission.unblock');
        });
    });


    // Gestion des commentaires
    Route::prefix('review')->group(function () {
        Route::middleware(['role_or_permission:traveler|superAdmin|hote|admin|Managereview.store'])->group(function () {
            Route::post('/store', [ReviewController::class, 'store'])->name('review.store');
        });

        Route::middleware(['role_or_permission:traveler|superAdmin|hote|admin|Managereview.show'])->group(function () {
            Route::get('/show/{id}', [ReviewController::class, 'show'])->name('review.show');
        });

        Route::middleware(['role_or_permission:traveler|superAdmin|hote|admin|Managereview.update'])->group(function () {
            Route::put('/update/{id}', [ReviewController::class, 'update'])->name('review.update');
        });

        Route::middleware(['role_or_permission:traveler|superAdmin|hote|admin|Managereview.destroy'])->group(function () {
            Route::delete('/destroy/{id}', [ReviewController::class, 'destroy'])->name('review.destroy');
        });
    });

    // Gestion des chats
    Route::prefix('chats')->group(function () {
        Route::post('/createMessage/{recipientId}/{content}', [ChatController::class, 'createMessage']);
        Route::get('/getChatsByModelType/{modelType}', [ChatController::class, 'getChatsByModelType']);
        Route::get('/getChatsByModelTypeAndId/{modelType}/{modelId}', [ChatController::class, 'getChatsByModelTypeAndId']);
        Route::post('/markMessageAsRead/{messageId}', [ChatController::class, 'markMessageAsRead']);
        Route::post('/markMessageAsUnRead/{messageId}', [ChatController::class, 'markMessageAsUnRead']);
        Route::get('/getMessagesByChatId/{chatId}', [ChatController::class, 'getMessagesByChatId']);
    });

    //Gestion de quelques stats
    Route::prefix('stat')->middleware(['role:superAdmin|admin'])->group(function () {
        Route::get('/getUsersGroupedByCountry', [AdminReservationController::class, 'getUsersGroupedByCountry']);
        Route::get('/getHousingGroupedByCountry', [AdminReservationController::class, 'getHousingGroupedByCountry']);
        Route::get('/getReservationGroupedByCountry', [AdminReservationController::class, 'getReservationGroupedByCountry']);
        Route::get('/getNumberOfReservationGroupedByTraveler', [AdminReservationController::class, 'getNumberOfReservationGroupedByTraveler']);
        Route::get('/getNumberOfReservationGroupedByHousing', [AdminReservationController::class, 'getNumberOfReservationGroupedByHousing']);
    });


    //Gestion des langues sous formes de CRUD.
    Route::prefix('language')->group(function () {
        Route::middleware(['role_or_permission:admin|superAdmin|Managelanguage.store'])->group(function () {
            Route::post('/store', [LanguageController::class, 'store'])->name('language.store');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managelanguage.show'])->group(function () {
            Route::get('/show/{id}', [LanguageController::class, 'show'])->name('language.show');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managelanguage.updateName'])->group(function () {
            Route::put('/updateName/{id}', [LanguageController::class, 'updateName'])->name('language.updateName');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managelanguage.updateIcone'])->group(function () {
            Route::post('/updateIcone/{id}', [LanguageController::class, 'updateIcone'])->name('language.updateIcone');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managelanguage.destroy'])->group(function () {
            Route::delete('/destroy/{id}', [LanguageController::class, 'destroy'])->name('language.destroy');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managelanguage.block'])->group(function () {
            Route::put('/block/{id}', [LanguageController::class, 'block'])->name('language.block');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managelanguage.unblock'])->group(function () {
            Route::put('/unblock/{id}', [LanguageController::class, 'unblock'])->name('language.unblock');
        });
    });


    //Gestion des préférences.
    Route::prefix('preference')->group(function () {
        Route::middleware(['role_or_permission:admin|superAdmin|Managepreference.store'])->group(function () {
            Route::post('/store', [PreferenceController::class, 'store'])->name('preference.store');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managepreference.indexUnverified'])->group(function () {
            Route::get('/indexUnverified', [PreferenceController::class, 'indexUnverified'])->name('preference.indexUnverified');
        });

        Route::middleware(['role_or_permission:hote|superAdmin|Managepreference.storeUnexist'])->group(function () {
            Route::post('/storeUnexist/{housingId}', [PreferenceController::class, 'storeUnexist'])->name('preference.storeUnexist');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managepreference.show'])->group(function () {
            Route::get('/show/{id}', [PreferenceController::class, 'show'])->name('preference.show');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managepreference.updateName'])->group(function () {
            Route::put('/updateName/{id}', [PreferenceController::class, 'updateName'])->name('preference.updateName');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managepreference.updateIcone'])->group(function () {
            Route::post('/updateIcone/{id}', [PreferenceController::class, 'updateIcone'])->name('preference.updateIcone');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managepreference.destroy'])->group(function () {
            Route::delete('/destroy/{id}', [PreferenceController::class, 'destroy'])->name('preference.destroy');
        });

        Route::middleware(['role_or_permission:superAdmin|Managepreference.makeVerified'])->group(function () {
            Route::put('/makeVerified/{id}', [PreferenceController::class, 'makeVerified'])->name('preference.makeVerified');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managepreference.block'])->group(function () {
            Route::put('/block/{id}', [PreferenceController::class, 'block'])->name('preference.block');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managepreference.unblock'])->group(function () {
            Route::put('/unblock/{id}', [PreferenceController::class, 'unblock'])->name('preference.unblock');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managepreference.VerifiedBlocknotDelete'])->group(function () {
            Route::get('/VerifiedBlocknotDelete', [PreferenceController::class, 'VerifiedBlocknotDelete'])->name('preference.VerifiedBlocknotDelete');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managepreference.VerifiednotBlocknotDelete'])->group(function () {
            Route::get('/VerifiednotBlocknotDelete', [PreferenceController::class, 'VerifiednotBlocknotDelete'])->name('preference.VerifiednotBlocknotDelete');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managepreference.VerifiednotBlockDelete'])->group(function () {
            Route::get('/VerifiednotBlockDelete', [PreferenceController::class, 'VerifiednotBlockDelete'])->name('preference.VerifiednotBlockDelete');
        });
    });


    //Gestion des types de propriété.
    Route::prefix('propertyType')->group(function () {
        Route::middleware(['role_or_permission:admin|superAdmin|ManagepropertyType.store'])->group(function () {
            Route::post('/store', [PropertyTypeController::class, 'store'])->name('propertyType.store');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|ManagepropertyType.show'])->group(function () {
            Route::get('/show/{id}', [PropertyTypeController::class, 'show'])->name('propertyType.show');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|ManagepropertyType.updateName'])->group(function () {
            Route::put('/updateName/{id}', [PropertyTypeController::class, 'updateName'])->name('propertyType.updateName');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|ManagepropertyType.updateIcone'])->group(function () {
            Route::post('/updateIcone/{id}', [PropertyTypeController::class, 'updateIcone'])->name('propertyType.updateIcone');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|ManagepropertyType.destroy'])->group(function () {
            Route::delete('/destroy/{id}', [PropertyTypeController::class, 'destroy'])->name('propertyType.destroy');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|ManagepropertyType.block'])->group(function () {
            Route::put('/block/{id}', [PropertyTypeController::class, 'block'])->name('propertyType.block');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|ManagepropertyType.indexBlock'])->group(function () {
            Route::get('/indexBlock', [PropertyTypeController::class, 'indexBlock'])->name('propertyType.indexBlock');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|ManagepropertyType.unblock'])->group(function () {
            Route::put('/unblock/{id}', [PropertyTypeController::class, 'unblock'])->name('propertyType.unblock');
        });
    });



    //Gestion de la liste des documents
    Route::prefix('document')->group(function () {
        Route::middleware(['role_or_permission:admin|superAdmin|Managedocument.index'])->group(function () {
            Route::get('/index', [DocumentController::class, 'index'])->name('document.index');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managedocument.store'])->group(function () {
            Route::post('/store', [DocumentController::class, 'store'])->name('document.store');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managedocument.show'])->group(function () {
            Route::get('/show/{id}', [DocumentController::class, 'show'])->name('document.show');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managedocument.update'])->group(function () {
            Route::put('/update/{id}', [DocumentController::class, 'update'])->name('document.update');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managedocument.destroy'])->group(function () {
            Route::delete('/destroy/{id}', [DocumentController::class, 'destroy'])->name('document.destroy');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managedocument.block'])->group(function () {
            Route::put('/block/{id}', [DocumentController::class, 'block'])->name('document.block');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managedocument.unblock'])->group(function () {
            Route::put('/unblock/{id}', [DocumentController::class, 'unblock'])->name('document.unblock');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managedocument.active'])->group(function () {
            Route::put('/active/{id}', [DocumentController::class, 'active'])->name('document.active');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managedocument.inactive'])->group(function () {
            Route::put('/inactive/{id}', [DocumentController::class, 'inactive'])->name('document.inactive');
        });

        Route::middleware(['role_or_permission:admin|superAdmin|Managedocument.document_inactif'])->group(function () {
            Route::get('/document_inactif', [DocumentController::class, 'document_inactif'])->name('document.document_inactif');
        });
    });

    Route::get('/document/document_actif', [DocumentController::class, 'document_actif']);

        //Gestion de la Verification des documents hote
        Route::prefix('verificationdocument')->group(function () {
            Route::middleware(['role_or_permission:admin|superAdmin|Manageverificationdocumenthote.index'])->group(function () {
                Route::get('/index', [VerificationDocumentController::class, 'index'])->name('verificationdocumenthote.index');
            });

            Route::middleware(['role_or_permission:admin|superAdmin|Manageverificationdocumenthote.show'])->group(function () {
                Route::get('/show/{id}', [VerificationDocumentController::class, 'show'])->name('verificationdocumenthote.show');
            });

            Route::middleware(['role_or_permission:admin|superAdmin|Manageverificationdocumenthote.validateDocuments'])->group(function () {
                Route::post('/hote/valider/all', [VerificationDocumentController::class, 'validateDocuments'])->name('verificationdocumenthote.validateDocuments');
            });

            Route::middleware(['role_or_permission:admin|superAdmin|Manageverificationdocumenthote.validateDocument'])->group(function () {
                Route::post('/hote/valider/one', [VerificationDocumentController::class, 'validateDocument'])->name('verificationdocumenthote.validateDocument');
            });

        });
        //Gestion de la Verification des documents partenaires
        Route::prefix('verificationdocumentpartenaire')->group(function () {
            Route::middleware(['role_or_permission:admin|superAdmin|Manageverificationdocumentpartenaire.index'])->group(function () {
                Route::get('/index', [VerificationDocumentPartenaireController::class, 'index'])->name('verificationdocumentpartenaire.index');
            });

            Route::middleware(['role_or_permission:admin|superAdmin|Manageverificationdocumentpartenaire.show'])->group(function () {
                Route::get('/show/{id}', [VerificationDocumentPartenaireController::class, 'show'])->name('verificationdocumentpartenaire.show');
            });

            Route::middleware(['role_or_permission:admin|superAdmin|Manageverificationdocumentpartenaire.validateDocuments'])->group(function () {
                Route::post('/partenaire/valider/all', [VerificationDocumentPartenaireController::class, 'validateDocuments'])->name('verificationdocumentpartenaire.validateDocuments');
            });

            Route::middleware(['role_or_permission:admin|superAdmin|Manageverificationdocumentpartenaire.validateDocument'])->group(function () {
                Route::post('/partenaire/valider/one', [VerificationDocumentPartenaireController::class, 'validateDocument'])->name('verificationdocumentpartenaire.validateDocument');
            });

        });


    //Gestion des commissions de l'hôte
    Route::prefix('commission')->group(function () {
        Route::middleware(['role:admin|superAdmin'])->group(function () {
            Route::get('/usersWithCommission/{commission}', [CommissionController::class, 'usersWithCommission']);
        });

        Route::middleware(['role_or_permission:superAdmin|Managecommission.updateCommissionForSpecifiqueUser'])->group(function () {
            Route::post('/updateCommissionForSpecifiqueUser', [CommissionController::class, 'updateCommissionForSpecifiqueUser'])->name('commission.updateCommissionForSpecifiqueUser');
        });
        Route::middleware(['role_or_permission:superAdmin|Managecommission.updateCommissionValueByAnother'])->group(function () {
            Route::put('/updateCommissionValueByAnother', [CommissionController::class, 'updateCommissionValueByAnother'])->name('commission.updateCommissionValueByAnother');
        });
    });

    //Gestion des commissions de partenaire
    Route::prefix('commissionpartenaire')->group(function () {

        Route::middleware(['role_or_permission:superAdmin|Managecommissionpartenaire.updateCommissionForSpecifiqueUser'])->group(function () {
            Route::post('/updateCommissionForSpecifiqueUser', [UserPartenaireController::class, 'updateCommissionForSpecifiqueUser'])->name('commissionpartenaire.updateCommissionForSpecifiqueUser');
        });
        Route::middleware(['role_or_permission:superAdmin|Managecommissionpartenaire.updateCommissionValueByAnother'])->group(function () {
            Route::put('/updateCommissionValueByAnother', [UserPartenaireController::class, 'updateCommissionValueByAnother'])->name('commissionpartenaire.updateCommissionValueByAnother');
     });
    });

    //Gestion des reduction de partenaire
    Route::prefix('reductionpartenaire')->group(function () {

        Route::middleware(['role_or_permission:superAdmin|Managereductionpartenaire.updatereductionForSpecifiqueUser'])->group(function () {
            Route::post('/updatereductionForSpecifiqueUser', [UserPartenaireController::class, 'updatereductionForSpecifiqueUser'])->name('reductionpartenaire.updatereductionForSpecifiqueUser');
        });
        Route::middleware(['role_or_permission:superAdmin|Managereductionpartenaire.updatereductionValueByAnother'])->group(function () {
            Route::put('/updatereductionValueByAnother', [UserPartenaireController::class, 'updatereductionValueByAnother'])->name('reductionpartenaire.updatereductionValueByAnother');
            });
    });

    //Gestion des nombres de reservation reduction de partenaire
    Route::prefix('numberreservationpartenaire')->group(function () {

        Route::middleware(['role_or_permission:superAdmin|Managenumberreservationpartenaire.updatenumberreservationForSpecifiqueUser'])->group(function () {
            Route::post('/updatenumberreservationForSpecifiqueUser', [UserPartenaireController::class, 'updatenumberreservationForSpecifiqueUser'])->name('numberreservationpartenaire.updatenumberreservationForSpecifiqueUser');
        });
        Route::middleware(['role_or_permission:superAdmin|Managenumberreservationpartenaire.updatenumberreservationValueByAnother'])->group(function () {
            Route::put('/updatenumberreservationValueByAnother', [UserPartenaireController::class, 'updatenumberreservationValueByAnother'])->name('numberreservationpartenaire.updatenumberreservationValueByAnother');
          });
    });

  //Gestion des préférences des utilisateurs (Pas besoin de permission ,les roles font l'affaire)
    Route::group(['middleware' => ['role:traveler|superAdmin']], function () {
        Route::prefix('users/preference')->group(function () {
            Route::post('/add', [User_preferenceController::class, 'AddUserPreferences']);
            Route::post('/remove', [User_preferenceController::class, 'RemoveUserPreferences']);
            Route::get('/show', [User_preferenceController::class, 'showUserPreferences']);
        });

    });

    //Gestion des audits
    Route::group(['middleware' => ['role:superAdmin|admin']], function () {
        Route::prefix('audit')->group(function () {
         Route::get('/getAudits', [AuditController::class, 'getAudits']);
         Route::get('/getAuditsByModelType/{modelType}', [AuditController::class, 'getAuditsByModelType']);
         Route::get('/getAuditsByModelTypeAndId/{modelType}/{modelId}', [AuditController::class, 'getAuditsByModelTypeAndId']);
         });
      });


   // Gestion des Notifications (Pas besoin de permission ,ni de role,il suffit d'etre connecté)
     Route::prefix('notifications')->group(function () {
        Route::put('/{id}/markread', [NotificationController::class, 'markNotificationAsRead']);
        Route::get('/read', [NotificationController::class, 'getReadNotifications']);
        Route::get('/unread', [NotificationController::class, 'getUnreadNotifications']);
    });

    Route::group(['middleware' => ['role:superAdmin|admin']], function () {
      Route::prefix('notifications')->group(function () {
       Route::get('/index', [NotificationController::class, 'index']);
       Route::post('/store', [NotificationController::class, 'store']);
       Route::delete('/destroy/{id}', [NotificationController::class, 'destroy']);
       Route::post('/notifyUserHaveRoles/{mode}', [NotificationController::class, 'notifyUserHaveRoles']);
       Route::post('/notifyUsers/{mode}', [NotificationController::class, 'notifyUsers']);
       });
    });
    //Gestion des logements en favoris (ici il suffit d'etre connecté)
    Route::prefix('logement')->group(function () {

            Route::post('/addfavorites', [FavorisController::class, 'addToFavorites']);
            Route::delete('/removefromfavorites/{housingId}', [FavorisController::class, 'removeFromFavorites']);
            Route::get('/favorites', [FavorisController::class, 'getFavorites']);
    });

    // Gestion logement côté hôte

    Route::prefix('logement')->group(function () {

            //Gestion des logements (CRUD)
            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.store']], function () {
                Route::post('/store', [HousingController::class, 'addHousing'])->name('logement.store');
                Route::post('/store_step_1/{housingId}', [AddHousingController::class, 'addHousing_step_1'])->name('logement.store_step_1');
                Route::post('/store_step_2/{housingId}', [AddHousingController::class, 'addHousing_step_2'])->name('logement.store_step_2');
                Route::post('/store_step_3/{housingId}', [AddHousingZController::class, 'addHousing_step_3'])->name('logement.store_step_3');
                Route::post('/store_step_4/{housingId}', [AddHousingController::class, 'addHousing_step_4'])->name('logement.store_step_4');
                Route::post('/store_step_5/{housingId}', [AddHousingController::class, 'addHousing_step_5'])->name('logement.store_step_5');
                Route::post('/store_step_6/{housingId}', [AddHousingController::class, 'addHousing_step_6'])->name('logement.store_step_6');
                Route::post('/store_step_7/{housingId}', [AddHousingZController::class, 'addHousing_step_7'])->name('logement.store_step_7');
                Route::post('/store_step_8/{housingId}', [AddHousingZController::class, 'addHousing_step_8'])->name('logement.store_step_8');
                Route::post('/store_step_9/{housingId}', [AddHousingController::class, 'addHousing_step_9'])->name('logement.store_step_9');
                Route::post('/store_step_10/{housingId}', [AddHousingController::class, 'addHousing_step_10'])->name('logement.store_step_10');
                Route::post('/store_step_11/{housingId}', [AddHousingController::class, 'addHousing_step_11'])->name('logement.store_step_11');
                Route::post('/store_step_12/{housingId}', [AddHousingController::class, 'addHousing_step_12'])->name('logement.store_step_12');
                Route::post('/store_step_13/{housingId}', [AddHousingZController::class, 'addHousing_step_13'])->name('logement.store_step_13');
                Route::post('/store_step_14/{housingId}', [AddHousingController::class, 'addHousing_step_14'])->name('logement.store_step_14');
                Route::post('/store_step_15/{housingId}', [AddHousingZController::class, 'addHousing_step_15'])->name('logement.store_step_15');
                Route::post('/store_step_16/{housingId}', [AddHousingZController::class, 'addHousing_step_16'])->name('logement.store_step_16');
                Route::post('/store_step_17/{housingId}', [AddHousingZController::class, 'addHousing_step_17'])->name('logement.store_step_17');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.updateSensible']], function () {
                Route::put('/update/sensible/{housingid}', [HousingController::class, 'updateSensibleHousing'])->name('logement.updateSensible');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.updateInsensible']], function () {
                Route::put('/update/insensible/{housingid}', [HousingController::class, 'updateInsensibleHousing'])->name('logement.updateInsensible');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.disable']], function () {
                Route::put('/{housingId}/hote/disable', [HousingController::class, 'disableHousing'])->name('logement.disable');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.enable']], function () {
                Route::put('/{housingId}/hote/enable', [HousingController::class, 'enableHousing'])->name('logement.enable');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.destroyHote']], function () {
                Route::delete('/destroyHousingHote/{id}', [HousingController::class, 'destroyHousingHote'])->name('logement.destroyHote');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.getHousingForHote']], function () {
                Route::get('/getHousingForHote', [HousingController::class, 'getHousingForHote'])->name('logement.getHousingForHote');
            });

            // Gestion des photos logement
            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.updatePhoto']], function () {
                Route::post('/updatephoto/{photo_id}', [PhotoController::class, 'updatePhotoHousing'])->name('logement.updatePhoto');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.setCoverPhoto']], function () {
                Route::post('/{housingId}/setcoverphoto/{photoId}', [PhotoController::class, 'setCoverPhoto'])->name('logement.setCoverPhoto');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.deletePhoto']], function () {
                Route::delete('/photo/{photoId}', [PhotoController::class, 'deletePhotoHousing'])->name('logement.deletePhoto');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.addPhoto']], function () {
                Route::post('/add/file/{housingId}', [HousingController::class, 'addPhotoToHousing'])->name('logement.addPhoto');
            });
            // Gestion des équipements du logement

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.storeUnexistEquipment']], function () {
                Route::post('equipment/storeUnexist/{housingId}', [HousingEquipmentController::class, 'storeUnexist'])->name('logement.storeUnexistEquipment');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.equipementsHousing']], function () {
                Route::get('/{housingId}/equipements', [HousingEquipmentController::class, 'equipementsHousing'])->name('logement.equipementsHousing');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.deleteEquipement']], function () {
                Route::delete('/equipement', [HousingEquipmentController::class, 'DeleteEquipementHousing'])->name('logement.deleteEquipement');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.addEquipment']], function () {
                Route::post('/equipment/addEquipmentToHousing', [HousingEquipmentController::class, 'addEquipmentToHousing'])->name('logement.addEquipment');
            });
            // Gestion des préférences du logement

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.housingPreference']], function () {
                Route::get('/{housingPreferenceId}/preferences', [HousingPreferenceController::class, 'housingPreference'])->name('logement.housingPreference');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.deletePreference']], function () {
                Route::delete('/preference', [HousingPreferenceController::class, 'deletePreferenceHousing'])->name('logement.deletePreference');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.addPreference']], function () {
                Route::post('/preference/addPreferenceToHousing', [HousingPreferenceController::class, 'addPreferenceToHousing'])->name('logement.addPreference');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.storeUnexistPreference']], function () {
            Route::post('/preference/storeUnexist/{housingId}', [HousingPreferenceController::class, 'storeUnexist'])->name('logement.storeUnexistPreference');
            });

            // Gestion des catégories pour un hôte qui ajoute déjà un logement

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.deletePhotoCategory']], function () {
            Route::delete('/category/photo/{photoid}', [HousingCategoryFileController::class, 'deletePhotoHousingCategory'])->name('logement.deletePhotoCategory');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.addDefaultCategory']], function () {
            Route::post('/category/default/add', [HousingCategoryFileController::class, 'addHousingCategory'])->name('logement.addDefaultCategory');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.addNewCategory']], function () {
            Route::post('/category/default/addNew', [HousingCategoryFileController::class, 'addHousingCategoryNew'])->name('logement.addNewCategory');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.deleteCategory']], function () {
            Route::delete('/{housingId}/category/{categoryId}/delete', [HousingCategoryFileController::class, 'deleteHousingCategory'])->name('logement.deleteCategory');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.addPhotoCategory']], function () {
            Route::post('/{housingId}/category/{categoryId}/photos/add', [HousingCategoryFileController::class, 'addPhotosCategoryToHousing'])->name('logement.addPhotoCategory');
            });

            // Gestion des charges

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.addCharge']], function () {
            Route::post('/charge/addChargeToHousing', [HousingChargeController::class, 'addChargeToHousing'])->name('logement.addCharge');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.listCharge']], function () {
            Route::get('/charge/listelogementcharge/{housingId}', [HousingChargeController::class, 'listelogementcharge'])->name('logement.listCharge');
            });

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.deleteCharge']], function () {
            Route::delete('/charge', [HousingChargeController::class, 'DeleteChargeHousing'])->name('logement.deleteCharge');
            });

            // Liste des logements non remplis complètement par l'hôte

            Route::group(['middleware' => ['role_or_permission:superAdmin|hote|Managelogement.HousingHoteInProgress']], function () {
            Route::get('/liste/notFinished', [HousingController::class, 'HousingHoteInProgress'])->name('logement.HousingHoteInProgress');
            });


    });


   // Gestion logement côté admin

    Route::prefix('logement')->group(function () {

        //Gestion des logements coté administrateur
       Route::get('/index/ListeDesLogementsValideBloque', [AdminHousingController::class, 'ListeDesLogementsValideBloque'])->name('logement.ListeDesLogementsValideBloque')->middleware('role_or_permission:superAdmin|admin|Managelogement.ListeDesLogementsValideBloque');
       Route::get('/index/ListeDesLogementsValideDelete', [AdminHousingController::class, 'ListeDesLogementsValideDelete'])->name('logement.ListeDesLogementsValideDelete')->middleware('role_or_permission:superAdmin|admin|Managelogement.ListeDesLogementsValideDelete');
       Route::get('/index/ListeDesLogementsValideDisable', [AdminHousingController::class, 'ListeDesLogementsValideDisable'])->name('logement.ListeDesLogementsValideDisable')->middleware('role_or_permission:superAdmin|admin|Managelogement.ListeDesLogementsValideDisable');
       Route::get('/hote_with_many_housing', [AdminHousingController::class, 'hote_with_many_housing'])->name('logement.hote_with_many_housing')->middleware('role_or_permission:superAdmin|admin|Managelogement.hote_with_many_housing');
       Route::get('/country_with_many_housing', [AdminHousingController::class, 'country_with_many_housing'])->name('logement.country_with_many_housing')->middleware('role_or_permission:superAdmin|admin|Managelogement.country_with_many_housing');
       Route::get('/getHousingDestroyedByHote', [AdminHousingController::class, 'getHousingDestroyedByHote'])->name('logement.getHousingDestroyedByHote')->middleware('role_or_permission:superAdmin|admin|Managelogement.getHousingDestroyedByHote');
       Route::get('/getTop10HousingByAverageNotes', [AdminHousingController::class, 'getTop10HousingByAverageNotes'])->name('logement.getTop10HousingByAverageNotes')->middleware('role_or_permission:superAdmin|admin|Managelogement.getTop10HousingByAverageNotes');
        //Gestion des categories côté admin
       Route::get('/category/default/invalid', [HousingCategoryFileController::class, 'getCategoryDefaultInvalidHousings'])->name('logement.getCategoryDefaultInvalidHousings')->middleware('role_or_permission:superAdmin|admin|Managelogement.getCategoryDefaultInvalidHousings');
       Route::put('/category/default/{housing_id}/{category_id}/validate', [HousingCategoryFileController::class, 'validateDefaultCategoryHousing'])->name('logement.validateDefaultCategoryHousing')->middleware('role_or_permission:superAdmin|admin|Managelogement.validateDefaultCategoryHousing');
       Route::get('/category/unexist/invalid', [HousingCategoryFileController::class, 'getCategoryUnexistInvalidHousings'])->name('logement.getCategoryUnexistInvalidHousings')->middleware('role_or_permission:superAdmin|admin|Managelogement.getCategoryUnexistInvalidHousings');
       Route::put('/category/unexist/{housing_id}/{category_id}/validate', [HousingCategoryFileController::class, 'validateUnexistCategoryHousing'])->name('logement.validateUnexistCategoryHousing')->middleware('role_or_permission:superAdmin|admin|Managelogement.validateUnexistCategoryHousing');
       Route::get('/category/{housing_id}/{category_id}/detail', [HousingCategoryFileController::class, 'getCategoryDetail'])->name('logement.getCategoryDetail')->middleware('role_or_permission:superAdmin|admin|Managelogement.getCategoryDetail');
       Route::get('/category/photo/unverified', [HousingCategoryFileController::class, 'getUnverifiedHousingCategoryFilesWithDetails'])->name('logement.getUnverifiedHousingCategoryFilesWithDetails')->middleware('role_or_permission:superAdmin|admin|Managelogement.getUnverifiedHousingCategoryFilesWithDetails');
       Route::put('/category/photo/{id}/validate', [HousingCategoryFileController::class, 'validateHousingCategoryFile'])->name('logement.validateHousingCategoryFile')->middleware('role_or_permission:superAdmin|admin|Managelogement.validateHousingCategoryFile');
       //Gestion des équipements côté admin
       Route::get('/equipment/ListHousingEquipmentInvalid/{housingId}', [HousingEquipmentController::class, 'ListHousingEquipmentInvalid'])->name('logement.ListHousingEquipmentInvalid')->middleware('role_or_permission:superAdmin|admin|Managelogement.ListHousingEquipmentInvalid');
       Route::post('/equipment/makeVerifiedHousingEquipment/{housingEquipmentId}', [HousingEquipmentController::class, 'makeVerifiedHousingEquipment'])->name('logement.makeVerifiedHousingEquipment')->middleware('role_or_permission:superAdmin|admin|Managelogement.makeVerifiedHousingEquipment');
       Route::get('/equipment/ListEquipmentForHousingInvalid/{housingId}', [HousingEquipmentController::class, 'ListEquipmentForHousingInvalid'])->name('logement.ListEquipmentForHousingInvalid')->middleware('role_or_permission:superAdmin|admin|Managelogement.ListEquipmentForHousingInvalid');
       Route::get('/equipment/getHousingEquipmentInvalid', [HousingEquipmentController::class, 'getHousingEquipmentInvalid'])->name('logement.getHousingEquipmentInvalid')->middleware('role_or_permission:superAdmin|admin|Managelogement.getHousingEquipmentInvalid');
       Route::get('/equipment/getUnexistEquipmentInvalidForHousing', [HousingEquipmentController::class, 'getUnexistEquipmentInvalidForHousing'])->name('logement.getUnexistEquipmentInvalidForHousing')->middleware('role_or_permission:superAdmin|admin|Managelogement.getUnexistEquipmentInvalidForHousing');
       //Gestion des preference côté admin
       Route::get('/preference/getHousingPreferenceInvalid', [HousingPreferenceController::class, 'getHousingPreferenceInvalid'])->name('logement.getHousingPreferenceInvalid')->middleware('role_or_permission:superAdmin|admin|Managelogement.getHousingPreferenceInvalid');
       Route::get('/preference/getUnexistPreferenceInvalidForHousing', [HousingPreferenceController::class, 'getUnexistPreferenceInvalidForHousing'])->name('logement.getUnexistPreferenceInvalidForHousing')->middleware('role_or_permission:superAdmin|admin|Managelogement.getUnexistPreferenceInvalidForHousing');
       Route::get('/preference/ListHousingPreferenceInvalid/{housingId}', [HousingPreferenceController::class, 'ListHousingPreferenceInvalid'])->name('logement.ListHousingPreferenceInvalid')->middleware('role_or_permission:superAdmin|admin|Managelogement.ListHousingPreferenceInvalid');
       Route::get('/preference/ListPreferenceForHousingInvalid/{housingId}', [HousingPreferenceController::class, 'ListPreferenceForHousingInvalid'])->name('logement.ListPreferenceForHousingInvalid')->middleware('role_or_permission:superAdmin|admin|Managelogement.ListPreferenceForHousingInvalid');
       Route::post('/preference/makeVerifiedHousingPreference/{housingPreferenceId}', [HousingPreferenceController::class, 'makeVerifiedHousingPreference'])->name('logement.makeVerifiedHousingPreference')->middleware('role_or_permission:superAdmin|admin|Managelogement.makeVerifiedHousingPreference');
       Route::post('/block/{housingId}', [HousingController::class, 'block'])->name('logement.block')->middleware('role_or_permission:superAdmin|admin|Managelogement.block');
       Route::post('/unblock/{housingId}', [HousingController::class, 'unblock'])->name('logement.unblock')->middleware('role_or_permission:superAdmin|admin|Managelogement.unblock');
       //Gestion des photos de logement
       Route::get('/photos/unverified', [HousingController::class, 'getUnverifiedPhotos'])->name('logement.getUnverifiedPhotos')->middleware('role_or_permission:superAdmin|admin|Managelogement.getUnverifiedPhotos');
       Route::put('/photos/validate/{photoId}', [HousingController::class, 'validatePhoto'])->name('logement.validatePhoto')->middleware('role_or_permission:superAdmin|admin|Managelogement.validatePhoto');

   });





   Route::prefix('logement')->group(function () {
    // Gestion des logements en attente de validation ou de mise à jour pour être visible sur le site côté administrateur
    Route::get('/withoutvalidate', [AdminHousingController::class, 'indexHousingForValidationForadmin'])
        ->name('logement.indexHousingForValidationForadmin')
        ->middleware('role_or_permission:superAdmin|Managelogement.indexHousingForValidationForadmin');

    Route::get('/HousingHoteInProgressForAdmin', [AdminHousingController::class, 'HousingHoteInProgressForAdmin'])
        ->name('logement.HousingHoteInProgressForAdmin')
        ->middleware('role_or_permission:superAdmin|Managelogement.HousingHoteInProgressForAdmin');

    Route::get('/withoutupdate', [AdminHousingController::class, 'indexHousingForUpdateForadmin'])
        ->name('logement.indexHousingForUpdateForadmin')
        ->middleware('role_or_permission:superAdmin|Managelogement.indexHousingForUpdateForadmin');

    Route::get('/withoutvalidation/show/{id}', [AdminHousingController::class, 'showHousingDetailForValidationForadmin'])
        ->name('logement.showHousingDetailForValidationForadmin')
        ->middleware('role_or_permission:superAdmin|Managelogement.showHousingDetailForValidationForadmin');

    Route::put('/validate/one/{id}', [AdminHousingController::class, 'ValidateOneHousing'])
        ->name('logement.ValidateOneHousing')
        ->middleware('role_or_permission:superAdmin|Managelogement.ValidateOneHousing');

    Route::put('/validate/many/', [AdminHousingController::class, 'ValidateManyHousing'])
        ->name('logement.ValidateManyHousing')
        ->middleware('role_or_permission:superAdmin|Managelogement.ValidateManyHousing');

    Route::put('/update/one/{id}', [AdminHousingController::class, 'UpdateOneHousing'])
        ->name('logement.UpdateOneHousing')
        ->middleware('role_or_permission:superAdmin|Managelogement.UpdateOneHousing');
});


    //Gestion des reservation

    Route::prefix('reservation')->group(function () {
        // Reviews
        Route::post('/reviews/note/add', [ReviewReservationController::class, 'AddReviewNote'])
            ->name('reservation.reviews.note.add')
            ->middleware('role_or_permission:superAdmin|hote|Managereservation.reviews.note.add');

        Route::get('/{housingId}/reviews/note/get', [ReviewReservationController::class, 'LogementAvecMoyenneNotesCritereEtCommentairesAcceuil'])
            ->name('reservation.reviews.note.get');


        Route::get('/statistiques_notes/{housing_id}', [ReviewReservationController::class, 'getStatistiquesDesNotes'])
            ->name('reservation.reviews.statistiques_notes.get')
            ->middleware('role_or_permission:superAdmin|Managereservation.statistiques_notes.get');

        // Reservation operations
        Route::post('/store', [ReservationController::class, 'storeReservationWithPayment'])
            ->name('reservation.store')
            ->middleware('role_or_permission:superAdmin|traveler|Managereservation.store');

        // Hote (Host)
        Route::put('/hote_confirm_reservation/{idReservation}', [ReservationController::class, 'hote_confirm_reservation'])
            ->name('reservation.hote_confirm_reservation')
            ->middleware('role_or_permission:superAdmin|hote|Managereservation.hote_confirm_reservation');

        Route::put('/hote_reject_reservation/{idReservation}', [ReservationController::class, 'hote_reject_reservation'])
            ->name('reservation.hote_reject_reservation')
            ->middleware('role_or_permission:superAdmin|Managereservation.hote_reject_reservation');

        Route::get('/showDetailOfReservationForHote/{idReservation}', [ReservationController::class, 'showDetailOfReservationForHote'])
            ->name('reservation.showDetailOfReservationForHote')
            ->middleware('role_or_permission:superAdmin|Managereservation.showDetailOfReservationForHote');

        Route::get('/getReservationsByHousingId/{housingId}', [ReservationController::class, 'getReservationsByHousingId'])
            ->name('reservation.getReservationsByHousingId')
            ->middleware('role_or_permission:superAdmin|Managereservation.getReservationsByHousingId');

        Route::get('/reservationsConfirmedByHost', [HoteReservationController::class, 'reservationsConfirmedByHost'])
            ->name('reservation.reservationsConfirmedByHost')
            ->middleware('role_or_permission:superAdmin|Managereservation.reservationsConfirmedByHost');

        Route::get('/reservationsRejectedByHost', [HoteReservationController::class, 'reservationsRejectedByHost'])
            ->name('reservation.reservationsRejectedByHost')
            ->middleware('role_or_permission:superAdmin|Managereservation.reservationsRejectedByHost');

        Route::get('/reservationsCanceledByTravelerForHost', [HoteReservationController::class, 'reservationsCanceledByTravelerForHost'])
            ->name('reservation.reservationsCanceledByTravelerForHost')
            ->middleware('role_or_permission:superAdmin|Managereservation.reservationsCanceledByTravelerForHost');

        Route::get('/reservationsNotConfirmedYetByHost', [HoteReservationController::class, 'reservationsNotConfirmedYetByHost'])
            ->name('reservation.reservationsNotConfirmedYetByHost')
            ->middleware('role_or_permission:superAdmin|Managereservation.reservationsNotConfirmedYetByHost');

        // Traveler
        Route::put('/traveler_reject_reservation/{idReservation}', [ReservationController::class, 'traveler_reject_reservation'])
            ->name('reservation.traveler_reject_reservation')
            ->middleware('role_or_permission:superAdmin|Managereservation.traveler_reject_reservation');

        Route::post('/confirmIntegration', [ReservationController::class, 'confirmIntegration'])
            ->name('reservation.confirmIntegration')
            ->middleware('role_or_permission:superAdmin|Managereservation.confirmIntegration');

        // Admin
        Route::get('/housing_with_many_reservation', [AdminReservationController::class, 'housing_with_many_reservation'])
            ->name('reservation.housing_with_many_reservation')
            ->middleware('role_or_permission:superAdmin|Managereservation.housing_with_many_reservation');

        Route::get('/country_with_many_reservation', [AdminReservationController::class, 'country_with_many_reservation'])
            ->name('reservation.country_with_many_reservation')
            ->middleware('role_or_permission:superAdmin|Managereservation.country_with_many_reservation');

        Route::get('/housing_without_reservation', [AdminReservationController::class, 'housing_without_reservation'])
            ->name('reservation.housing_without_reservation')
            ->middleware('role_or_permission:superAdmin|Managereservation.housing_without_reservation');

        Route::get('/getReservationsCountByYear', [AdminReservationController::class, 'getReservationsCountByYear'])
            ->name('reservation.getReservationsCountByYear')
            ->middleware('role_or_permission:superAdmin|Managereservation.getReservationsCountByYear');

        Route::get('/getAllReservation', [AdminReservationController::class, 'getAllReservation'])
            ->name('reservation.getAllReservation')
            ->middleware('role_or_permission:superAdmin|Managereservation.getAllReservation');

        Route::get('/getUserReservations/{user}', [AdminReservationController::class, 'getUserReservationsForAdmin'])
            ->name('reservation.getUserReservations')
            ->middleware('role_or_permission:superAdmin|Managereservation.getUserReservations');

        Route::get('/showDetailOfReservation/{idReservation}', [AdminReservationController::class, 'showDetailOfReservationForAdmin'])
            ->name('reservation.showDetailOfReservation')
            ->middleware('role_or_permission:superAdmin|Managereservation.showDetailOfReservation');

        Route::get('/topTravelersWithMostReservations', [AdminReservationController::class, 'topTravelersWithMostReservations'])
            ->name('reservation.topTravelersWithMostReservations')
            ->middleware('role_or_permission:superAdmin|Managereservation.topTravelersWithMostReservations');

        Route::get('/getReservationsCountByYearAndMonth', [AdminReservationController::class, 'getReservationsCountByYearAndMonth'])
            ->name('reservation.getReservationsCountByYearAndMonth')
            ->middleware('role_or_permission:superAdmin|Managereservation.getReservationsCountByYearAndMonth');

        Route::get('/getAllReservationCanceledByTravelerForAdmin', [AdminReservationController::class, 'getAllReservationCanceledByTravelerForAdmin'])
            ->name('reservation.getAllReservationCanceledByTravelerForAdmin')
            ->middleware('role_or_permission:superAdmin|Managereservation.getAllReservationCanceledByTravelerForAdmin');

        Route::get('/getAllReservationRejectedForAdmin', [AdminReservationController::class, 'getAllReservationRejectedForAdmin'])
            ->name('reservation.getAllReservationRejectedForAdmin')
            ->middleware('role_or_permission:superAdmin|Managereservation.getAllReservationRejectedForAdmin');

        Route::get('/getAllReservationConfirmedForAdmin', [AdminReservationController::class, 'getAllReservationConfirmedForAdmin'])
            ->name('reservation.getAllReservationConfirmedForAdmin')
            ->middleware('role_or_permission:superAdmin|Managereservation.getAllReservationConfirmedForAdmin');
});


Route::prefix('portefeuille')->group(function () {
    Route::post('/credit', [PortfeuilleController::class, 'creditPortfeuille'])
        ->name('portefeuille.credit')
        ->middleware('role_or_permission:superAdmin|admin|hote|traveler|Manageportefeuille.credit');

    Route::get('/user/transaction', [PortfeuilleTransactionController::class, 'getPortfeuilleDetails'])
        ->name('portefeuille.user.transaction');



    Route::get('/transaction/all', [PortfeuilleTransactionController::class, 'getAllTransactions'])
        ->name('portefeuille.transaction.all')
        ->middleware('role_or_permission:superAdmin|Manageportefeuille.transaction.all');
        
    Route::post('/transaction/update', [PortfeuilleTransactionController::class, 'updateTransaction'])
         ->name('portefeuille.transaction.update')
        ->middleware('role_or_permission:superAdmin');

        Route::get('/transaction/{id}/history', [PortfeuilleTransactionController::class, 'getTransactionHistory'])
        ->name('portefeuille.transaction.history')
        ->middleware('role_or_permission:superAdmin|Manageportefeuille.transaction.history');
});

        //Crud de methode de paiement
        Route::prefix('methodPayement')->group(function () {
            Route::post('/store', [MethodPayementController::class, 'store'])
                ->name('methodPayement.store')
                ->middleware('role_or_permission:ManagemethodPayement.store|superAdmin|admin');

            Route::get('/index', [MethodPayementController::class, 'index'])
                ->name('methodPayement.index')
                ->middleware('role_or_permission:ManagemethodPayement.index|superAdmin|admin');

            Route::get('/show/{id}', [MethodPayementController::class, 'show'])
                ->name('methodPayement.show')
                ->middleware('role_or_permission:ManagemethodPayement.show|superAdmin|admin');

            Route::put('/updateName/{id}', [MethodPayementController::class, 'updateName'])
                ->name('methodPayement.updateName')
                ->middleware('role_or_permission:ManagemethodPayement.updateName|superAdmin|admin');

            Route::post('/updateIcone/{id}', [MethodPayementController::class, 'updateIcone'])
                ->name('methodPayement.updateIcone')
                ->middleware('role_or_permission:ManagemethodPayement.updateIcone|superAdmin|admin');

            Route::delete('/destroy/{id}', [MethodPayementController::class, 'destroy'])
                ->name('methodPayement.destroy')
                ->middleware('role_or_permission:ManagemethodPayement.destroy|superAdmin|admin');

            Route::put('/block/{id}', [MethodPayementController::class, 'block'])
                ->name('methodPayement.block')
                ->middleware('role_or_permission:ManagemethodPayement.block|superAdmin|admin');

            Route::put('/unblock/{id}', [MethodPayementController::class, 'unblock'])
                ->name('methodPayement.unblock')
                ->middleware('role_or_permission:ManagemethodPayement.unblock|superAdmin|admin');
        });
  //Gestion des retraits

  Route::prefix('retrait')->group(function () {
    // Admin
    Route::get('/ListRetraitWaitingConfirmationByAdmin', [RetraitController::class, 'ListRetraitWaitingConfirmationByAdmin'])
        ->name('retrait.ListRetraitWaitingConfirmationByAdmin')
        ->middleware('role_or_permission:admin|superAdmin|Manageretrait.ListRetraitWaitingConfirmationByAdmin');

    Route::get('/ListRetraitOfTravelerWaitingConfirmationByAdmin', [RetraitController::class, 'ListRetraitOfTravelerWaitingConfirmationByAdmin'])
        ->name('retrait.ListRetraitOfTravelerWaitingConfirmationByAdmin')
        ->middleware('role_or_permission:admin|superAdmin|Manageretrait.ListRetraitOfTravelerWaitingConfirmationByAdmin');

    Route::get('/ListRetraitOfHoteWaitingConfirmationByAdmin', [RetraitController::class, 'ListRetraitOfHoteWaitingConfirmationByAdmin'])
        ->name('retrait.ListRetraitOfHoteWaitingConfirmationByAdmin')
        ->middleware('role_or_permission:admin|superAdmin|Manageretrait.ListRetraitOfHoteWaitingConfirmationByAdmin');

    Route::get('/ListRetraitConfirmedByAdmin', [RetraitController::class, 'ListRetraitConfirmedByAdmin'])
        ->name('retrait.ListRetraitConfirmedByAdmin')
        ->middleware('role_or_permission:admin|superAdmin|Manageretrait.ListRetraitConfirmedByAdmin');

    Route::put('/validateRetraitByAdmin/{retraitId}', [RetraitController::class, 'validateRetraitByAdmin'])
        ->name('retrait.validateRetraitByAdmin')
        ->middleware('role_or_permission:admin|superAdmin|Manageretrait.validateRetraitByAdmin');

    Route::get('/ListRetraitRejectForAdmin', [RetraitController::class, 'ListRetraitRejectForAdmin'])
        ->name('retrait.ListRetraitRejectForAdmin')
        ->middleware('role_or_permission:admin|superAdmin|Manageretrait.ListRetraitRejectForAdmin');

    Route::put('/rejectRetraitByAdmin/{retraitId}', [RetraitController::class, 'rejectRetraitByAdmin'])
        ->name('retrait.rejectRetraitByAdmin')
        ->middleware('role_or_permission:admin|superAdmin|Manageretrait.rejectRetraitByAdmin');

    // Another user
    Route::post('/store', [RetraitController::class, 'store'])
        ->name('retrait.store');
    Route::get('/ListRetraitOfUserAuth', [RetraitController::class, 'ListRetraitOfUserAuth'])
        ->name('retrait.ListRetraitOfUserAuth');

    Route::get('/ListRetraitRejectOfUserAuth', [RetraitController::class, 'ListRetraitRejectOfUserAuth'])
        ->name('retrait.ListRetraitRejectOfUserAuth');

    });


//Gestion methode de paiement
        Route::prefix('moyenPayement')->group(function () {
            Route::get('/ListeMoyenPayement', [MoyenPayementController::class, 'ListeMoyenPayement'])
                ->name('moyenPayement.ListeMoyenPayement')
                ->middleware('role_or_permission:superAdmin|admin|ManagemoyenPayement.ListeMoyenPayement');

            Route::get('/ListeMoyenPayementUserAuth', [MoyenPayementController::class, 'ListeMoyenPayementUserAuth'])
                ->name('moyenPayement.ListeMoyenPayementUserAuth');

            Route::get('/ListeMoyenPayementBlocked', [MoyenPayementController::class, 'ListeMoyenPayementBlocked'])
                ->name('moyenPayement.ListeMoyenPayementBlocked')
                ->middleware('role_or_permission:superAdmin|admin|ManagemoyenPayement.ListeMoyenPayementBlocked');

            Route::get('/ListeMoyenPayementDeleted', [MoyenPayementController::class, 'ListeMoyenPayementDeleted'])
                ->name('moyenPayement.ListeMoyenPayementDeleted')
                ->middleware('role_or_permission:superAdmin|admin|ManagemoyenPayement.ListeMoyenPayementDeleted');

            Route::post('/store', [MoyenPayementController::class, 'store'])
                ->name('moyenPayement.store');

            Route::get('/show/{idMoyenPayement}', [MoyenPayementController::class, 'show'])
                ->name('moyenPayement.show');

            Route::put('/update/{idMoyenPayement}', [MoyenPayementController::class, 'update'])
                ->name('moyenPayement.update');

            Route::delete('/destroy/{idMoyenPayement}', [MoyenPayementController::class, 'destroy'])
                ->name('moyenPayement.destroy');

            Route::put('/block/{idMoyenPayement}', [MoyenPayementController::class, 'block'])
                ->name('moyenPayement.block')
                ->middleware('role_or_permission:superAdmin|admin|ManagemoyenPayement.block');

            Route::put('/unblock/{idMoyenPayement}', [MoyenPayementController::class, 'unblock'])
                ->name('moyenPayement.unblock')
                ->middleware('role_or_permission:superAdmin|admin|ManagemoyenPayement.unblock');
        });


        Route::prefix('paiement')->group(function () {
                Route::get('/reservation/user', [PayementController::class, 'listPaymentsForUser'])
                    ->name('paiement.reservation.user')
                    ->middleware('role_or_permission:traveler|Managepaiement.reservation.user');

                Route::get('/reservation/all', [PayementController::class, 'listAllPayments'])
                    ->name('paiement.reservation.all')
                    ->middleware('role_or_permission:superAdmin|admin|Managepaiement.reservation.all');
            });


            //Gestion des charges

        Route::prefix('charge')->group(function() {
                    Route::get('index', [ChargeController::class, 'index'])
                        ->name('charge.index') ->middleware('role_or_permission:admin|superAdmin|Managecharge.index');
                    Route::post('store', [ChargeController::class, 'store'])
                        ->name('charge.store')
                        ->middleware('role_or_permission:admin|superAdmin|Managecharge.store');
                    Route::put('updateName/{id}', [ChargeController::class, 'updateName'])
                        ->name('charge.updateName')
                        ->middleware('role_or_permission:admin|superAdmin|Managecharge.updateName');
                    Route::post('updateIcone/{id}', [ChargeController::class, 'updateIcone'])
                        ->name('charge.updateIcone')
                        ->middleware('role_or_permission:admin|superAdmin|Managecharge.updateIcone');
                    Route::delete('destroy/{id}', [ChargeController::class, 'destroy'])
                        ->name('charge.destroy')
                        ->middleware('role_or_permission:admin|superAdmin|Managecharge.destroy');
                });


                  //  Gestion ajout promotion

        Route::prefix('promotion')->group(function () {
                   Route::post('/add', [PromotionController::class, 'addPromotion'])
                       ->name('promotion.add')
                       ->middleware('role_or_permission:superAdmin|hote|Managepromotion.add');
                   Route::get('/user', [PromotionController::class, 'getUserPromotions'])
                       ->name('promotion.user')
                       ->middleware('role_or_permission:superAdmin|hote|Managepromotion.user');
                   Route::get('/housing/{housingId}', [PromotionController::class, 'getHousingPromotions'])
                       ->name('promotion.housing')
                       ->middleware('role_or_permission:superAdmin|hote|Managepromotion.housing');
                   Route::get('/all', [PromotionController::class, 'getAllPromotions'])
                       ->name('promotion.all')
                       ->middleware('role_or_permission:superAdmin|admin|Managepromotion.all');
                   Route::delete('/delete/{id}', [PromotionController::class, 'DeletePromotion'])
                       ->name('promotion.delete')
                       ->middleware('role_or_permission:superAdmin|hote|Managepromotion.delete');
                    Route::post('/active/{promotionId}/{housingId}', [PromotionController::class, 'activePromotion'])
                       ->name('promotion.activePromotion')
                       ->middleware('role_or_permission:superAdmin|hote|Managepromotion.activePromotion');

                       Route::post('/activatePromotionsForHousing/{housingId}', [PromotionController::class, 'activatePromotionsForHousing'])
                       ->name('promotion.activatePromotionsForHousing')
                       ->middleware('role_or_permission:superAdmin|hote|Managepromotion.activatePromotionsForHousing');

                       Route::post('/desactive/{promotionId}/{housingId}', [PromotionController::class, 'desactivePromotion'])
                       ->name('promotion.desactivePromotion')
                       ->middleware('role_or_permission:superAdmin|hote|Managepromotion.desactivePromotion');

                       Route::post('/desactivePromotionByJob/{housingId}', [PromotionController::class, 'desactivePromotionByJob'])
                       ->name('promotion.desactivePromotionByJob')
                       ->middleware('role_or_permission:superAdmin|hote|Managepromotion.desactivePromotionByJob');
               });

        Route::prefix('reduction')->group(function () {
                    Route::post('/add', [ReductionController::class, 'addReduction'])
                        ->name('reduction.add')
                        ->middleware('role_or_permission:superAdmin|hote|Managereduction.add');
                    Route::get('/user', [ReductionController::class, 'getUserReductions'])
                        ->name('reduction.user')
                        ->middleware('role_or_permission:superAdmin|hote|Managereduction.user');
                    Route::get('/housing/{housingId}', [ReductionController::class, 'getHousingReductions'])
                        ->name('reduction.housing')
                        ->middleware('role_or_permission:superAdmin|hote|Managereduction.housing');
                    Route::get('/all', [ReductionController::class, 'getAllReductions'])
                        ->name('reduction.all')
                        ->middleware('role_or_permission:superAdmin|admin|Managereduction.all');
                    Route::delete('/delete/{id}', [ReductionController::class, 'DeleteReduction'])
                        ->name('reduction.delete')
                        ->middleware('role_or_permission:superAdmin|hote|Managereduction.delete');
                        Route::post('/active/{reductionId}/{housingId}', [ReductionController::class, 'activeReduction'])
                        ->name('reduction.activeReduction')
                        ->middleware('role_or_permission:superAdmin|hote|Managereduction.activeReduction');
                        Route::post('/desactive/{reductionId}/{housingId}', [ReductionController::class, 'desactiveReduction'])
                        ->name('reduction.desactiveReduction')
                        ->middleware('role_or_permission:superAdmin|hote|Managereduction.desactiveReduction');
                        Route::post('/update/{reductionId}', [ReductionController::class, 'updateReduction'])
                        ->name('reduction.updateReduction')
                        ->middleware('role_or_permission:superAdmin|hote|Managereduction.updateReduction');
                });

                //Crud de type de demande
        Route::prefix('type_demande')->group(function () {
            Route::post('/store', [TypeDemandeController::class, 'store'])
                ->name('type_demande.store')
                ->middleware('role_or_permission:ManagemethodPayement.store|superAdmin|admin');

            Route::get('/index', [TypeDemandeController::class, 'index'])
                ->name('type_demande.index')
                ->middleware('role_or_permission:Managetype_demande.index|superAdmin|admin');

            Route::get('/show/{id}', [TypeDemandeController::class, 'show'])
                ->name('type_demande.show')
                ->middleware('role_or_permission:Managetype_demande.show|superAdmin|admin');

            Route::put('/updateName/{id}', [TypeDemandeController::class, 'updateName'])
                ->name('type_demande.updateName')
                ->middleware('role_or_permission:Managetype_demande.updateName|superAdmin|admin');

            Route::delete('/destroy/{id}', [TypeDemandeController::class, 'destroy'])
                ->name('type_demande.destroy')
                ->middleware('role_or_permission:ManagemethodPayement.destroy|superAdmin|admin');


        });


       Route::prefix('partenaire')->group(function () {
               Route::get('/users', [DashboardPartenaireController::class, 'getUsersForPartenaire'])
                   ->name('partenaire.getUsersForPartenaire')
                   ->middleware('role_or_permission:superAdmin|partenaire|Managepartenaire.getUsersForPartenaire');

                Route::get('users/transaction', [DashboardPartenaireController::class, 'getPartnerPortfeuilleDetails'])
                ->name('portefeuille.user.transactionpartenaire');
                
                Route::get('users/reservation', [DashboardPartenaireController::class, 'getReservationsWithPromoCode'])
                ->name('portefeuille.user.reservationpartenaire');

      });







});

/*end Route nécéssitant l'authentification/



/**Route ne nécéssitant pas l'authentification */

Route::get('/propertyType/index', [PropertyTypeController::class, 'index']);
Route::get('/housingtype/index', [HousingTypeController::class, 'index']);
// Route::get('/typestays/index', [TypeStayController::class, 'index']);
Route::get('/category/index', [CategorieController::class, 'index']);
Route::get('/criteria/index', [CriteriaController::class, 'index']);
Route::get('/review/index', [ReviewController::class, 'index']);
Route::get('/preference/index', [PreferenceController::class, 'index']);
Route::get('/language/index', [LanguageController::class, 'index']);

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

Route::middleware(['auth:sanctum', '2fa'])->group(function () {
    Route::prefix('site')->group(function () {
        Route::get('/visit_statistics', [UserVisiteSiteController::class, 'getSiteVisitStatistics'])
            ->name('site.getSiteVisitStatistics')
            ->middleware('role_or_permission:superAdmin|Managesite.getSiteVisitStatistics');

        Route::get('/date/visit_statistics', [UserVisiteSiteController::class, 'getSiteVisitStatisticsDate'])
            ->middleware('role_or_permission:superAdmin|Managesite.getSiteVisitStatisticsDate');

        Route::get('/current_month/visit_statistics', [UserVisiteSiteController::class, 'getCurrentMonthVisitStatistics'])
            ->middleware('role_or_permission:superAdmin|Managesite.getCurrentMonthVisitStatistics');

        Route::get('/current_year/visit_statistics', [UserVisiteSiteController::class, 'getCurrentYearVisitStatistics'])
            ->middleware('role_or_permission:superAdmin|Managesite.getCurrentYearVisitStatistics');

        Route::get('/yearly/visit_statistics', [UserVisiteSiteController::class, 'getYearlyVisitStatistics'])
            ->middleware('role_or_permission:superAdmin|Managesite.getYearlyVisitStatistics');
    });

    Route::get('logement/admin/statistique', [AdminHousingController::class, 'getAdminStatistics'])
        ->name('logement.getAdminStatistics')
        ->middleware('role_or_permission:Admin|superAdmin|Managelogement.getAdminStatistics');


});

    Route::middleware(['auth:sanctum', '2fa'])->group(function () {
        Route::prefix('reservation')->group(function () {
            Route::get('showDetailReservation/{reservationId}', [DashBoardTravelerController::class, 'showDetailReservation'])
            ->name('reservation.showDetailReservation')
            ->middleware('role_or_permission:superAdmin|traveler|Managereservation.showDetailReservation');

            Route::get('getReservationsForTraveler', [DashBoardTravelerController::class, 'getReservationsForTraveler'])
            ->name('reservation.getReservationsForTraveler')
            ->middleware('role_or_permission:superAdmin|traveler|Managereservation.getReservationsForTraveler');

            Route::get('getRejectedReservationsByTraveler', [DashBoardTravelerController::class, 'getRejectedReservationsByTraveler'])
            ->name('reservation.getRejectedReservationsByTraveler')
            ->middleware('role_or_permission:superAdmin|traveler|Managereservation.getRejectedReservationsByTraveler');

            Route::get('getConfirmedReservations', [DashBoardTravelerController::class, 'getConfirmedReservations'])
            ->name('reservation.getConfirmedReservations')
            ->middleware('role_or_permission:superAdmin|traveler|Managereservation.getConfirmedReservations');

            Route::get('getRejectedReservationsByHost', [DashBoardTravelerController::class, 'getRejectedReservationsByHost'])
            ->name('reservation.getRejectedReservationsByHost')
            ->middleware('role_or_permission:superAdmin|traveler|Managereservation.getRejectedReservationsByHost');

            Route::get('getUnpaidReservations', [DashBoardTravelerController::class, 'getUnpaidReservations'])
            ->name('reservation.getUnpaidReservations')
            ->middleware('role_or_permission:superAdmin|traveler|Managereservation.getUnpaidReservations');

            Route::get('getPendingConfirmations', [DashBoardTravelerController::class, 'getPendingConfirmations'])
            ->name('reservation.getPendingConfirmations')
            ->middleware('role_or_permission:superAdmin|traveler|Managereservation.getPendingConfirmations');

            Route::post('soldeReservation', [DashBoardTravelerController::class, 'soldeReservation'])
            ->name('reservation.soldeReservation')
            ->middleware('role_or_permission:superAdmin|traveler|Managereservation.soldeReservation');


        });
    });



Route::middleware(['auth:sanctum', '2fa'])->group(function () {
    Route::prefix('logement')->group(function () {
        Route::get('{housing_id}/date/visit_statistics', [UserVisiteHousingController::class, 'getVisitStatisticsDate'])
            ->name('logement.getVisitStatisticsDate')
            ->middleware('role_or_permission:superAdmin|hote|Managelogement.getVisitStatisticsDate');

        Route::get('{housing_id}/current_month/visit_statistics', [UserVisiteHousingController::class, 'getCurrentMonthVisitStatistics'])
            ->name('logement.getCurrentMonthVisitStatistics')
            ->middleware('role_or_permission:superAdmin|hote|Managelogement.getCurrentMonthVisitStatistics');

        Route::get('{housing_id}/current_year/visit_statistics', [UserVisiteHousingController::class, 'getCurrentYearVisitStatistics'])
            ->name('logement.getCurrentYearVisitStatistics')
            ->middleware('role_or_permission:superAdmin|hote|Managelogement.getCurrentYearVisitStatistics');

        Route::get('{housing_id}/yearly/visit_statistics', [UserVisiteHousingController::class, 'getYearlyVisitStatistics'])
            ->name('logement.getYearlyVisitStatistics')
            ->middleware('role_or_permission:superAdmin|hote|Managelogement.getYearlyVisitStatistics');

        Route::get('/{housingId}/visit_statistics', [UserVisiteHousingController::class, 'getHousingVisitStatistics'])
            ->name('logement.getHousingVisitStatistics')
            ->middleware('role_or_permission:superAdmin|hote|Managelogement.getHousingVisitStatistics');
    });



});



/** end Route ne nécéssitant pas l'authentification */
