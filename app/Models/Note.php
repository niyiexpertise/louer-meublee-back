<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'reservation_id',
        'criteria_id',
        'content',
        'note',
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

    public function criteria()
    {
        return $this->belongsTo(Criteria::class);
    }
}
