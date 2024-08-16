<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reduction extends Model
{
    use HasFactory;
    protected $fillable = [
        'housing_id', 
        'night_number',
        'value',
        'is_encours',
        'is_deleted'
    ];

    protected $hidden = [
        'date_debut',
        'date_fin',
    ];

    
    public function housing()
    {
        return $this->belongsTo(Housing::class, 'housing_id'); 
    }
}
