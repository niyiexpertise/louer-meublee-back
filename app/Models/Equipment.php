<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'is_deleted', 'is_blocked', 'icone','is_verified'
    ];

    public function equipment_category()
    {
        return $this->hasMany(equipment_category::class);           
                    
    }
    public function housing_equipment()
    {
        return $this->hasMany(housing_equipment::class);
        
    }


}
