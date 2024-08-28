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
        'sent_to',
        'is_read',
        'sent_by',
        'last_message',
        'model_type_concerned',
        'model_id',
        'is_deleted'
    ];

    public function chat_message(){
        return $this->hasMany(ChatMessage::class);
    }
}
