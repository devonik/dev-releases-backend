<?php


namespace App\Console;


use App\Models\Tech;
use Carbon\Carbon;
use http\Message\Body;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class CheckReleases
{
    public function __invoke()
    {

        $checkableTechs = Tech::query()->whereDate('created_at', '<', Carbon::today())->get();

        if($checkableTechs->count() > 0){
            $firebaseMessaging = (new Factory())->createMessaging();
            $firebaseMessages = array();
            foreach ($checkableTechs as $tech){
                $response = Http::get("https://api.github.com/repos/$tech->github_owner/$tech->github_repo/releases/latest");
                if($response->json()['tag_name'] != $tech->latest_tag){
                    //If the tag is new lets update our database entry
                    $tech->update(
                        [
                            "latest_tag" => $response->json()['tag_name'],
                            "github_release_id" =>  $response->json()['id'],
                            "github_link" => $response->json()['html_url'],
                            "release_published_at" => $response->json()['published_at'],
                            "body" => $response->json()['body']
                        ]
                    );

                    //Lets send firebase push notifications
                    $topic = 'new-tech-release';
                    $title = 'New github release';
                    $body = $tech->title.' released to '.$tech->latest_tag;

                    $notification = Notification::fromArray([
                        'title' => $title,
                        'body' => $body,
                        'image' => $tech->hero_image,
                    ]);

                    $notificationData = $tech->toArray();
                    //Needed so we can read the send message if the app is in background
                    $notificationData['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';
                    //Needed because the notification itself is not attatched on background message
                    $notificationData['message_identifier'] = $topic;

                    array_push(
                        $firebaseMessages,
                        CloudMessage::withTarget('topic', $topic)
                            ->withNotification($notification)
                            ->withData($notificationData)
                    );

                }
            }
            if(count($firebaseMessages) > 0){
                try {
                    $firebaseMessaging->sendAll($firebaseMessages);
                    print_r("[".count($firebaseMessages)."] firebase messages successfully sent !!!");
                } catch (MessagingException $e) {
                    error_log($e);
                } catch (FirebaseException $e) {
                    error_log($e);
                }
            }
        }
    }
}
