<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portfeuille_transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'portfeuille_id', 
        'amount',    
        'reservation_id',
        'payment_method',
        'debit',
        'credit',
        'motif',
        'id_transaction'
    ];

    public function portfeuille()
    {
        return $this->belongsTo(Portfeuille::class);
    }
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }


}
