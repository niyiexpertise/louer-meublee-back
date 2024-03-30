<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeStay extends Model
{
    use HasFactory;
    protected $fillable = ['name'];
    public function housing_price()
    {
        return $this->hasMany(housing_price::class);
    }
}
