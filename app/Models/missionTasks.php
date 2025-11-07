<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;


class missionTasks extends Model
{
    use HasFactory;
    protected $fillable = [
        'missionId',
        'title',
        'desc',
        'completedDesc',
        'script',
        'toTaskId',
        'active',
        'timeLeft',
        'orderNumber'
    ];
        
}
