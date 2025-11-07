<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;


class PasswordReset extends Model
{
    use HasFactory;
    protected $fillable = [
        'email',
        'token',
        'created_at'
    ];
    protected $table = 'password_resets';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}