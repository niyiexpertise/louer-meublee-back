<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentCategory extends Model
{
    use HasFactory;
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}
