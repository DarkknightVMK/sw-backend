<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class catalogCategories extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'active',
        'isManuallyControlled',
        'isControlledByPopularity',
        'type',
        'iconUrl',
        'parentId',
        'parentName',
        'children',
        'orderIndex',
        'sortsByOrderIndex',
        'sortsAlphabetically',
        'hasSeparator'

    ];
    public $timestamps = false;

}
