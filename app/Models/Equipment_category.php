<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Equipment_category extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
