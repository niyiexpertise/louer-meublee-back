<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Equipment extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'name',
        'icone',
        'is_deleted',
        'is_blocked',
        'is_verified'
    ];

    public function equipment_category()
    {
        return $this->hasMany(equipment_category::class);

    }
    public function housing_equipment()
    {
        return $this->hasMany(housing_equipment::class);

    }


}
