<?php


namespace App\Console;


use App\Models\Tech;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class CheckReleases
{
    public function __invoke()
    {

        $checkableTechs = Tech::query()->whereDate('created_at', '<', Carbon::today())->get();

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
            }
        }

        print_r(json_encode($checkableTechs));
    }
}
