<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class File extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    public function housing_category_file()
    {
        return $this->hasMany(housing_category_file::class);
    }
}
