<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Portfeuille extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;

    
    protected $fillable = [
        'user_id', 
        'solde',    
        'is_blocked', 
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function portfeuille_transaction()
    {
      return $this->hasMany(Portfeuille_transaction::class);
    }
}
