<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class actionbar extends Model
{
    use HasFactory;
    protected $fillable = [
      // 'actionbaritem_id',
      'actionbaritem_column',
      'actionbaritem_name', //item Id
      'actionbaritem_row',
      'actionbaritem_type',
      'actionbaritem_cid',
      'actionbaritem_label',
      'actionbaritem_url',
      'actionbaritem_user_id'
    ];
    protected $table = 'actionbar';
    protected $primaryKey = 'actionbaritem_id';


}