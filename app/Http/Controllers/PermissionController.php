<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role  ;

class PermissionController extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/permission/index",
     *     summary="Get all permissions",
     *     tags={"Permission"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of permissions"
     *
     *     )
     * )
     */
    public function index()
    {
        try{
            $permissions = Permission::all();
            return response()->json([
                'permissions' => $permissions
            ],200);
            }catch (Exception $e){
                  return response()->json(['error' => $e->getMessage()], 500);
            }
        

    }


  

     /**
     * @OA\Get(
     *     path="/api/permission/show/{id}",
     *     summary="Get a specific permission by ID",
     *     tags={"Permission"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the permission",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Permission not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        try{
            $permission = Permission::find($id);
            return response()->json([
                'data' => $permission
            ],200);
        }catch (Exception $e){
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }
/**
     * @OA\Get(
     *     path="/api/permission/indexbycategorie",
     *     summary="Get all permissions groupe by categorie",
     *     tags={"Permission"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of permissions groupe by category"
     * 
     *     )
     * )
     */
    public function indexbycategorie()
{
    try {
        $permissions = Permission::all();
        
        $groupedPermissions = $permissions->filter(function ($permission) {
            return !is_null($permission->groupe);
        })->groupBy('groupe')->map(function ($group, $groupeName) {
            return [
                'group_name' => $groupeName, 
                'permissions' => $group->map(function ($permission) use ($groupeName) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'description' => $permission->description,
                    ];
                })->toArray(),
                'count' => $group->count() 
            ];
        });

        $response = $groupedPermissions->values()->toArray();

        return response()->json($response, 200);

    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}




    /**
     * @OA\Post(
     *     path="/api/updateOrInsert",
     *     summary="Update or Insert Permissions",
     *     description="Update permissions if they exist or insert them if they do not.",
     *     tags={"Permission"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Permissions updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */


