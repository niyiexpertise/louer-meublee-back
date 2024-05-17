<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'is_verified','name'
    ];

    public function equipment_category()
    {
      return $this->hasMany(equipment_category::class);
    }
    public function housing_category_file()
    {
        return $this->hasMany(housing_category_file::class);
    }

    

}
