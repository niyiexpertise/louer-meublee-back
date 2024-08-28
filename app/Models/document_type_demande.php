<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class document_type_demande extends Model  implements Auditable
{
    use HasFactory;
    use AuditableTrait;

    protected $fillable = [
        'document_id',
        'type_demande_id'
        ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
    public function type_demande()
    {
        return $this->belongsTo(type_demande::class);
    }
}
