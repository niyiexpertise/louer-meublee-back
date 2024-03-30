<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    public function equipment_category()
    {
      return $this->hasMany(EquipmentCategory::class);
    }
    public function accessibility()
    {
        return $this->hasMany(Accessibility::class);
    }
    public function housing_category_file()
    {
        return $this->hasMany(housing_category_file::class);
    }

    public function housing_category_equipment()
    {
        return $this->hasMany(housing_category_equipment::class);
    }
    

}
