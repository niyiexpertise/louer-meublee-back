<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class User_preference extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'user_id',
        'preference_id',
        'icone',
        'is_deleted',
        'is_blocked'
    ];

    
    public function preference()
    {
        return $this->belongsTo(Preference::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
