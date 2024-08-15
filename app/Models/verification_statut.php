<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class verification_statut extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'verification_document_id',
        'status',
        'is_deleted',
        'is_blocked',
    ];
    
    public function verificationDocument()
    {
        return $this->belongsTo(verification_document::class);
    }
}
