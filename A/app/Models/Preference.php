<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preference extends Model
{   protected $fillable = ['name'];
  
    use HasFactory;
    public function housing_preference()
    {
      return $this->hasMany(housing_preference::class);
    }
    public function user_preference()
    {
        return $this->hasMany(user_preference::class);
    }
}
