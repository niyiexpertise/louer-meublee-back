<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class photo extends Model implements Auditable
{
    protected $fillable = [
        'housing_id',
        'path',
        'extension',
        'is_couverture',
        'is_deleted',
        'is_blocked',
        'is_verified'

    ];

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($photo) {
            $setting = Setting::first();
            $adresseFichier = $setting->adresse_serveur_fichier ?? url('/');

            $photo->path = $adresseFichier . '' . $photo->path;
        });
    }
    use HasFactory;
    use AuditableTrait;
    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
}
