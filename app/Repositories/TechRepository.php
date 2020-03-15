<?php


namespace App\Repositories;



use App\Models\Tech;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class TechRepository
{
    public function updateTechWithGithubApiRequest(Model $tech){
        $response = false;

        $latestRelease = Http::get("https://api.github.com/repos/$tech->github_owner/$tech->github_repo/releases/latest");
        //TODO some repos doesn't have releases (Example flutter https://github.com/flutter/flutter/releases) so we have to grab tags and take latest
        if($latestRelease->ok()){
            //If the Https Status is 200 (ok)
            if($latestRelease->json()['tag_name'] != $tech->latest_tag){
                //If the tag is new lets update the database entry
                $response = Tech::query()
                    ->where("id", $tech->id)
                    ->update(
                        [
                            "latest_tag" => $latestRelease->json()['tag_name'],
                            "github_release_id" =>  $latestRelease->json()['id'],
                            "github_link" => $latestRelease->json()['html_url'],
                            "release_published_at" => $latestRelease->json()['published_at'],
                            "body" => $latestRelease->json()['body']
                        ]
                    );
            }

        }else{
            //The response is not ok in following scenarios
            //1. The repo does'nt have a latest release so we get "Method not found"

            //Case 1: We have to call the tags api and take the latest to update our database entry
            $tags = Http::get("https://api.github.com/repos/$tech->github_owner/$tech->github_repo/tags");
            if($tags->ok()){
                //If the Https Status is 200 (ok)
                $latestTagObject = $tags->json()[0];
                if($latestTagObject['name'] != $tech->latest_tag) {
                    //If the tag is new lets update the database entry
                    $responseCommit = Http::get($latestTagObject['commit']['url']);

                    $githubLink = 'https://github.com/'.$tech->github_owner.'/'.$tech->github_repo.'/';
                    $githubPublishedAt = Carbon::now();
                    $body = 'No body text found';
                    if($responseCommit->ok()){
                        //If we can get the commit data lets take it
                        $githubPublishedAt = $responseCommit->json()['commit']['author']['date'];
                        $githubLink = $responseCommit->json()['html_url'];
                        $body = $responseCommit->json()['commit']['message'];
                    }
                    //If the Https Status is 200 (ok) lets update our database
                    $response = Tech::query()
                        ->where("id", $tech->id)
                        ->update(
                            [
                                "latest_tag" => $latestTagObject['name'],
                                "github_link" => $githubLink,
                                "release_published_at" => $githubPublishedAt,
                                "body" => $body
                            ]
                        );
                }

            }

        }
        return $response;
    }
}
