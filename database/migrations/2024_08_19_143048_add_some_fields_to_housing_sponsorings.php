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
        // Ajout du trigger avant mise à jour
        DB::unprepared('
            CREATE TRIGGER check_status_before_update
            BEFORE UPDATE ON housing_sponsorings
            FOR EACH ROW
            BEGIN
                -- Vérifie si le statut est "non_payee"
                IF OLD.statut = "non_payee" THEN
                    IF NEW.is_actif = 1 OR NEW.is_rejected = 1 THEN
                        SIGNAL SQLSTATE "45000"
                        SET MESSAGE_TEXT = "Cannot set is_actif or is_rejected to 1 if status is non_payee.";
                    END IF;
                END IF;

                -- Vérifie si le statut est "payee"
                IF OLD.statut = "payee" THEN
                    IF NEW.is_actif = 1 AND NEW.is_rejected = 1 THEN
                        SIGNAL SQLSTATE "45000"
                        SET MESSAGE_TEXT = "Cannot have both is_actif and is_rejected set to 1 if status is payee.";
                    END IF;
                END IF;
            END
        ');

        // Ajout du trigger avant insertion
        DB::unprepared('
            CREATE TRIGGER check_status_before_insert
            BEFORE INSERT ON housing_sponsorings
            FOR EACH ROW
            BEGIN
                -- Vérifie si le statut est "non_payee"
                IF NEW.statut = "non_payee" THEN
                    IF NEW.is_actif = 1 OR NEW.is_rejected = 1 THEN
                        SIGNAL SQLSTATE "45000"
                        SET MESSAGE_TEXT = "Cannot set is_actif or is_rejected to 1 if status is non_payee.";
                    END IF;
                END IF;

                -- Vérifie si le statut est "payee"
                IF NEW.statut = "payee" THEN
                    IF NEW.is_actif = 1 AND NEW.is_rejected = 1 THEN
                        SIGNAL SQLSTATE "45000"
                        SET MESSAGE_TEXT = "Cannot have both is_actif and is_rejected set to 1 if status is payee.";
                    END IF;
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression des triggers
        DB::unprepared('DROP TRIGGER IF EXISTS check_status_before_update');
        DB::unprepared('DROP TRIGGER IF EXISTS check_status_before_insert');
    }
};
