<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class MethodPayement extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'name',
        'icone'
    ];

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($methodPayement) {
            $setting = Setting::first();
            $adresseFichier = $setting->adresse_serveur_fichier ?? url('/');

            $methodPayement->icone = $adresseFichier . '' . $methodPayement->icone;
        });
    }
}
