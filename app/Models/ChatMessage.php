<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class ChatMessage extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    public function file()
    {
        return $this->hasMany(ChatFile::class,'referencecode','filecode');
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
