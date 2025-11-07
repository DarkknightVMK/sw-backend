<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class s2wCat extends Model
{
    use HasFactory;
    protected $table = 's2w_categories';
    // protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'name',
        'background',
        'weighting',
        'count',
        'order',
        'isDeluxe',
        'active'
    ];

}
