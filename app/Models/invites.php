<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class invites extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    protected $fillable = 
    [
        'email',
        'code',
        'invitee',
    ];
    public $timestamps = false;


}
