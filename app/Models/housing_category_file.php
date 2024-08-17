<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Housing_category_file extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;

   protected $fillable = [
        'is_verified',
    ];
    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
