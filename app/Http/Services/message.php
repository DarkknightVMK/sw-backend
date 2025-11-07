<?php

use App\result\DataResult;
use App\result\ServiceResult;
use App\result\message\MessageDetails;
use App\result\TypedRecordSetResult;
use App\Models\Messages;
use App\Models\messagesSent;
use App\Http\Services\functions;

use Carbon\Carbon;

class message
{
    function getInboxUnreadMessageCount()
    {
        //count unread messages
        $unreadMessageCount = Messages::where('told', '=', session('avatar'))
            ->where('status', '=', 'N')
            ->count();
        return new DataResult((float)$unreadMessageCount);
        
    }

    function viewMyAvatarsInbox()
    {
        //order by date 
        //get all messages from my avatars
        $messages = Messages::where('told', '=', session('avatar'))->where('indexed', true)
            ->orderBy('created_at', 'desc')
            ->get();
        $messageDetails = array();
        foreach ($messages as $message) {
            $messageDetails[] = new MessageDetails($message);
        }
        return new TypedRecordSetResult($messageDetails);
    }

    function viewMyAvatarsSentMessages()
    {
        //get all messages from my avatars
        $messages = messagesSent::where('fromAvatarId', '=', session('avatar'))->where('indexed', true)
            ->orderBy('created_at', 'desc')
            ->get();
        $messageDetails = array();
        foreach ($messages as $message) {
            $messageDetails[] = new MessageDetails($message);
        }
        return new TypedRecordSetResult($messageDetails);
    }

    function readMessage($timeconfig, $messageId)
    {
        //read message
        $message = Messages::where('id', '=', $messageId)
            ->first();
        $message->status = 'R';
        $message->timeRead = Carbon::now()->toDateTimeString();
        $message->save();
        return new ServiceResult(true);
    }

    function sendMessageFromWorld($timeconfig, $subject, $text, $avatarId, $unk)
    {
        //send message from world
        // TODO check permissions of AID to send message to for activeInbox etc or if avatar is blocked
        $message = new Messages();
        $message->fromAvatarId = session('avatar');
        $message->told = $avatarId;
        $message->avatar_id = $avatarId;
        $message->subject = $subject;
        $message->text = $text;
        $message->type = 'A';
        $message->status = 'N';
        $message->timeRead = null;
        $message->ref = functions::generateRandomString(20);
        $message->save();

        $messageSent = new messagesSent();
        $messageSent->fromAvatarId = session('avatar');
        $messageSent->told = $avatarId;
        $messageSent->avatar_id = $avatarId;
        $messageSent->subject = $subject;
        $messageSent->text = $text;
        $messageSent->type = 'A';
        $messageSent->status = 'N';
        $messageSent->timeRead = null;
        $messageSent->ref =$message->ref;
        $messageSent->save();

        return new ServiceResult(true);
    }
    // SENT MESSAGES DELETION
    function deleteMessagesFromSent($array)
    {
        //delete messages from sent
        // $array is an array of message ids

        foreach ($array as $messageId) {
            $message = messagesSent::where('id', $messageId)
                ->first();
            $message->indexed = false;
            $message->save();
        }
        return new ServiceResult(true);
    }

    function deleteAllMessagesFromSent($timeconfig, $days)
    {
        //delete all messages from sent
        // $days is the number of days to delete messages from
                //if 0 days, delete all messages from inbox else delete messages from inbox older than days

        if ($days == 0) {   
            $messages = messagesSent::where('fromAvatarId', '=', session('avatar'))
                ->where('indexed', true)
                ->get();
                foreach ($messages as $message) {
                    $message->indexed = false;
                    $message->save();
                }
        } else {
            $messages = messagesSent::where('fromAvatarId', '=', session('avatar'))
                ->where('indexed', true)
                ->where('created_at', '<', Carbon::now()->subDays($days)->toDateTimeString())
                ->get();
            foreach ($messages as $message) {
                $message->indexed = false;
                $message->save();
            }
        }
        return new ServiceResult(true);
 
    }

    //INBOX DELETION
    function deleteMessagesFromInbox($array)
    {
        //delete messages from inbox
        // $array is an array of message ids
        foreach ($array as $messageId) {
            $message = Messages::where('id', $messageId)
                ->first();
            $message->indexed = false;
            $message->save();
        }
        return new ServiceResult(true);
    }

    function deleteAllReadMessagesFromInbox()
    {
        //delete all read messages from inbox
        $messages = Messages::where('told', '=', session('avatar'))->where('indexed', true)
            ->where('status', '=', 'R')
            ->get();
        foreach ($messages as $message) {
            $message->indexed = false;
            $message->save();
        }
        return new ServiceResult(true);
    }

    function deleteAllMessagesFromInbox($timeconfig, $days)
    {
        //if 0 days, delete all messages from inbox else delete messages from inbox older than days
        if ($days == 0) {
            $messages = Messages::where('told', '=', session('avatar'))->where('indexed', true)
                ->get();
            foreach ($messages as $message) {
                $message->indexed = false;
                $message->save();
            }
        } else {
            $messages = Messages::where('told', '=', session('avatar'))->where('indexed', true)
                ->where('created_at', '<', Carbon::now()->subDays($days)->toDateTimeString())
                ->get();
            foreach ($messages as $message) {
                $message->indexed = false;
                $message->save();
            }
        }
        return new ServiceResult(true);
    }
    
}