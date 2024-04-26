<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }

    public function portfeuille_transaction()
    {
      return $this->hasMany(portfeuille_transaction::class);
    }

}
