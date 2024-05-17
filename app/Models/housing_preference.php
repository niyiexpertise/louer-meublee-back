<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class housing_preference extends Model
{
    use HasFactory;
    protected $fillable = [
        'is_verified',
    ];
    public function preference()
    {
        return $this->belongsTo(Preference::class);
    }

    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
}
