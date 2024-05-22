<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_partenaire extends Model
{
    use HasFactory;
    protected $fillable = ['code_promo','commission','number_of_reservation','reduction_traveler'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function userspartenaire()
    {
        return $this->hasMany(user_partenaire::class, 'partenaire_id', 'id');
    }
}
