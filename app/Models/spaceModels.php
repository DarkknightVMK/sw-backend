<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class spaceModels extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_details',
        'model_source',
        'model_desc',
        'model_id',
        'model_price'
        ];
    public $timestamps = false;

}
