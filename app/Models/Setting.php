<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = [
        'pagination_logement_acceuil',
        'condition_tranche_paiement',
        'condition_prix_logement',
        'condition_sponsoring_logement',
        'contact_email',
        'contact_telephone',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'linkedin_url',
        'logo',
        'app_mode',
        'adresse_serveur_fichier',
        'montant_maximum_recharge',
        'montant_minimum_recharge',
        'montant_minimum_retrait',
        'montant_maximum_retrait',
        'montant_minimum_solde_retrait',
        'commission_partenaire',
        'reduction_partenaire_defaut',
        'number_of_reservation_partenaire_defaut',
        'commission_hote_defaut',
        'max_night_number',
        'max_value_reduction',
        'max_number_of_reservation',
        'max_value_promotion',
        'commission_seuil_hote_partenaire',
    ];
}
