<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class verification_statut extends Model
{
    use HasFactory;
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
