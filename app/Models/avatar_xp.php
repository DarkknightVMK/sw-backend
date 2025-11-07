<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class avatar_xp extends Model
{
    use HasFactory;
    protected $fillable = [
        'xp',
        'level',
        'xpleveltype',
        'avatar_id',
        'cap',
        'updates',
        'xpGiven'
    //     'configString',
    //     'memoryString',
    //     'active',
    //     'ownerId',
    //     'snapshotUrl',
    //     'headUrl',
    //     'thumbUrl',
    //     'snapshotPostfix',
    //     'headPostfix',
    //     'thumbPostfix',
    //     'created_at',
    //     'updated_at',
    //     'stringId',
    ];
    // //table name
    // protected $primaryKey = 'stringId';
    // public $incrementing = false;

    protected $table = 'xp';

}