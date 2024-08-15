<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class reduction extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'housing_id', 
        'night_number',
        'value',
        'is_encours',
        'is_deleted'
    ];

    protected $hidden = [
        'date_debut',
        'date_fin',
    ];

    
    public function housing()
    {
        return $this->belongsTo(Housing::class, 'housing_id'); 
    }
}
