<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class s2wTiming extends Model
{
    use HasFactory;
    protected $fillable = [
        'duration'
    ];
    protected $table = 's2w_timings';
    
}
