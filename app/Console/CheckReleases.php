<?php


namespace App\Console;


use App\Models\Tech;
use App\Repositories\TechRepository;
use Carbon\Carbon;
use http\Message\Body;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class CheckReleases
{
    /**
     * The tech repository implementation.
     *
     * @var TechRepository
     */
    protected $techRepository;

    public function __invoke()
    {
        error_log("Cronjob call: Starting CheckReleases");

        $this->techRepository = new TechRepository();

        $checkableTechs = Tech::query()->whereDate('created_at', '<', Carbon::today())->get();

        if ($checkableTechs->count() > 0) {
            $firebaseMessaging = (new Factory())->createMessaging();
            $firebaseMessages = array();
            foreach ($checkableTechs as $tech) {
                $updateTechResponse = $this->techRepository->updateTechWithGithubApiRequest($tech);
                //updateTechResponse is false when there was an error or the tag is not new so we dont need a push notification
                if ($updateTechResponse) {
                    //Only send push notification if the response is there (not false)
                    //Lets send firebase push notifications
                    $topic = 'new-tech-release';
                    $title = 'New github release';
                    $body = $updateTechResponse->title . ' released to ' . $updateTechResponse->latest_tag;

                    $notification = Notification::fromArray([
                        'title' => $title,
                        'body' => $body,
                        'image' => $tech->hero_image,
                    ]);

                    $notificationData = $updateTechResponse->toArray();
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

            if (count($firebaseMessages) > 0) {
                //Only send notifications if there are new tags
                try {
                    $firebaseMessaging->sendAll($firebaseMessages);
                    print_r("[" . count($firebaseMessages) . "] firebase messages successfully sent !!!");
                } catch (MessagingException $e) {
                    error_log($e);
                } catch (FirebaseException $e) {
                    error_log($e);
                }
            }
        }
    }

}
