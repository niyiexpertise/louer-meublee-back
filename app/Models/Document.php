<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{protected $fillable = ['name','is_actif'];
    use HasFactory;
    
    public function verificationDocuments()
    {
        return $this->hasMany(Verification_document::class);
    }

}
