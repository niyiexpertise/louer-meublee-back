<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Document extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;

    protected $fillable = [
    'name',
    'is_actif',
    'icone',
    'is_deleted',
    'is_blocked'
    ];
    
    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($document) {
            $setting = Setting::first();
            $adresseFichier = $setting->adresse_serveur_fichier ?? url('/'); 

            $document->icone = $adresseFichier . '' . $document->icone;
        });
    }

    public function verificationDocuments()
    {
        return $this->hasMany(verification_document::class);
    }

    public function document_type_demande()
    {
        return $this->hasMany(document_type_demande::class);
    }

}
