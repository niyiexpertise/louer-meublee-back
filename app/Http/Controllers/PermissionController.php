<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
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
      * @OA\Post(
      *     path="/api/permission/store",
      *     summary="Create a new permission ",
      *     tags={"Permission"},
      *security={{"bearerAuth": {}}},
      *     @OA\RequestBody(
      *         required=true,
      *         @OA\JsonContent(
      *             required={"name"},
      *             @OA\Property(property="name", type="string", example="create"),
      *         )
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="Permission  created successfully"
      *     ),
      *     @OA\Response(
      *         response=401,
      *         description="Invalid credentials"
      *     )
      * )
      */
    public function store(Request $request)
    {
        try{
            $data = $request->validate([
                'name' => 'required|unique:permissions|max:255',
            ]);
            $exist = Permission::where('name',$request->name)->exists();
            if($exist){
                return response()->json([
                    "message"=>"This name has already taken"
                ]);
            }
                $permission = new Permission();
                $permission->name = $request->name;
                $permission->guard_name= "web";
                $permission->save();
                return response()->json([
                    'message' =>'Successfully created',
                    'permission' => $permission
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
            // Récupérer toutes les permissions
            $permissions = Permission::all();
    
            // Grouper les permissions par groupe
            $groupedPermissions = $permissions->filter(function ($permission) {
                return !is_null($permission->groupe); // Filtrer les permissions où groupe n'est pas nul
            })->groupBy('groupe')->map(function ($group) {
                return [
                    'permissions' => $group->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'description' => $permission->description,
                            // Ajoutez d'autres champs pertinents ici
                        ];
                    }),
                    'count' => $group->count() // Nombre de permissions dans ce groupe
                ];
            });
    
            // Calculer le nombre total de permissions en faisant la somme des sous-totaux
            $totalPermissionsCount = $groupedPermissions->sum('count');
    
            // Préparer la structure de la réponse
            $response = [
                'groups' => $groupedPermissions->mapWithKeys(function ($data, $group) {
                    return [$group => [
                        'permissions' => $data['permissions'],
                        'count' => $data['count']
                    ]];
                }),
                'total_permissions_count' => $totalPermissionsCount
            ];
    
            // Retourner la réponse JSON
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
public function updatePermission()
{
    try {
        // GESTION CHARGE ADMIN
        DB::statement("
            INSERT INTO permissions (name, groupe, description)
            VALUES 
                ('Managecharge.indexChargeActive', 'GESTION_CHARGE_ADMIN', 'Voir la liste des charges actives'),
                ('Managecharge.indexChargeInactive', 'GESTION_CHARGE_ADMIN', 'Voir la liste des charges inactives'),
                ('Managecharge.active', 'GESTION_CHARGE_ADMIN', 'Activer une charge'),
                ('Managecharge.desactive', 'GESTION_CHARGE_ADMIN', 'Désactiver une charge')
            ON CONFLICT (name) DO UPDATE
                SET groupe = EXCLUDED.groupe,
                    description = EXCLUDED.description;
        ");

        // GESTION FILE STOCKAGE
        DB::statement("
            INSERT INTO permissions (name, groupe, description)
            VALUES 
                ('ManagefileStockage.store', 'GESTION_FILE_STOCKAGE', 'Créer un nouveau système de stockage de fichier'),
                ('ManagefileStockage.update', 'GESTION_FILE_STOCKAGE', 'Modifier un système de stockage de fichier'),
                ('ManagefileStockage.show', 'GESTION_FILE_STOCKAGE', 'Voir les détails d\'un système de stockage de fichier'),
                ('ManagefileStockage.showActif', 'GESTION_FILE_STOCKAGE', 'Voir les détails du système de stockage de fichier actif'),
                ('ManagefileStockage.indexInactif', 'GESTION_FILE_STOCKAGE', 'Voir la liste des systèmes de stockage de fichier inactifs'),
                ('ManagefileStockage.active', 'GESTION_FILE_STOCKAGE', 'Activer un système de stockage de fichier'),
                ('ManagefileStockage.delete', 'GESTION_FILE_STOCKAGE', 'Supprimer un système de fichier')
            ON CONFLICT (name) DO UPDATE
                SET groupe = EXCLUDED.groupe,
                    description = EXCLUDED.description;
        ");

        // GESTION PROMOTION ADMIN
        DB::statement("
            INSERT INTO permissions (name, groupe, description)
            VALUES 
                ('Managespromotion.active', 'GESTION_PROMOTION_ADMIN', 'Activer une promotion'),
                ('Managespromotion.desactive', 'GESTION_PROMOTION_ADMIN', 'Désactiver une promotion'),
                ('Managespromotion.listActivePromotions', 'GESTION_PROMOTION_ADMIN', 'Voir la liste des promotions activées'),
                ('Managespromotion.listInactivePromotions', 'GESTION_PROMOTION_ADMIN', 'Voir la liste des promotions désactivées')
            ON CONFLICT (name) DO UPDATE
                SET groupe = EXCLUDED.groupe,
                    description = EXCLUDED.description;
        ");

        // GESTION REDUCTION ADMIN
        DB::statement("
            INSERT INTO permissions (name, groupe, description)
            VALUES 
                ('Managesreduction.activeReductionAdmin', 'GESTION_REDUCTION_ADMIN', 'Activer une réduction'),
                ('Managesreduction.desactiveReductionAdmin', 'GESTION_REDUCTION_ADMIN', 'Désactiver une réduction'),
                ('Managesreduction.listeActiveReductionAdmin', 'GESTION_REDUCTION_ADMIN', 'Voir la liste des réductions activées'),
                ('Managesreduction.listeDesactiveReductionAdmin', 'GESTION_REDUCTION_ADMIN', 'Voir la liste des réductions désactivées')
            ON CONFLICT (name) DO UPDATE
                SET groupe = EXCLUDED.groupe,
                    description = EXCLUDED.description;
        ");

        // GESTION TARIF SPONSORING ADMIN
        DB::statement("
            INSERT INTO permissions (name, groupe, description)
            VALUES 
                ('Managesponsoring.indexAdmin', 'GESTION_TARIF_SPONSORING_ADMIN', 'Voir la liste complète des tarifs de sponsoring'),
                ('Managesponsoring.indexActifAdmin', 'GESTION_TARIF_SPONSORING_ADMIN', 'Voir la liste des tarifs de sponsoring actifs'),
                ('Managesponsoring.indexInactifAdmin', 'GESTION_TARIF_SPONSORING_ADMIN', 'Voir la liste des tarifs de sponsoring inactifs'),
                ('Managesponsoring.store', 'GESTION_TARIF_SPONSORING_ADMIN', 'Créer un nouveau tarif de sponsoring'),
                ('Managesponsoring.update', 'GESTION_TARIF_SPONSORING_ADMIN', 'Modifier un tarif de sponsoring'),
                ('Managesponsoring.show', 'GESTION_TARIF_SPONSORING_ADMIN', 'Voir les détails d\'un tarif de sponsoring'),
                ('Managesponsoring.destroy', 'GESTION_TARIF_SPONSORING_ADMIN', 'Supprimer un tarif de sponsoring'),
                ('Managesponsoring.active', 'GESTION_TARIF_SPONSORING_ADMIN', 'Activer un tarif de sponsoring'),
                ('Managesponsoring.desactive', 'GESTION_TARIF_SPONSORING_ADMIN', 'Désactiver un tarif de sponsoring')
            ON CONFLICT (name) DO UPDATE
                SET groupe = EXCLUDED.groupe,
                    description = EXCLUDED.description;
        ");

        // GESTION SPONSORING ADMIN
        DB::statement("
            INSERT INTO permissions (name, groupe, description)
            VALUES 
                ('Managesponsoring.demandeSponsoringNonvalidee', 'GESTION_SPONSORING_ADMIN', 'Voir la liste des demandes de sponsoring non validée par l\'administrateur'),
                ('Managesponsoring.demandeSponsoringvalidee', 'GESTION_SPONSORING_ADMIN', 'Voir la liste des demandes de sponsoring validée par l\'administrateur'),
                ('Managesponsoring.rejectSponsoringRequest', 'GESTION_SPONSORING_ADMIN', 'Rejeter une demande de sponsoring'),
                ('Managesponsoring.demandeSponsoringrejetee', 'GESTION_SPONSORING_ADMIN', 'Voir la liste des demandes de sponsoring rejetée par l\'administrateur'),
                ('Managesponsoring.demandeSponsoringsupprimee', 'GESTION_SPONSORING_ADMIN', 'Voir la liste des demandes de sponsoring supprimée par l\'administrateur par les hôtes'),
                ('Managesponsoring.validSponsoringRequest', 'GESTION_SPONSORING_ADMIN', 'Valider une demande de sponsoring'),
                ('Managesponsoring.invalidSponsoringRequest', 'GESTION_SPONSORING_ADMIN', 'Invalider une demande de sponsoring')
            ON CONFLICT (name) DO UPDATE
                SET groupe = EXCLUDED.groupe,
                    description = EXCLUDED.description;
        ");
    } catch (\Exception $e) {
        // Log the error message
        Log::error('Database update failed: ' . $e->getMessage());
    }
}


public function updatePermissions()
    {
        $permissions = [
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
    ]
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



}