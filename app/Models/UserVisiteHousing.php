<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVisiteHousing extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 
        'housing_id', 
        'date_de_visite',
        'heure',  
        'revisite_nb'      
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
