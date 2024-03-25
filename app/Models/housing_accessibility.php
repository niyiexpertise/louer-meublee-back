<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class housing_accessibility extends Model
{
    use HasFactory;

    public function accessibility()
    {
        return $this->belongsTo(Accessibility::class);
    }

    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
}
