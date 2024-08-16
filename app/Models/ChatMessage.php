<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;
    public function file()
    {
        return $this->hasMany(ChatFile::class,'referencecode','filecode');
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
