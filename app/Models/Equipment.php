<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'description', 'is_deleted', 'is_blocked', 'icone',
    ];

    public function equipment_category()
    {
        return $this->hasMany(EquipmentCategory::class);           
                    
    }
    public function housing_equipment()
    {
        return $this->hasMany(housing_equipment::class);
        
    }
    public function housing_category_equipment()
    {
        return $this->hasMany(housing_category_equipment::class);
    }


}
