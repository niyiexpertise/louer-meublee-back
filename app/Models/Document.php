<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Document extends Model implements Auditable
{protected $fillable = ['name','is_actif'];
    use HasFactory;
    use AuditableTrait;
    
    public function verificationDocuments()
    {
        return $this->hasMany(verification_document::class);
    }

    public function document_type_demande()
    {
        return $this->hasMany(document_type_demande::class);
    }

}
