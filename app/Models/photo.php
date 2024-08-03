<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class photo extends Model
{
    protected $fillable = [
        'is_couverture'
    ];
    use HasFactory;
    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
}
