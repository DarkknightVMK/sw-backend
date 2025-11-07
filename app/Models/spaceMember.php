<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class spaceMember extends Model
{
    use HasFactory;
    protected $fillable = [
        'space_id',
        'spacerole_id',
        'avatar_id',
    ];
}
