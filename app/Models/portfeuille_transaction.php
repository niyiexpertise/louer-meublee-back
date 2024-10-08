<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Portfeuille_transaction extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'portfeuille_id',
        'amount',
        'reservation_id',
        'payment_method',
        'debit',
        'credit',
        'motif',
        'id_transaction',
        'valeur_commission',
        'montant_commission',
        'montant_restant',
        'solde_commission',
        'solde_restant',
        'solde_total',
        'valeur_commission_partenaire',
        'montant_commission_partenaire',
        'solde_commission_partenaire',
        'partenaire_id',
        'valeur_commission_admin',
        'montant_commission_admin',
        'new_solde_admin',
        'sponsoring_id',
        'retrait_id',
        'solde_credit',
        'solde_debit',
        'housing_sponsoring_id',
        'operation_type',
    ];

    protected $hidden = [
        'solde_restant',
        'solde_total',
    ];
    public function portfeuille()
    {
        return $this->belongsTo(Portfeuille::class);
    }
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }


}
