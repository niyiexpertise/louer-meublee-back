<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Sponsoring extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;

    protected $fillable = [
        'duree',
        'prix',
        'description',
        'is_deleted',
        'is_actif'
    ];

    public function housing_sponsoring()
    {
        return $this->hasMany(HousingSponsoring::class);
    }
}
