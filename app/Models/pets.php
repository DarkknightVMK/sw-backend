<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class pets extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'motto',
        'configString',
        'memoryString',
        'active',
        'ownerId',
        'snapshotUrl',
        'headUrl',
        'thumbUrl',
        'snapshotPostfix',
        'headPostfix',
        'thumbPostfix',
        'created_at',
        'updated_at',
        'stringId',
    ];
    //table name
    protected $primaryKey = 'stringId';
    public $incrementing = false;

    protected $table = 'avatar_pets';

}
