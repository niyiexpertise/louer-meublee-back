<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePaiement extends Model
{
    use HasFactory;
    protected $fillable = [
        'type', 'method_paiement_id', 'public_key', 'private_key', 'secret_key', 
        'is_actif', 'description_type', 'description_service', 'fees', 'date_activation'
    ];

    protected $hidden = [
        'private_key',
        'secret_key'
    ];

    public function methodPaiement()
    {
        return $this->belongsTo(MethodPayement::class);
    }
}
