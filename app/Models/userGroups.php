<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class userGroups extends Model
{
    use HasFactory;
    protected $fillable = [
        'uid',
        'name',
        'description',
        'active',
        'indexed',
        'tags',
        'accessControl',
        'type',
        'permissionId'
    ];
    public function user()
    {
        return $this->belongsTo(Users::class);
    }
    public function userPermission()

    {
        return $this->hasMany(userPermissions::class,'permissionId');
    }

}
