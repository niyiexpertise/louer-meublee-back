<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class TypeStay extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = ['name'];
    public function housing_price()
    {
        return $this->hasMany(housing_price::class);
    }
}
