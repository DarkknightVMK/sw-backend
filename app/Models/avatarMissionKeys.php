<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class avatarMissionKeys extends Model
{
    use HasFactory;
    protected $fillable = [
        'missionkey_key',
        'missionkey_visible',
        'missionkey_mission_id',
        'missionkey_expires',
        'avatar_id',
    ];
    public $timestamps = false;
}
