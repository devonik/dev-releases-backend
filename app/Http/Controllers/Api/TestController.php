<?php


namespace App\Http\Controllers\Api;


use App\Models\Tech;
use Carbon\Carbon;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class TestController
{
    public function getServerTimestamp(){
        return Carbon::now();
    }

    public function testFirebaseMessage(){
        $tech = Tech::all()->all();
        if($tech[1]){

            $firebaseMessaging = (new Factory())->createMessaging();
            $tech = $tech[1];
            $tech->latest_tag = "dd";
            //Lets send firebase push notifications
            $topic = 'new-release-'.$tech->id;
            error_log("send message to: ".$topic);
            $title = 'New github release';
            $body = $tech->title.' released to '.$tech->latest_tag;
            $notification = ['title' => $title, 'body' => $body];
            $notificationData = $tech->toArray();
            //Needed so we can read the send message if the app is in background
            $notificationData['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';
            //Needed because the notification itself is not attatched on background message
            $notificationData['message_identifier'] = $topic;
            $firebaseMessage = CloudMessage::withTarget('topic', $topic)
                ->withNotification($notification)
                ->withData($notificationData);

            try {
                $firebaseMessaging->send($firebaseMessage);
                return "firebase message successfully sent !!!";
            } catch (MessagingException $e) {
                error_log($e);
            } catch (FirebaseException $e) {
                error_log($e);
            }
        }

    }
}
