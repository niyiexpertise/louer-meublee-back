<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Preference extends Model implements Auditable
{   protected $fillable = [
    'name',
    'icone',
    'is_verified',
    'is_deleted',
    'is_blocked' 
];

protected static function boot()
{
    parent::boot();

    static::retrieved(function ($preference) {
        $setting = Setting::first();
        $adresseFichier = $setting->adresse_serveur_fichier ?? url('/');

        $preference->icone = $adresseFichier . '' . $preference->icone;
    });
}
  
    use HasFactory;
    use AuditableTrait;
    public function housing_preference()
    {
      return $this->hasMany(housing_preference::class);
    }
    public function user_preference()
    {
        return $this->hasMany(user_preference::class);
    }
}
