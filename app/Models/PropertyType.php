<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class PropertyType extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'name',
        'icone',
        'is_deleted',
        'is_blocked',
        
    ];

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($propertyType) {
            $setting = Setting::first();
            $adresseFichier = $setting->adresse_serveur_fichier ?? url('/');

            $propertyType->icone = $adresseFichier . '' . $propertyType->icone;
        });
    }


    public function housings()
    {
        return $this->hasMany(Housing::class);
    }
}
