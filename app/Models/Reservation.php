<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Reservation extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'user_id',
        'housing_id',
        'date_of_reservation',
        'date_of_starting',
        'date_of_end',
        'number_of_adult',
        'number_of_child',
        'number_of_domestical_animal',
        'number_of_baby',
        'message_to_hote',
        'icone',
        'motif_rejet_hote',
        'motif_rejet_traveler',
        'code_pays',
        'telephone_traveler',
        'heure_arrivee_max',
        'heure_arrivee_min',
        'is_tranche_paiement',
        'montant_total',
        'valeur_payee',
        'is_confirmed_hote',
        'is_integration',
        'is_rejected_traveler',
        'is_rejected_hote',
        'is_deleted',
        'is_blocked',
        'photo',
        'valeur_reduction_hote',
        'valeur_promotion_hote',
        'valeur_reduction_code_promo',
        'valeur_reduction_staturp',
        'montant_charge',
        'montant_housing',
        'montant_a_paye',
        'statut',
        'is_accepted'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
    public function review()
    {
        return $this->hasOne(Review_reservation::class);
    }

    public function portfeuille_transaction()
    {
      return $this->hasMany(Portfeuille_transaction::class);
    }
}
