<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Housing_category_file extends Model
{
    use HasFactory;

   protected $fillable = [
        'is_verified',
    ];
    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
