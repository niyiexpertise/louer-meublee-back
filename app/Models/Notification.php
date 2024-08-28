<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Notification extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'name',
        'user_id',
        'object',
        'is_read'
    ];

    public function user()
    {
      return $this->belongsTo(User::class);
    }
    public function notification()
    {
        return $this->hasMany(Review::class);
    }
}
