<?php


namespace App\Http\Controllers\Api;


use App\Models\Tech;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class TestController
{
    public function testFirebaseMessage(){
        $tech = Tech::all()->all();
        if($tech[0]){

            $firebaseMessaging = (new Factory())->createMessaging();
            $tech = $tech[0];

            //Lets send firebase push notifications
            $title = 'New github release';
            $body = $tech->title.' released to '.$tech->latest_tag;
            $notification = ['title' => $title, 'body' => $body];
            $notificationData = $tech->toArray();
            $notificationData['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';
            $firebaseMessage = CloudMessage::withTarget('topic', 'new-tech-release')
                ->withNotification($notification)
                ->withData($notificationData);

            try {
                $firebaseMessaging->send($firebaseMessage);
                print_r("firebase message successfully sent !!!");
            } catch (MessagingException $e) {
                error_log($e);
            } catch (FirebaseException $e) {
                error_log($e);
            }
        }

    }
}
