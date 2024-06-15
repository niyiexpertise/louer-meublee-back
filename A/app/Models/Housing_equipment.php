<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Housing_equipment extends Model
{
    use HasFactory;
    protected $fillable = [
        'is_verified',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
}
