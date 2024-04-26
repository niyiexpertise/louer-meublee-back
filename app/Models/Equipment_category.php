<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment_category extends Model
{
    use HasFactory;


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
