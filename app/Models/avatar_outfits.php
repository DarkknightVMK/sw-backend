<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class avatar_outfits extends Model
{
    protected $primaryKey = 'outfit_id';
    public $incrementing = false;

    protected $fillable = [
        'outfit_id',
        'avatar_id',
        'outfit_config'
    ];

}
