<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class MoyenPayement extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'user_id','method_payement_id','valeur_method_payement'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function methodPayement()
    {
        return $this->belongsTo(MethodPayement::class);
    }
}
