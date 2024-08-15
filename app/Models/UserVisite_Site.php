<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class UserVisite_Site extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'user_id', 
        'date_de_visite',
        'heure',  
        'revisite_nb'     
    ];
}
