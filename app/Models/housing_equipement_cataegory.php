<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class housing_equipement_cataegory extends Model
{
    use HasFactory;
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
