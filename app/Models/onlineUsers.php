<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;


class onlineUsers extends Model
{
    use HasFactory;
    protected $fillable = [
        'space_id',
        'avatar_id',
        'online',
        'x',
        'y',
        'z',
        'local'

    ];
    protected $table = 'onlineUsers';

}
