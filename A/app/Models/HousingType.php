<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousingType extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'description'];
    public function housings()
    {
        return $this->hasMany(Housing::class);
    }
}
