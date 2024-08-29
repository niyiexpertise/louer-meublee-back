<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Review extends Model implements Auditable 
{
  use HasFactory;
  use AuditableTrait;

    protected $fillable = [
      'content',
      'icone',
      'is_blocked',
      'is_deleted',
      'user_id'
  ];

  protected  static  $auditInclude = [
    'content',
      'is_deleted',
      'user_id'
];

  
    public function user()
    {
      return $this->belongsTo(User::class);
    }
}
