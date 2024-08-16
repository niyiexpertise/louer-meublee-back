<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class promotion extends Model
{
    use HasFactory;
    protected $fillable = [
        'housing_id', 
        'number_of_reservation',
        'value',
        'is_encours', 
        'is_deleted',
        'date_debut',
        'date_fin',
    ];

    
    public function housing()
    {
        return $this->belongsTo(Housing::class, 'housing_id'); 
    }
}
