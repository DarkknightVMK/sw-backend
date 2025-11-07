<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class avatarBlocks extends Model
{
    use HasFactory;
    protected $fillable = ['avatar_id', 'block_id'];
    public function avatar() 
    {
        return $this->belongsTo(Avatars::class, 'avatar_id');
    }
}
