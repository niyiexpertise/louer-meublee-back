<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class verification_document extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'document_id',
        'user_id',
        'path',
        'is_deleted',
        'is_blocked',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verificationStatut()
    {
        return $this->hasOne(verification_statut::class);
    }
    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
