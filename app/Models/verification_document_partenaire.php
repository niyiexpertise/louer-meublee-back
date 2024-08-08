<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class verification_document_partenaire extends Model
{
    use HasFactory;
    protected $fillable = [
        'document_id',
        'user_id',
        'path',
        'code_promo',
        'is_deleted',
        'is_blocked',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

        public function verificationStatutpartenaire()
    {
        return $this->hasOne(verification_statut_partenaire::class, 'vpdocument_id', 'id');
    }


    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}