<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class spaceBans extends Model
{
    use HasFactory;

    protected $fillable = [
        'avatar_id',
        'space_id',
        'reason',
        'expires',
    ];
}
