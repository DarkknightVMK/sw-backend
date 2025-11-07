<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class avatarFriends extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    protected $fillable = [
        'avatar_id',
        'friend_id',
        'reciprocal',
    ];
}
