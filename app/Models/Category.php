<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Category extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'is_verified',
        'name',
        'icone',
        'is_deleted',
        'is_blocked'
    ];

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($category) {
            $setting = Setting::first();
            $adresseFichier = $setting->adresse_serveur_fichier ?? url('/'); 

            $category->icone = $adresseFichier . '' . $category->icone;
        });
    }

    public function equipment_category()
    {
      return $this->hasMany(equipment_category::class);
    }
    public function housing_category_file()
    {
        return $this->hasMany(housing_category_file::class);
    }

    

}
