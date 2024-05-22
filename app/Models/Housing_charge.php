<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Housing_charge extends Model
{
    use HasFactory;
    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }

    public function charge()
    {
        return $this->belongsTo(Charge::class);
    }
}