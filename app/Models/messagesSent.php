<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class messagesSent extends Model
{
    use HasFactory;
    protected $table = 'messages_sent';
    protected $primaryKey = 'id';
    protected $fillable = ['avatar_id', 'told', 'fromAvatarId', 'status', 'type', 'subject', 'text', 'timeRead', 'ref', 'fromEntity', 'toEntity', 'activeInbox', 'activeSent', 'indexed'];
}
