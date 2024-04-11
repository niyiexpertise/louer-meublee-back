<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Housing extends Model
{
    use HasFactory;

    public function housing_equipment()
    {
        return $this->hasMany(housing_equipment::class);
                
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function housingType()
    {
        return $this->belongsTo(HousingType::class);
    }

    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class);
    }
    public function ExceptionalStay()
    {
        return $this->belongsTo(ExceptionalStay::class);
    }

    public function housingPrice()
    {
        return $this->hasMany(housing_price::class);
    }

    public function housing_preference()
    {
      return $this->hasMany(housing_preference::class);
    }

    public function housing_accessibility()
    {
        return $this->hasMany(housing_accessibility::class);
        
    }

    public function housing_category_file()
    {
        return $this->hasMany(housing_category_file::class);
    }

    public function reservation()
    {
        return $this->hasMany(Reservation::class);
    }

    public function housing_category_equipment()
    {
        return $this->hasMany(housing_category_equipment::class);
    }
}

