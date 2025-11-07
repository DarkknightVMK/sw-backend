<?php
namespace App\result\message;
use App\Http\Services\functions;
use Carbon\Carbon;

#[\AllowDynamicProperties]
class MessageDetails
{
  public $_explicitType = 'com.smallworlds.communication.message.detail.service.data.MessageDetails';

  public function __construct($message)
  {
    $this->fromAvatarNameInstance = (float)functions::getAvatarByID($message->fromAvatarId, 'nameInstance');
    $this->fromId = $message->fromAvatarId;
    $this->subject = $message->subject;
    $this->toAvatarOnline = null;
    $this->activeInbox = true; // check perms
    $this->fromAvatarFirstName = functions::getAvatarByID($message->fromAvatarId, 'firstName');
    $this->fromAvatarOwnerId = null; // uid
    $this->type =  $message->type; // S = System, A = avatar, M = Mod, U = user
    $this->previousId = null;
    $this->timestamp =  new \Amfphp_Core_Amf_Types_Date( Carbon::createFromTimestamp(strtotime($message->created_at))->valueOf());
    $this->id = strval($message->id);
    $this->toEntity = $message->toEntity; // A = Avatar, look in main.swf
    $this->fromAvatarOnline = null;
    $this->toAvatarOwnerId = null; // uid
    $this->text = $message->text;
    $this->toUserLastName = null;
    $this->fromAvatarId = $message->fromAvatarId;
    $this->activeSent = true; // check perms
    $this->toId = $message->told; // ? current avatar id
    $this->fromAvatarThumbPostfix = functions::getAvatarByID($message->fromAvatarId, 'headPostfix');
    $this->status = $message->status; // R = Read, N = unread
    $this->toAvatarNameInstance = (float) functions::getAvatarByID($message->told, 'nameInstance');
    $this->fromUserLastName = null;
    $this->fromAvatarLastName = functions::getAvatarByID($message->fromAvatarId, 'lastName');
    $this->toUserFirstName= null;
    $this->ref = $message->ref;
    $this->fromUserFirstName = null;
    $this->toAvatarLastName=  functions::getAvatarByID($message->told, 'lastName');
    $this->timeRead = ($message->timeRead != null) ? new \Amfphp_Core_Amf_Types_Date( Carbon::createFromTimestamp(strtotime($message->timeRead))->valueOf()) : null; // date
    $this->fromEntity = $message->fromEntity; // A = Avatar, U = User
    $this->toAvatarFirstName = functions::getAvatarByID($message->told, 'firstName');
    return $this;
  }
}