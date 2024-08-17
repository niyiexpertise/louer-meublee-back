<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class HousingType extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    
    protected $fillable = ['name', 'description'];
    public function housings()
    {
        return $this->hasMany(Housing::class);
    }
}