public function updatePermissions()
    {
        $permissions = [
            //GESTION_CHARGE_ADMIN
            [
                'name' => 'Managecharge.indexChargeActive',
                'groupe' => 'GESTION_CHARGE_ADMIN',
                'description' => 'Voir la liste des charges actives'
            ],
            [
                'name' => 'Managecharge.indexChargeInactive',
                'groupe' => 'GESTION_CHARGE_ADMIN',
                'description' => 'Voir la liste des charges inactives'
            ],
            [
                'name' => 'Managecharge.active',
                'groupe' => 'GESTION_CHARGE_ADMIN',
                'description' => 'Activer une charge'
            ],
            [
                'name' => 'Managecharge.desactive',
                'groupe' => 'GESTION_CHARGE_ADMIN',
                'description' => 'Désactiver une charge'
            ],
            [
                'name' => 'Managecharge.indexChargeActive',
                'groupe' => 'GESTION_CHARGE_ADMIN',
                'description' => 'Voir la liste des charges actives'
            ],
            [
                'name' => 'Managecharge.indexChargeInactive',
                'groupe' => 'GESTION_CHARGE_ADMIN',
                'description' => 'Voir la liste des charges inactives'
            ],
            [
                'name' => 'Managecharge.active',
                'groupe' => 'GESTION_CHARGE_ADMIN',
                'description' => 'Activer une charge'
            ],
            [
                'name' => 'Managecharge.desactive',
                'groupe' => 'GESTION_CHARGE_ADMIN',
                'description' => 'Désactiver une charge'
            ],
            // GESTION FILE STOCKAGE
            [
                'name' => 'ManagefileStockage.store',
                'groupe' => 'GESTION_FILE_STOCKAGE',
                'description' => 'Créer un nouveau système de stockage de fichier'
            ],
            [
                'name' => 'ManagefileStockage.update',
                'groupe' => 'GESTION_FILE_STOCKAGE',
                'description' => 'Modifier un système de stockage de fichier'
            ],
            [
                'name' => 'ManagefileStockage.show',
                'groupe' => 'GESTION_FILE_STOCKAGE',
                'description' => 'Voir les détails d\'un système de stockage de fichier'
            ],
            [
                'name' => 'ManagefileStockage.showActif',
                'groupe' => 'GESTION_FILE_STOCKAGE',
                'description' => 'Voir les détails du système de stockage de fichier actif'
            ],
            [
                'name' => 'ManagefileStockage.indexInactif',
                'groupe' => 'GESTION_FILE_STOCKAGE',
                'description' => 'Voir la liste des systèmes de stockage de fichier inactifs'
            ],
            [
                'name' => 'ManagefileStockage.active',
                'groupe' => 'GESTION_FILE_STOCKAGE',
                'description' => 'Activer un système de stockage de fichier'
            ],
            [
                'name' => 'ManagefileStockage.delete',
                'groupe' => 'GESTION_FILE_STOCKAGE',
                'description' => 'Supprimer un système de fichier'
            ],
            // GESTION PROMOTION ADMIN
            [
                'name' => 'Managespromotion.active',
                'groupe' => 'GESTION_PROMOTION_ADMIN',
                'description' => 'Activer une promotion'
            ],
            [
                'name' => 'Managespromotion.desactive',
                'groupe' => 'GESTION_PROMOTION_ADMIN',
                'description' => 'Désactiver une promotion'
            ],
            [
                'name' => 'Managespromotion.listActivePromotions',
                'groupe' => 'GESTION_PROMOTION_ADMIN',
                'description' => 'Voir la liste des promotions activées'
            ],
            [
                'name' => 'Managespromotion.listInactivePromotions',
                'groupe' => 'GESTION_PROMOTION_ADMIN',
                'description' => 'Voir la liste des promotions désactivées'
            ],

            
            // GESTION REDUCTION ADMIN
            [
                'name' => 'Managesreduction.activeReductionAdmin',
                'groupe' => 'GESTION_REDUCTION_ADMIN',
                'description' => 'Activer une réduction'
            ],
            [
                'name' => 'Managesreduction.desactiveReductionAdmin',
                'groupe' => 'GESTION_REDUCTION_ADMIN',
                'description' => 'Désactiver une réduction'
            ],
            [
                'name' => 'Managesreduction.listeActiveReductionAdmin',
                'groupe' => 'GESTION_REDUCTION_ADMIN',
                'description' => 'Voir la liste des réductions activées'
            ],
            [
                'name' => 'Managesreduction.listeDesactiveReductionAdmin',
                'groupe' => 'GESTION_REDUCTION_ADMIN',
                'description' => 'Voir la liste des réductions désactivées'
            ],
            // GESTION TARIF SPONSORING ADMIN
    [
        'name' => 'Managesponsoring.indexAdmin',
        'groupe' => 'GESTION_TARIF_SPONSORING_ADMIN',
        'description' => 'Voir la liste complète des tarifs de sponsoring'
    ],
    [
        'name' => 'Managesponsoring.indexActifAdmin',
        'groupe' => 'GESTION_TARIF_SPONSORING_ADMIN',
        'description' => 'Voir la liste des tarifs de sponsoring actifs'
    ],
    [
        'name' => 'Managesponsoring.indexInactifAdmin',
        'groupe' => 'GESTION_TARIF_SPONSORING_ADMIN',
        'description' => 'Voir la liste des tarifs de sponsoring inactifs'
    ],
    [
        'name' => 'Managesponsoring.store',
        'groupe' => 'GESTION_TARIF_SPONSORING_ADMIN',
        'description' => 'Créer un nouveau tarif de sponsoring'
    ],
    [
        'name' => 'Managesponsoring.update',
        'groupe' => 'GESTION_TARIF_SPONSORING_ADMIN',
        'description' => 'Modifier un tarif de sponsoring'
    ],
    [
        'name' => 'Managesponsoring.show',
        'groupe' => 'GESTION_TARIF_SPONSORING_ADMIN',
        'description' => 'Voir les détails d\'un tarif de sponsoring'
    ],
    [
        'name' => 'Managesponsoring.destroy',
        'groupe' => 'GESTION_TARIF_SPONSORING_ADMIN',
        'description' => 'Supprimer un tarif de sponsoring'
    ],
    [
        'name' => 'Managesponsoring.active',
        'groupe' => 'GESTION_TARIF_SPONSORING_ADMIN',
        'description' => 'Activer un tarif de sponsoring'
    ],
    [
        'name' => 'Managesponsoring.desactive',
        'groupe' => 'GESTION_TARIF_SPONSORING_ADMIN',
        'description' => 'Désactiver un tarif de sponsoring'
    ],

    // GESTION SPONSORING ADMIN
    [
        'name' => 'Managesponsoring.demandeSponsoringNonvalidee',
        'groupe' => 'GESTION_SPONSORING_ADMIN',
        'description' => 'Voir la liste des demandes de sponsoring non validée par l\'administrateur'
    ],
    [
        'name' => 'Managesponsoring.demandeSponsoringvalidee',
        'groupe' => 'GESTION_SPONSORING_ADMIN',
        'description' => 'Voir la liste des demandes de sponsoring validée par l\'administrateur'
    ],
    [
        'name' => 'Managesponsoring.rejectSponsoringRequest',
        'groupe' => 'GESTION_SPONSORING_ADMIN',
        'description' => 'Rejeter une demande de sponsoring'
    ],
    [
        'name' => 'Managesponsoring.demandeSponsoringrejetee',
        'groupe' => 'GESTION_SPONSORING_ADMIN',
        'description' => 'Voir la liste des demandes de sponsoring rejetée par l\'administrateur'
    ],
    [
        'name' => 'Managesponsoring.demandeSponsoringsupprimee',
        'groupe' => 'GESTION_SPONSORING_ADMIN',
        'description' => 'Voir la liste des demandes de sponsoring supprimée par l\'administrateur par les hôtes'
    ],
    [
        'name' => 'Managesponsoring.validSponsoringRequest',
        'groupe' => 'GESTION_SPONSORING_ADMIN',
        'description' => 'Valider une demande de sponsoring'
    ],
    [
        'name' => 'Managesponsoring.invalidSponsoringRequest',
        'groupe' => 'GESTION_SPONSORING_ADMIN',
        'description' => 'Invalider une demande de sponsoring'
    ],
    
    // GESTION PROMOTION ADMIN
    [
        'name' => 'Managespromotion.active',
        'groupe' => 'GESTION_PROMOTION_ADMIN',
        'description' => 'Activer une promotion'
    ],
    [
        'name' => 'Managespromotion.desactive',
        'groupe' => 'GESTION_PROMOTION_ADMIN',
        'description' => 'Désactiver une promotion'
    ],
    [
        'name' => 'Managespromotion.listActivePromotions',
        'groupe' => 'GESTION_PROMOTION_ADMIN',
        'description' => 'Voir la liste des promotions activées'
    ],
    [
        'name' => 'Managespromotion.listInactivePromotions',
        'groupe' => 'GESTION_PROMOTION_ADMIN',
        'description' => 'Voir la liste des promotions désactivées'
    ],

    // GESTION REDUCTION ADMIN
    [
        'name' => 'Managesreduction.activeReductionAdmin',
        'groupe' => 'GESTION_REDUCTION_ADMIN',
        'description' => 'Activer une réduction'
    ],
    [
        'name' => 'Managesreduction.desactiveReductionAdmin',
        'groupe' => 'GESTION_REDUCTION_ADMIN',
        'description' => 'Désactiver une réduction'
    ],
    [
        'name' => 'Managesreduction.listeActiveReductionAdmin',
        'groupe' => 'GESTION_REDUCTION_ADMIN',
        'description' => 'Voir la liste des réductions activées'
    ],
    [
        'name' => 'Managesreduction.listeDesactiveReductionAdmin',
        'groupe' => 'GESTION_REDUCTION_ADMIN',
        'description' => 'Voir la liste des réductions désactivées'
    ],

     // GESTION SERVICE PAIEMENT ADMIN
     [
        'name' => 'Manageservicepaiement.getServicesByMethodPaiement',
        'groupe' => 'GESTION_SERVICE_PAIEMENT_ADMIN',
        'description' => 'Voir la liste des services par méthodes de paiement'
    ],
    [
        'name' => 'Manageservicepaiement.getActiveServices',
        'groupe' => 'GESTION_SERVICE_PAIEMENT_ADMIN',
        'description' => 'Voir la liste des services actifs'
    ],
    [
        'name' => 'Manageservicepaiement.getInactiveServices',
        'groupe' => 'GESTION_SERVICE_PAIEMENT_ADMIN',
        'description' => 'Voir la liste des services inactifs'
    ],
    [
        'name' => 'Manageservicepaiement.active',
        'groupe' => 'GESTION_SERVICE_PAIEMENT_ADMIN',
        'description' => 'Activer un service de paiement'
    ],
    [
        'name' => 'Manageservicepaiement.desactive',
        'groupe' => 'GESTION_SERVICE_PAIEMENT_ADMIN',
        'description' => 'Désactiver un service de paiement'
    ],
    [
        'name' => 'Manageservicepaiement.update',
        'groupe' => 'GESTION_SERVICE_PAIEMENT_ADMIN',
        'description' => 'Modifier un service de paiement'
    ],
    [
        'name' => 'Manageservicepaiement.store',
        'groupe' => 'GESTION_SERVICE_PAIEMENT_ADMIN',
        'description' => 'Ajouter un service de paiement'
    ],
    [
        'name' => 'Manageservicepaiement.showServiceActifByMethodPaiement',
        'groupe' => 'GESTION_SERVICE_PAIEMENT_ADMIN',
        'description' => 'Voir le service actif d\'une méthode de paiement'
    ],
    [
        'name' => 'Manageservicepaiement.show',
        'groupe' => 'GESTION_SERVICE_PAIEMENT_ADMIN',
        'description' => 'Voir les détails d\'une méthode de paiement'
    ],
    [
        'name' => 'Manageservicepaiement.destroy',
        'groupe' => 'GESTION_SERVICE_PAIEMENT_ADMIN',
        'description' => 'Supprimer une méthode de paiement'
    ],
    [
        'name' => 'Manageservicepaiement.desactiveSandbox',
        'groupe' => 'GESTION_SERVICE_PAIEMENT_ADMIN',
        'description' => 'Désactiver le mode sandbox d\'une méthode de paiement'
    ],
    [
        'name' => 'Manageservicepaiement.activeSandbox',
        'groupe' => 'GESTION_SERVICE_PAIEMENT_ADMIN',
        'description' => 'Activer le mode sandbox d\'une méthode de paiement'
    ],
    [
        'name' => 'Manageservicepaiement.getSandboxServices',
        'groupe' => 'GESTION_SERVICE_PAIEMENT_ADMIN',
        'description' => 'Récupérer la liste des services qui sont en mode sandbox'
    ],
    [
        'name' => 'Manageservicepaiement.getNotSandboxServices',
        'groupe' => 'GESTION_SERVICE_PAIEMENT_ADMIN',
        'description' => 'Récupérer la liste des services qui ne sont pas en mode sandbox'
    ],
    // validateCategoriesHousing
    //GESTION_CATEGORIE_ADMIN_LOGEMENT
    [
        'name' => 'Managelogement.validateHousingCategoryFile',
        'groupe' => 'GESTION_CATEGORIE_ADMIN_LOGEMENT',
        'description' => "Mettre à jour le statut de vérification des fichiers des pièces d'un logement"
    ],
    [
        'name' => 'Managelogement.getCategoryPhotoUnverified',
        'groupe' => 'GESTION_CATEGORIE_ADMIN_LOGEMENT',
        'description' => "Récupérer les photos des catégories(pièce) en attente de validation"
    ],
    [
        'name' => 'Managelogement.getHousingCategoryUnverifiedFiles',
        'groupe' => 'GESTION_CATEGORIE_ADMIN_LOGEMENT',
        'description' => "Récupérer les logements avec des photos non vérifiées par catégorie (vérifié ou non)"
    ],
    [
        'name' => 'Managelogement.validateDefaultCategoriesHousing',
        'groupe' => 'GESTION_CATEGORIE_ADMIN_LOGEMENT',
        'description' => "Valide plusieurs catégories par défaut en attente de validation pour différents logements"
    ],
    [
        'name' => 'Managelogement.validateInexistantCategoriesHousing',
        'groupe' => 'GESTION_CATEGORIE_ADMIN_LOGEMENT',
        'description' => "Valide plusieurs nouvelles catégories  en attente de validation pour différents logement."
    ],
    [
        'name' => 'Managelogement.validateCategoriesHousing',
        'groupe' => 'GESTION_CATEGORIE_ADMIN_LOGEMENT',
        'description' => "Valide plusieurs  catégories qu'elles soient vérifié ou non  en attente de validation pour différents logement."
    ],

    //GESTION_CATEGORIES_HOTE_LOGEMENT
    [
        'name' => 'Managelogement.addPhotoCategory',
        'groupe' => 'GESTION_CATEGORIES_HOTE_LOGEMENT',
        'description' => "Ajouter des photos à une catégorie d'un logement"
    ],
    [
        'name' => 'Managelogement.updateHousingCategoryNumber',
        'groupe' => 'GESTION_CATEGORIES_HOTE_LOGEMENT',
        'description' => "Mettre à jour le nombre de pièces d'une catégorie de logement"
    ],
    [
        'name' => 'Managelogement.getHousingCategoryFile',
        'groupe' => 'GESTION_CATEGORIES_HOTE_LOGEMENT',
        'description' => "Obtenir les fichiers d'une catégorie pour un logement"
    ],
    [
        'name' => 'Managelogement.getRemainingCategories',
        'groupe' => 'GESTION_CATEGORIES_HOTE_LOGEMENT',
        'description' => "Récupérer les catégories (pièces) restant à ajouter à un logement"
    ],
    [
        'name' => 'Managelogement.getHousingCategories',
        'groupe' => 'GESTION_CATEGORIES_HOTE_LOGEMENT',
        'description' => "Récupérer les catégories (pièces) d'un logement"
    ],
    [
        'name' => 'Managelogement.addCategoryToHousing',
        'groupe' => 'GESTION_CATEGORIES_HOTE_LOGEMENT',
        'description' => "Ajouter des catégories à un logement"
    ],
    //GESTION_EQUIPEMENT__HOTE_LOGEMENT
    [
        'name' => 'Managelogement.addCategoryToHousing',
        'groupe' => 'GESTION_EQUIPEMENT__HOTE_LOGEMENT',
        'description' => "Ajouter des équipements aux pièces d'un logement"
    ],
    //GESTION_EQUIPEMENT_ADMIN_LOGEMENT
    [
        'name' => 'Managelogement.getUnverifiedHousingCategoryEquipmentExistant',
        'groupe' => 'GESTION_EQUIPEMENT_ADMIN_LOGEMENT',
        'description' => "Récupérer les équipements existants des catégories(pièce) en attente de validation"
    ],
    [
        'name' => 'Managelogement.getUnverifiedHousingCategoryEquipmentInexistant',
        'groupe' => 'GESTION_EQUIPEMENT_ADMIN_LOGEMENT',
        'description' => "Récupérer les équipements inexistants des catégories(pièce) en attente de validation"
    ],
    [
        'name' => 'Managelogement.getHousingCategoriesEquipmentForAdd',
        'groupe' => 'GESTION_EQUIPEMENT_ADMIN_LOGEMENT',
        'description' => "Récupère la liste des pièces (catégories) et les équipements qui restent à ajouter pour chaque pièce associée à un logement donné."
    ],

    //GESTION_PHOTO__HOTE_LOGEMENT
    [
        'name' => 'Managelogement.getHousingPhoto',
        'groupe' => 'GESTION_PHOTO__HOTE_LOGEMENT',
        'description' => "Obtenir les photos d'un logement"
    ],
        ];

        foreach ($permissions as $permission) {
            // Vérifier si l'entrée existe déjà
            $existingPermission = DB::table('permissions')->where('name', $permission['name'])->first();

            if ($existingPermission) {
                // Mettre à jour l'entrée existante
                DB::table('permissions')->where('name', $permission['name'])->update([
                    'groupe' => $permission['groupe'],
                    'description' => $permission['description']
                ]);
            } else {
                // Créer une nouvelle entrée
                DB::table('permissions')->insert([
                    'name' => $permission['name'],
                    'groupe' => $permission['groupe'],
                    'guard_name' => 'web',
                    'description' => $permission['description'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        return response()->json(['message' => 'Permissions mises à jour avec succès.']);
    }



    /**
     * @OA\Get(
     *     path="/api/permission/indexbycategorieforuser/{userId}",
     *     summary="Get all permissions groupe by categorie for user",
     *     tags={"Permission"},
     *       @OA\Parameter(
 *         name="userId",
 *         in="path",
 *         required=true,
 *         description="id de l'utilisateur)",
 *         @OA\Schema(type="string")
 *     ),
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of permissions groupe by category"
     * 
     *     )
     * )
     */
    public function indexbycategorieforuser($userId)
{
    try {

        $user= User::whereId($userId)->exists();

        if(!$user){
            return (new ServiceController())->apiResponse(404, [],'Utilisateur non trouvé');
        }

        $permissions = Permission::all();

        $userPerms = (new AuthController)->getUserPerms($userId);



        $userPermissions = array_merge(
            $userPerms->original['data']['directPermissions'] ?? [],
            $userPerms->original['data']['indirectPermissions'] ?? []
        );

        $groupedPermissions = $permissions->filter(function ($permission) {
            return !is_null($permission->groupe); 
        })->groupBy('groupe')->map(function ($group, $groupeName) use ($userPermissions) {
            return [
                'group_name' => $groupeName, 
                'permissions' => $group->map(function ($permission) use ($userPermissions) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'description' => $permission->description,
                        'active' => in_array($permission->name, $userPermissions) 
                    ];
                })->toArray(),
                'count' => $group->count()
            ];
        });

        $response = $groupedPermissions->values()->toArray(); 

        return response()->json($response, 200);

    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


    /**
     * @OA\Get(
     *     path="/api/permission/indexbycategorieforrole/{roleId}",
     *     summary="Get all permissions groupe by categorie for role",
     *     tags={"Permission"},
     *       @OA\Parameter(
 *         name="roleId",
 *         in="path",
 *         required=true,
 *         description="id de l'utilisateur)",
 *         @OA\Schema(type="string")
 *     ),
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of permissions groupe by category"
     * 
     *     )
     * )
     */

public function indexbycategorieforrole($roleId)
{
    try {

        $role= Role::whereId($roleId)->exists();

        if(!$role){
            return (new ServiceController())->apiResponse(404, [],'rôle non trouvé');
        }
        $permissions = Permission::all();


        $rolePermissions =(new AuthController)->rolesPerms($roleId)->original['data']?? [];


       $rolePermissionName = [];

        foreach ($rolePermissions as $permission) {
            $rolePermissionName[] = $permission->name;
        }


        $groupedPermissions = $permissions->filter(function ($permission) {
            return !is_null($permission->groupe);
        })->groupBy('groupe')->map(function ($group, $groupeName) use ($rolePermissionName) {
            return [
                'group_name' => $groupeName, 
                'permissions' => $group->map(function ($permission) use ($rolePermissionName) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'description' => $permission->description,
                        'active' => in_array($permission->name, $rolePermissionName)
                    ];
                })->toArray(),
                'count' => $group->count()
            ];
        });

        $response = $groupedPermissions->values()->toArray();
        return response()->json($response, 200);

    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

function getEmailsByPermissionName(string $permissionName)
{
    $permission = Permission::where('name', $permissionName)->first();

    if (!$permission) {
        return [];
    }

    $directUsers = DB::table('model_has_permissions')
        ->join('users', 'model_has_permissions.model_id', '=', 'users.id')
        ->where('model_has_permissions.permission_id', $permission->id)
        ->pluck('users.email')
        ->toArray();

    $roleUsers = DB::table('model_has_roles')
        ->join('role_has_permissions', 'model_has_roles.role_id', '=', 'role_has_permissions.role_id')
        ->join('users', 'model_has_roles.model_id', '=', 'users.id')
        ->where('role_has_permissions.permission_id', $permission->id)
        ->pluck('users.email')
        ->toArray();

    $allEmails = array_unique(array_merge($directUsers, $roleUsers));

    if(count($allEmails) == 0){
        return $this->getSuperAdminEmails();

    }

    return $allEmails;
}

function getSuperAdminEmails()
{

    $superAdminRole = Role::where('name', 'superAdmin')->first();

    if (!$superAdminRole) {
        return [];
    }

    $superAdminEmails = DB::table('model_has_roles')
        ->join('users', 'model_has_roles.model_id', '=', 'users.id')
        ->where('model_has_roles.role_id', $superAdminRole->id)
        ->pluck('users.email')
        ->toArray();

    return $superAdminEmails;
}





}


// $personToNotify = (new PermissionController())->getEmailsByPermissionName('Managelogement.validateCategoriesHousing');
//     (new NotificationController())->store($personToNotify,$mail['body'],$mail['title'],2);

// Managelogement.validateHousingCategoryFile
// Managelogement.validatePhoto
// Managelogement.validatePhoto
// Managelogement.makeVerifiedHousingEquipment

//  $personToNotify = (new PermissionController())->getEmailsByPermissionName('Managelogement.makeVerifiedHousingEquipment');
// (new NotificationController())->store($personToNotify,$mail['body'],$mail['title'],2);

// Managelogement.makeVerifiedHousingPreference

//Managesponsoring.validSponsoringRequest
// Manageretrait.validateRetraitByAdmin
//Manageverificationdocumenthote.validateDocument

// (new NotificationController())->storeAndSendFileEmail($this->email,$this->name,$this->object,$this->attachedFiles)

// Manageverificationdocumentpartenaire.validateDocument