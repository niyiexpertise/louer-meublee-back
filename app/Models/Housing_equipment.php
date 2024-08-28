<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Housing_equipment extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'equipment_id',
        'category_id',
        'housing_id',
        'is_verified',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
}
