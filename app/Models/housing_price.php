<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class housing_price extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'price_with_cleaning_fees',
        'price_without_cleaning_fees',
        'housing_id',
        'type_stay_id',
        'is_deleted',
        'is_blocked'
    ];
    public function typeStay()
    {
        return $this->belongsTo(TypeStay::class);
    }

    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
}
