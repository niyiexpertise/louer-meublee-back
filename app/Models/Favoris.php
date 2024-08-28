<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Favoris extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'housing_id',
        'user_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }

}
