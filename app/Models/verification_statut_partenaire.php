<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class verification_statut_partenaire extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'vpdocument_id',
        'status',
        'is_deleted',
        'is_blocked',
    ];
    
    public function verificationDocumentpartenaire()
    {
        return $this->belongsTo(verification_document_partenaire::class, 'vpdocument_id', 'id');
    }
    

}
