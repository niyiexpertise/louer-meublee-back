<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class housing_equipment extends Model
{
    use HasFactory;
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
}
