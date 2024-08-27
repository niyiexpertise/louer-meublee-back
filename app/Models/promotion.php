<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class promotion extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'housing_id',
        'number_of_reservation',
        'value',
        'is_encours',
        'is_blocked',
        'is_actif',
        'is_deleted',
        'date_debut',
        'date_fin',
    ];


    public function housing()
    {
        return $this->belongsTo(Housing::class, 'housing_id');
    }
}
