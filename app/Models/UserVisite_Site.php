<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVisite_Site extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 
        'date_de_visite',
        'heure',  
        'revisite_nb'     
    ];
}
