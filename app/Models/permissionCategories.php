<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class permissionCategories extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'product',
        'orderIndex'
    ];
}
