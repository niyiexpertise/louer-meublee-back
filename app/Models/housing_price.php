<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class housing_price extends Model
{
    use HasFactory;
    public function typeStay()
    {
        return $this->belongsTo(TypeStay::class);
    }

    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
}
