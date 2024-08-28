<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class User_language extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'user_id',
        'language_id',
        'icone',
        'is_deleted',
        'is_blocked'

    ];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
