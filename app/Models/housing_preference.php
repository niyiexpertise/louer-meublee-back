<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class housing_preference extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'housing_id',
        'preference_id',
        'is_deleted',
        'is_blocked',
        'is_verified',
    ];
    public function preference()
    {
        return $this->belongsTo(Preference::class);
    }

    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
}
