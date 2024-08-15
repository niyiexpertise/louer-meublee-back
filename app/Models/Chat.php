<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Chat extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;

    protected $fillable = [
        'is_read'
    ];

    public function chat_message(){
        return $this->hasMany(ChatMessage::class);
    }
}
