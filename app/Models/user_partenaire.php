<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class user_partenaire extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'user_id',
        'code_promo',
        'commission',
        'number_of_reservation',
        'reduction_traveler'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function userspartenaire()
    {
        return $this->hasMany(user_partenaire::class, 'partenaire_id', 'id');
    }
}
