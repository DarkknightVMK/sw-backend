<?
namespace App\result;
use App\Models\avatarFriends;

#[\AllowDynamicProperties]
class FriendRequest
{
  public $id;
  public $avatar_id;
  public $user_id;

  function __construct($avatar)
  {
    $this->id = avatarFriends::where('avatar_id', $avatar->avatar_id)->where('friend_id',session('avatar'))->get()->first()->id;
    $this->avatarId = $avatar->avatar_id;
    $this->userId = null;
    return $this;
  }

}