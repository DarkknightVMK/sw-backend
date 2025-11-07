<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class avatarWearing extends Model
{
  use HasFactory;
  public $primaryKey = 'avatar_id';
  public $incrementing = false;
  protected $fillable = [
    'avatar_id',
    'item_id',
  ];
  protected $table = 'avatar_wearing';

}