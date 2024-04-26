<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portfeuille extends Model
{
    use HasFactory;
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
