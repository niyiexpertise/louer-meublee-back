<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_preference extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'preference_id',
    ];
    public function preference()
    {
        return $this->belongsTo(Preference::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
