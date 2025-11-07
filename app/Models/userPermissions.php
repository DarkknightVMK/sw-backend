<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class userPermissions extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'desc',
        'sortOrder',
        'catId'
    ];
    public function userGroup()
    {
        return $this->belongsTo(userGroups::class,'permissionId');
    }
}
