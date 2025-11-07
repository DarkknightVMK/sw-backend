<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class s2w_SpinPriceDetails extends Model
{
    use HasFactory;
    protected $table = 's2w_spin_price_details';
    protected $fillable = [
        'spins',
        'price',
    ];
}
