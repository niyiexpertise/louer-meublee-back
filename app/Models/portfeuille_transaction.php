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
        'solde_total',
        'solde_commission',
        'solde_restant',
        'valeur_commission_partenaire',
        'montant_commission_partenaire',
        'solde_commission_partenaire',
        'partenaire_id',
        'valeur_commission_admin',
        'montant_commission_admin',
        'new_solde_admin',
        'created_at',
        'updated_at',
        'solde_credit',
        'solde_debit',
    ];
    
    protected $hidden = [
        'solde_restant',
        'montant_restant',
        'solde_credit',
        'solde_debit',
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
