<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review_reservation extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'reservation_id',
        'content',
        'is_deleted',
        'is_blocked',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
    
}
