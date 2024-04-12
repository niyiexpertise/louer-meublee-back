<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousingCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'number',
        'housing_id',
    ];

    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
