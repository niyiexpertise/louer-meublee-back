<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Charge extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'name',
        'icone',
        'is_blocked',
        'is_deleted'
    ];

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($charge) {
            $setting = Setting::first();
            $adresseFichier = $setting->adresse_serveur_fichier ?? url('/'); 

            $charge->icone = $adresseFichier . '' . $charge->icone;
        });
    }
    public function housing_charge(){
        return $this->hasMany(Housing_charge::class);
    }
}
