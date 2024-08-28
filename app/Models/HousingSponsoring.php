<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;


class HousingSponsoring extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'housing_id',
        'sponsoring_id',
        'date_debut',
        'date_fin',
        'is_deleted',
        'is_actif',
        'is_rejected',
        'motif',
        'nombre',
        'duree',
        'prix',
        'description',
        'statut'
    ];

    public function sponsoring()
    {
        return $this->belongsTo(Sponsoring::class);
    }

    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
}
