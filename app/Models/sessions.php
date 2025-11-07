<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sessions extends Model
{
    
    use HasFactory;
    public $primaryKey = 'SWSID';
    // protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'SWSID',
        'user_id',
        'ip_address',
        'user_agent',
        'expires_at',
        ];
}
