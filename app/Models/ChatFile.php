<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class ChatFile extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'location',
        'referencecode',
        'is_deleted'
    ];

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($chatFile) {
            $setting = Setting::first();
            $adresseFichier = $setting->adresse_serveur_fichier ?? url('/'); 

            $chatFile->location = $adresseFichier . '' . $chatFile->location;
        });
    }
}
