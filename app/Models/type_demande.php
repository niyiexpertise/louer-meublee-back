<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class type_demande extends Model
{
    protected $fillable = [
        'name',
    ];
    use HasFactory;
    public function document_type_demande()
    {
        return $this->hasMany(document_type_demande::class);
    }
}
