<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Criteria extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'name',
        'icone',
        'is_deleted',
        'is_blocked'
    ];
    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($critere) {
            $setting = Setting::first();
            $adresseFichier = $setting->adresse_serveur_fichier ?? url('/'); 

            $critere->icone = $adresseFichier . '' . $critere->icone;
        });
    }

    public function note()
    {
        return $this->hasMany(Note::class);
    }
}
