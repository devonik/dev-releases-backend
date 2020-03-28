<?php


namespace App\Http\Controllers\Api;


use App\Models\Tech;
use App\Repositories\TechRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class TestController
{
    /**
     * The tech repository implementation.
     *
     * @var TechRepository
     */
    protected $techRepository;

    /**
     * Create a new controller instance.
     *
     * @param  TechRepository $techRepository
     * @return void
     */
    public function __construct(TechRepository $techRepository)
    {
        $this->techRepository = $techRepository;
    }

    public function getServerTimestamp(){
        return Carbon::now();
    }

    public function testFirebaseMessage(){
        $tech = Tech::all()->all();
        if($tech[0]){

            $firebaseMessaging = (new Factory())->createMessaging();
            $tech = $tech[0];
            $updatedTech = $this->techRepository->updateTechWithGithubApiRequest($tech)['tech'];
            //updateTechResponse is false when there was an error or the tag is not new so we dont need a push notification
            if ($updatedTech->latest_tag !== null) {
                //Only send push notification if the response is there (not false)
                //Lets send firebase push notifications
                $topic = 'new-release-'.$updatedTech->id;
                $title = 'New github release';
                $body = $updatedTech->title . ' released to ' . $updatedTech->latest_tag;

                $notification = Notification::fromArray([
                    'title' => $title,
                    'body' => $body,
                    'image' => $updatedTech->hero_image,
                ]);

                $notificationData = $updatedTech->toArray();
                //Needed so we can read the send message if the app is in background
                $notificationData['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';
                //Needed because the notification itself is not attatched on background message
                $notificationData['message_identifier'] = $topic;

                Log::info("Test call: Add firebase message with topic [".$topic."] to the messages");
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
}
