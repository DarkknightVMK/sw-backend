<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class missions extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'desc',
        'entryTokens',
        'completedDesc',
        'xpLevelTypeId',
        'minXPLevel',
        'panelStyle',
        'creatorAvatarId',
        'creatorUserId',
        'expires',
        'lastActivatedSpaceId',
        'plays',
        'completed'

    ];

}
