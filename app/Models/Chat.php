<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_read'
    ];

    public function chat_message(){
        return $this->hasMany(ChatMessage::class);
    }
}
