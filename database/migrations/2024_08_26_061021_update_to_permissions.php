<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        $sql = "UPDATE permissions SET groupe = 'GESTION_TARIF_SPONSORING_ADMIN', description = 'Voir la liste complète des tarifs de sponsoring' WHERE name = 'Managesponsoring.indexAdmin';";
        $sql = "UPDATE permissions SET groupe = 'GESTION_TARIF_SPONSORING_ADMIN', description = 'Voir la liste des tarifs de sponsoring actifs' WHERE name = 'Managesponsoring.indexActifAdmin';";
        $sql = "UPDATE permissions SET groupe = 'GESTION_TARIF_SPONSORING_ADMIN', description = 'Voir la liste des tarifs de sponsoring inactifs' WHERE name = 'Managesponsoring.indexInactifAdmin';";
        $sql = "UPDATE permissions SET groupe = 'GESTION_TARIF_SPONSORING_ADMIN', description = 'Créer un nouveau tarif de sponsoring' WHERE name = 'Managesponsoring.store';";
        $sql = "UPDATE permissions SET groupe = 'GESTION_TARIF_SPONSORING_ADMIN', description = 'Modifier un tarif de sponsoring' WHERE name = 'Managesponsoring.update';";
        $sql = "UPDATE permissions SET groupe = 'GESTION_TARIF_SPONSORING_ADMIN', description = 'Voir les détails d'un tarif de sponsoring' WHERE name = 'Managesponsoring.show';";
        $sql = "UPDATE permissions SET groupe = 'GESTION_TARIF_SPONSORING_ADMIN', description = 'Supprimer un tarif de sponsoring' WHERE name = 'Managesponsoring.destroy';";
        $sql = "UPDATE permissions SET groupe = 'GESTION_TARIF_SPONSORING_ADMIN', description = 'Activer un tarif de sponsoring' WHERE name = 'Managesponsoring.active';";
        $sql = "UPDATE permissions SET groupe = 'GESTION_TARIF_SPONSORING_ADMIN', description = 'Désactiver un tarif de sponsoring' WHERE name = 'Managesponsoring.desactive';";

        $sql = "UPDATE permissions SET groupe = 'GESTION_SPONSORING_ADMIN', description = 'Voir la liste des demandes de sponsoring non validée par l\'administrateur' WHERE name = 'Managesponsoring.demandeSponsoringNonvalidee';";
        $sql = "UPDATE permissions SET groupe = 'GESTION_SPONSORING_ADMIN', description = 'Voir la liste des demandes de sponsoring validée par l\'administrateur' WHERE name = 'Managesponsoring.demandeSponsoringvalidee';";
        $sql = "UPDATE permissions SET groupe = 'GESTION_SPONSORING_ADMIN', description = 'Rejeter une demande de sponsoring' WHERE name = 'Managesponsoring.rejectSponsoringRequest';";
        $sql = "UPDATE permissions SET groupe = 'GESTION_SPONSORING_ADMIN', description = 'Voir la liste des demandes de sponsoring rejetée par l\'administrateur' WHERE name = 'Managesponsoring.demandeSponsoringrejetee';";
        $sql = "UPDATE permissions SET groupe = 'GESTION_SPONSORING_ADMIN', description = 'Voir la liste des demandes de sponsoring supprimée par l\'administrateur par les hôtes' WHERE name = 'Managesponsoring.demandeSponsoringsupprimee';";
        $sql = "UPDATE permissions SET groupe = 'GESTION_SPONSORING_ADMIN', description = 'Valider une demande de sponsoring' WHERE name = 'Managesponsoring.validSponsoringRequest';";
        $sql = "UPDATE permissions SET groupe = 'GESTION_SPONSORING_ADMIN', description = 'Invalider une demande de sponsoring' WHERE name = 'Managesponsoring.invalidSponsoringRequest';";


        DB::unprepared($sql);

    }
};
