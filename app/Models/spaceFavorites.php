<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class spaceFavorites extends Model
{
    use HasFactory;
    public $primaryKey = 'space_id';
    // public $incrementing = false;
    protected $fillable = 
    [        
        'space_id',
        'avatar_id', 
    ];
    // public $timestamps = false;

    public function avatar() 
    {
        return $this->belongsTo(Avatars::class, 'avatar_id');
    }
    public function space() 
    {
        return $this->belongsTo(avatarSpaces::class);
    }
}
