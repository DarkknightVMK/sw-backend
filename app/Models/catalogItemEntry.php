<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class catalogItemEntry extends Model
{
    use HasFactory;
    
    protected $fillable =
    [
        'orderIndex',
        'modelId',
        'categoryId',
        'categoryName',
        'parentCategoryName',
        'parentCategoryId',
        'discounted',
        'repeatTime',
        'active',
        'startDate',
        'endDate',
        'shareDiscount',
        'type',
        'quantity',
        'salePriceGold',
        'cp',
        'icon'
    ];
    public $timestamps = false;

}
