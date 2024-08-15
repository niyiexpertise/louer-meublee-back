<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Commission extends Model implements Auditable
{
    protected $fillable = ['valeur'];
    use HasFactory;
    use AuditableTrait;
}
