<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Right extends Model
{
    use HasFactory;
    public function user_right()
    {
        return $this->hasMany(user_right::class);
    }
}
