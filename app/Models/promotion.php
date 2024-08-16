<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;
    protected $fillable = [
        'value',
<<<<<<< HEAD
        'number_of_reservation'
    ];
=======
        'is_encours', 
        'is_deleted',
        'date_debut',
        'date_fin',
    ];

    
    public function housing()
    {
        return $this->belongsTo(Housing::class, 'housing_id'); 
    }
>>>>>>> 47466451b179ebc8658881198e8522a898727d72
}
