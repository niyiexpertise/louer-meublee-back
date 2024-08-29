<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Retrait extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'payment_method',
        'libelle',
        'user_id',
        'user_role',
        'montant_reel',
        'montant_valid',
        'statut',
        'identifiant_payement_method',
        'is_reject',
        'motif'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
