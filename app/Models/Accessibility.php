<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accessibility extends Model
{
    use HasFactory;
    public function category()
    {
        return $this->belongsTo(Category::class);
                 
                    
    }
    public function housing_accessibility()
    {
        return $this->hasMany(housing_accessibility::class);
        
    }
}
