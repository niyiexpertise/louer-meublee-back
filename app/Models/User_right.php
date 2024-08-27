<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class User_right extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'user_id',
        'right_id',
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function right()
    {
        return $this->belongsTo(Right::class);
    }
}
