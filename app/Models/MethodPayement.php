<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class MethodPayement extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'name',
        'icone'
    ];

    public function servicePaiement()
    {
        return $this->hasMany(ServicePaiement::class);
    }

    public function servicePaiementactif()
    {
        return $this->hasMany(ServicePaiement::class)->where('is_actif',true);
    }


}
