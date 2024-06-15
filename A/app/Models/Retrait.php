<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retrait extends Model
{
    use HasFactory;
    use HasFactory;
    protected $fillable = [
        'payment_method',
        'libelle',
        'user_id',
        'user_role',
        'montant_reel',
        'montant_valid',
        'statut',
        'identifiant_payement_method',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
