<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Housing extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
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
        'is_instant_reservation',
        'maximum_duration',
        'minimum_duration',
        'time_before_reservation',
        'cancelation_condition',
        'departure_instruction',
        'is_accept_arm',
        'is_accept_chill',
        'is_accept_noise',
        'is_updated',
        'is_deleted',
        'is_blocked',
        'is_finished',
        'price',
        'surface',
        'is_destroy',
        'is_actif',
        'is_accept_alccol',
        'delai_partiel_remboursement',
        'delai_integral_remboursement',
        'valeur_integral_remboursement',
        'valeur_partiel_remboursement',
        'is_accept_annulation',
        'step',
        'interior_regulation_pdf'
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


    public function housingPrice()
    {
        return $this->hasMany(housing_price::class);
    }

    public function housing_preference()
    {
      return $this->hasMany(housing_preference::class);
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

    public function housingEquipments()
    {
        return $this->hasMany(Housing_equipment::class);
    }

    public function housingCategoryFiles()
    {
        return $this->hasMany(Housing_category_file::class);
    }

    public function housing_charge(){
        return $this->hasMany(Housing_charge::class);
    }
    public function visites()
    {
        return $this->hasMany(UserVisiteHousing::class);
    }
    

}

