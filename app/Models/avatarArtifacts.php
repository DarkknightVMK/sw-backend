<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class avatarArtifacts extends Model
{
    use HasFactory;
    protected $fillable = [
        'artifact_key',
        'artifact_visible',
        'artifact_expires',
        'avatar_id',
    ];
    public $timestamps = false;


}
