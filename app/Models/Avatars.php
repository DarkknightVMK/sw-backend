<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;
class Avatars extends Model
{
    use HasFactory;
    protected $primaryKey = 'avatar_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'avatar_id',
        'owner_id',
        'firstName',
        'lastName',
        'fullName',
        'gender',
        'nameInstance',
        'takePet',
        'dateCreated',
        'config',
        'headPostfix',
        'snapshotPostfix',
        'thumbUrl',
        'snapUrl',
        'bonus'

    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function aitems() {
        return $this->hasMany(avatarItems::class, "avatar_id");
    }
    public function aspaces() {
        return $this->hasMany(avatarSpaces::class);
    }
    public function favSpaces() {
        return $this->hasMany(spaceFavorites::class);
    }

    public function xp() {
        return $this->hasMany(avatar_xp::class, 'avatar_id', 'avatar_id');
    }

    public function pet() {
        return $this->hasOne(pets::class, 'ownerId', 'avatar_id');
    }
}
