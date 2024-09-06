<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class File extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'path',
        'is_deleted',
        'is_blocked'
    ];

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($file) {
            $setting = Setting::first();
            $adresseFichier = $setting->adresse_serveur_fichier ?? url('/'); 

            $file->path = $adresseFichier . '' . $file->path;
        });
    }
    public function housing_category_file()
    {
        return $this->hasMany(housing_category_file::class);
    }
}
