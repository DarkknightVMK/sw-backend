<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class catalogSpaceEntry extends Model
{
    use HasFactory;
    protected $fillable = [
        'orderIndex',
        'spaceId',
        'categoryId',
        'categoryName',
        'parentCategoryName',
        'parentCategoryId',
        'spaceBlocked',
        'repeatTime',
        'active',
        'startDate',
        'endDate'
        // 'children',
        // 'orderIndex',
        // 'sortsByOrderIndex',
        // 'sortsAlphabetically',
        // 'hasSeparator'

    ];
    public $timestamps = false;

}

