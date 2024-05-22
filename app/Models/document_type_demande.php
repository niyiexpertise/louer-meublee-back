<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class document_type_demande extends Model
{
    use HasFactory;
    public function document()
    {
        return $this->belongsTo(Document::class);
    }
    public function type_demande()
    {
        return $this->belongsTo(type_demande::class);
    }
}
