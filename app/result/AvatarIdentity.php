<?
namespace App\result;
use App\Models\avatarFriends;
use App\Models\onlineUsers;
use Carbon\Carbon;
use App\Models\avatar_xp;

#[\AllowDynamicProperties]
class AvatarIdentity
{
  public $lastName;
  public $firstName;
  public $userId;
  public $homeSpaceId;
  public $nameInstance;
  public $id;
  public $currentSpaceId;
  public $onlineTimestamp;
  public $bio;
  public $primaryXP;
  public $thumbUrl;
  public $headUrl;
  public $isReciprocal;
  public $online;

  function __construct($avatar)
  {

    $this->lastName = $avatar->lastName;
    $this->firstName = $avatar->firstName;
    $this->nameInstance = $avatar->nameInstance;
    $this->id = $avatar->avatar_id;
    $af = avatarFriends::where('avatar_id', $avatar->avatar_id)->where('friend_id',session('avatar'))->first();
    if ($af == null)
      $af = avatarFriends::where('friend_id', $avatar->avatar_id)->where('avatar_id',session('avatar'))->first();
    $this->isReciprocal = boolval($af->reciprocal); //TODO
    $this->isFriend = boolval($af->reciprocal); //TODO
    $this->homeSpaceId = $avatar->homeSpaceId;
    $this->currentSpaceId = (onlineUsers::where('avatar_id', $avatar->avatar_id)->exists()) ? onlineUsers::where('avatar_id', $avatar->avatar_id)->first()->space_id : null;
    $this->onlineTimestamp = (onlineUsers::where('avatar_id', $avatar->avatar_id)->exists()) ? new \Amfphp_Core_Amf_Types_Date( Carbon::createFromTimestamp(strtotime(onlineUsers::where('avatar_id', $avatar->avatar_id)->first()->updated_at))->valueOf()) : null; // TODO
    $this->online = (onlineUsers::where('avatar_id', $avatar->avatar_id)->exists()) ? boolval(onlineUsers::where('avatar_id', $avatar->avatar_id)->first()->online) : false; // TODO
    $this->primaryXP = avatar_xp::where('avatar_id', $avatar->avatar_id)->where('xpleveltype', 8)->first()->xp; // TODO
    $this->primaryXPLevel = avatar_xp::where('avatar_id', $avatar->avatar_id)->where('xpleveltype', 8)->first()->level; // TODO
    $this->bio = ""; // TODO
    $this->userId = strval($avatar->owner_id);
    $this->thumbUrl = $avatar->thumbUrl;
    $this->headUrl = str_replace('_thumb', '', $avatar->thumbUrl);
    return $this;
  }

}