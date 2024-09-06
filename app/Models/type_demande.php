<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class type_demande extends Model implements Auditable
{
    protected $fillable = [
        'name',
        'icone',
        'is_deleted',
        'is_blocked'
    ];

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($typedemande) {
            $setting = Setting::first();
            $adresseFichier = $setting->adresse_serveur_fichier ?? url('/');

            $typedemande->icone = $adresseFichier . '' . $typedemande->icone;
        });
    }


    use HasFactory;
    use AuditableTrait;
    public function document_type_demande()
    {
        return $this->hasMany(document_type_demande::class);
    }
}
