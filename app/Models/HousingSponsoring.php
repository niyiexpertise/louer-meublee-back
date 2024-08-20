<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousingSponsoring extends Model
{
    use HasFactory;

    public function sponsoring()
    {
        return $this->belongsTo(Sponsoring::class);
    }

    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
}
