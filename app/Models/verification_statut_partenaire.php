<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class verification_statut_partenaire extends Model
{
    use HasFactory;
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
