<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class avatarMissions extends Model
{
    use HasFactory;
    protected $fillable = [
        'avatar_id',
        'pickupSpaceId',
        'activated',
        'isTesting',
        'pickupLocation',
        'missionId',
        'completed',
        'missionChainId'

    ];
    // protected $table = 'avatarMissions';
    protected $primaryKey = 'missionId';

    protected $table = 'avatar_missions';

}