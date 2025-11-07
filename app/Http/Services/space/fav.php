<?php
use App\Models\spaceFavorites;

class fav
{
    function isSpaceFavorite($timeconfig,$spaceID)
    {
        $ret = new stdClass();
        $fav = spaceFavorites::where('space_id', $spaceID)->where('avatar_id', session('avatar'))->first();

        $ret->value = $fav ? true : false;
        $ret->success = true;
        return $ret;
    }
}