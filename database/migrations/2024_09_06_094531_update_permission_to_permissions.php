<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            //
        });

        //GESTION FILE STOCKAGE

        // $sql = "UPDATE permissions SET groupe = 'GESTION_FILE_STOCKAGE', description = 'Créer un nouveau système de stockage de fichier' WHERE name = 'ManagefileStockage.store';";

        // $sql = "UPDATE permissions SET groupe = 'GESTION_FILE_STOCKAGE', description = 'Modifier un système de stockage de fichier' WHERE name = 'ManagefileStockage.update';";

        // $sql = "UPDATE permissions SET groupe = 'GESTION_FILE_STOCKAGE', description = 'Voir les détails d\'un système de stockage de fichier' WHERE name = 'ManagefileStockage.show';";

        // $sql = "UPDATE permissions SET groupe = 'GESTION_FILE_STOCKAGE', description = 'Voir les détails du système de stockage de fichier actif' WHERE name = 'ManagefileStockage.showActif';";

        // $sql = "UPDATE permissions SET groupe = 'GESTION_FILE_STOCKAGE', description = 'Voir la liste des systèmes de stockage de fichier inactifs' WHERE name = 'ManagefileStockage.indexInactif';";

        // $sql = "UPDATE permissions SET groupe = 'GESTION_FILE_STOCKAGE', description = 'Activer un système de stockage de fichier' WHERE name = 'ManagefileStockage.active';";

        // $sql = "UPDATE permissions SET groupe = 'GESTION_FILE_STOCKAGE', description = 'Supprimer un système de fichier' WHERE name = 'ManagefileStockage.delete';";

        // //GESTION PROMOTION ADMIN

        // $sql = "UPDATE permissions SET groupe = 'GESTION_PROMOTION_ADMIN', description = 'Activer une promotion' WHERE name = 'Managespromotion.active';";

        // $sql = "UPDATE permissions SET groupe = 'GESTION_PROMOTION_ADMIN', description = 'Désactiver une promotion' WHERE name = 'Managespromotion.desactive';";

        // $sql = "UPDATE permissions SET groupe = 'GESTION_PROMOTION_ADMIN', description = 'Voir la liste des promotions activées' WHERE name = 'Managespromotion.listActivePromotions';";

        // $sql = "UPDATE permissions SET groupe = 'GESTION_PROMOTION_ADMIN', description = 'Voir la liste des promotions désactivées' WHERE name = 'Managespromotion.listInactivePromotions';";


        // //GESTION REDUCTION ADMIN

        // $sql = "UPDATE permissions SET groupe = 'GESTION_REDUCTION_ADMIN', description = 'Activer une réduction' WHERE name = 'Managesreduction.activeReductionAdmin';";

        // $sql = "UPDATE permissions SET groupe = 'GESTION_REDUCTION_ADMIN', description = 'Désactiver une réduction' WHERE name = 'Managesreduction.desactiveReductionAdmin';";

        // $sql = "UPDATE permissions SET groupe = 'GESTION_REDUCTION_ADMIN', description = 'Voir la liste des réductions activées' WHERE name = 'Managesreduction.listeActiveReductionAdmin';";

        // $sql = "UPDATE permissions SET groupe = 'GESTION_REDUCTION_ADMIN', description = 'Voir la liste des réductions désactivées' WHERE name = 'Managesreduction.listeDesactiveReductionAdmin';";

        // //GESTION CHARGE ADMIN

        // $sql = "UPDATE permissions SET groupe = 'GESTION_CHARGE_ADMIN', description = 'Voir la liste des charges actives' WHERE name = 'Managecharge.indexChargeActive';";

        // $sql = "UPDATE permissions SET groupe = 'GESTION_CHARGE_ADMIN', description = 'Voir la liste des charges inactives' WHERE name = 'Managecharge.indexChargeInactive';";

        // $sql = "UPDATE permissions SET groupe = 'GESTION_CHARGE_ADMIN', description = 'Activer une charge' WHERE name = 'Managecharge.active';";

        // $sql = "UPDATE permissions SET groupe = 'GESTION_CHARGE_ADMIN', description = 'Désactiver une charge' WHERE name = 'Managecharge.desactive';";


        // DB::unprepared($sql);



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


};
