<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Housing extends Model
{
    use HasFactory;
    protected $fillable = [
        'housing_type_id',
        'property_type_id',
        'user_id',
        'name',
        'description',
        'number_of_bed',
        'number_of_traveller',
        'sit_geo_lat',
        'sit_geo_lng',
        'country',
        'address',
        'city',
        'department',
        'is_camera',
        'is_accepted_animal',
        'is_animal_exist',
        'is_disponible',
        'interior_regulation',
        'telephone',
        'code_pays',
        'status',
        'arrived_independently',
        'cleaning_fees',
        'is_instant_reservation',
        'maximum_duration',
        'minimum_duration',
        'time_before_reservation',
        'cancelation_condition',
        'departure_instruction',
        'is_deleted',
        'is_blocked'
    ];
    

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


    public function reservation()
    {
        return $this->hasMany(Reservation::class);
    }

    public function photos()
    {
        return $this->hasMany(photo::class);
    }

    public function preferences()
    {
        return $this->hasMany(housing_preference::class);
    }

    public function reductions()
    {
        return $this->hasMany(reduction::class);
    }

    public function promotions()
    {
        return $this->hasMany(promotion::class);
    }

    public function categories()
    {
        return $this->hasMany(HousingCategory::class);
    }

}

