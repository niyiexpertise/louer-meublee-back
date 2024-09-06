<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        $sql = "UPDATE permissions SET groupe = 'GESTION_FILE_STOCKAGE', description = 'Créer un nouveau système de stockage de fichier' WHERE name = 'ManagefileStockage.store';";

        $sql = "UPDATE permissions SET groupe = 'GESTION_FILE_STOCKAGE', description = 'Modifier un système de stockage de fichier' WHERE name = 'ManagefileStockage.update';";

        $sql = "UPDATE permissions SET groupe = 'GESTION_FILE_STOCKAGE', description = 'Voir les détails d\'un système de stockage de fichier' WHERE name = 'ManagefileStockage.show';";

        $sql = "UPDATE permissions SET groupe = 'GESTION_FILE_STOCKAGE', description = 'Voir les détails du système de stockage de fichier actif' WHERE name = 'ManagefileStockage.showActif';";

        $sql = "UPDATE permissions SET groupe = 'GESTION_FILE_STOCKAGE', description = 'Voir la liste des systèmes de stockage de fichier inactifs' WHERE name = 'ManagefileStockage.indexInactif';";

        $sql = "UPDATE permissions SET groupe = 'GESTION_FILE_STOCKAGE', description = 'Activer un système de stockage de fichier' WHERE name = 'ManagefileStockage.active';";

        $sql = "UPDATE permissions SET groupe = 'GESTION_FILE_STOCKAGE', description = 'Supprimer un système de fichier' WHERE name = 'ManagefileStockage.delete';";

        //GESTION ADMIN PROMOTION

        $sql = "UPDATE permissions SET groupe = 'GESTION_ADMIN_PROMOTION', description = 'Activer une promotion' WHERE name = 'Managespromotion.active';";

        $sql = "UPDATE permissions SET groupe = 'GESTION_ADMIN_PROMOTION', description = 'Désactiver une promotion' WHERE name = 'Managespromotion.desactive';";

        $sql = "UPDATE permissions SET groupe = 'GESTION_ADMIN_PROMOTION', description = 'Voir la liste des promotions activées' WHERE name = 'Managespromotion.listActivePromotions';";

        $sql = "UPDATE permissions SET groupe = 'GESTION_ADMIN_PROMOTION', description = 'Voir la liste des promotions désactivées' WHERE name = 'Managespromotion.listInactivePromotions';";


        //GESTION ADMIN REDUCTION

        $sql = "UPDATE permissions SET groupe = 'GESTION_ADMIN_REDUCTION', description = 'Activer une réduction' WHERE name = 'Managesreduction.activeReductionAdmin';";

        $sql = "UPDATE permissions SET groupe = 'GESTION_ADMIN_REDUCTION', description = 'Désactiver une réduction' WHERE name = 'Managesreduction.desactiveReductionAdmin';";

        $sql = "UPDATE permissions SET groupe = 'GESTION_ADMIN_REDUCTION', description = 'Voir la liste des réductions activées' WHERE name = 'Managesreduction.listeActiveReductionAdmin';";

        $sql = "UPDATE permissions SET groupe = 'GESTION_ADMIN_REDUCTION', description = 'Voir la liste des réductions désactivées' WHERE name = 'Managesreduction.listeDesactiveReductionAdmin';";



    }

    
};
