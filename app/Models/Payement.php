<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Payement extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;

    protected $fillable = [
        'reservation_id',
        'amount',
        'payment_method',
        'id_transaction',
        'statut',
        'country',
        'is_confirmed',
        'is_canceled'
    ];
}
