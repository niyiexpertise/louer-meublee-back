<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class photo extends Model implements Auditable
{
    protected $fillable = [
        'is_couverture'
    ];
    use HasFactory;
    use AuditableTrait;
    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
}
