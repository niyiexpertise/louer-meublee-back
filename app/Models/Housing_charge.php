<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Housing_charge extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'housing_id',
        'charge_id',
        'is_mycharge',
        'valeur'
    ];
    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }

    public function charge()
    {
        return $this->belongsTo(Charge::class);
    }
}
