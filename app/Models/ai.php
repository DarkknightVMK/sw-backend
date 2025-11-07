<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class ai extends Model
{
    use HasFactory;
    protected $fillable = [
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
        'firstName',
        'lastName',
        'motto'
    ];
    // //table name
    // protected $primaryKey = 'stringId';
    // public $incrementing = false;

    protected $table = 'ai';

}