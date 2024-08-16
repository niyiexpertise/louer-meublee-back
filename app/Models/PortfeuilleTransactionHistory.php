<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfeuilleTransactionHistory extends Model
{
    protected $table = 'portfeuille_transaction_history';

    protected $fillable = [
        'transaction_id',
        'column_name',
        'old_value',
        'new_value',
        'modified_by',
        'modified_at',
        'motif',
    ];

    public $timestamps = true;

 
    public function transaction()
    {
        return $this->belongsTo(portfeuille_transaction::class, 'transaction_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
