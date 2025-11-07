<?php
use App\Models\Users;
use App\Models\Avatars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\userGroups;
use Illuminate\Support\Facades\Hash;

function getChallenge($id) 
{
  $amf = new stdClass();
  $amf->success = true;
  return $amf;
}