<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class s2wPrize extends Model
{
    use HasFactory;
    protected $table = 's2w_prizes';
    protected $fillable = [
        'tokens',
        'gold',
        'modelCount',
        'type',
        'isInPreviewList',
        'isBraggable',
        'categoryId',
        'modelId',
    ];
}
