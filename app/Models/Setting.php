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
        'adresse_serveur_fichier'
    ];
}
