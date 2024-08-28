<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Category extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'is_verified',
        'name',
        'icone',
        'is_deleted',
        'is_blocked'
    ];

    public function equipment_category()
    {
      return $this->hasMany(equipment_category::class);
    }
    public function housing_category_file()
    {
        return $this->hasMany(housing_category_file::class);
    }

    

}
