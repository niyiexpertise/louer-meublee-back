<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExceptionalStay extends Model
{
    use HasFactory;
    public function housing()
    {
        return $this->hasMany(Housing::class);
    }
}
