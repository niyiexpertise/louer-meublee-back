<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class TypeStay extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'name',
        'is_deleted',
        'is_blocked',
        'icone'
    ];

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($typeStay) {
            $setting = Setting::first();
            $adresseFichier = $setting->adresse_serveur_fichier ?? url('/');

            $typeStay->icone = $adresseFichier . '' . $typeStay->icone;
        });
    }

    public function housing_price()
    {
        return $this->hasMany(housing_price::class);
    }
}
