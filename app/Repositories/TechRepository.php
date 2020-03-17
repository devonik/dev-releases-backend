<?php


namespace App\Repositories;



use App\Models\Tech;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

/**
 * Class TechRepository
 * @package App\Repositories
 */
class TechRepository
{
    /**
     * @param string $owner
     * @param string $repo
     * @return array
     */
    public function insertTechByAppAction(string $owner, string $repo){
        $response = [
            'tech' => null,
            'error' => null,
            'errorDetail' => null
        ];
        //TODO missing logo for the added repo
        //At first lets check if the owner / repo already exists
        $techExists = Tech::query()->where('github_owner', $owner)->where('github_repo', $repo)->get();
        if($techExists->isEmpty()){
            $githubReponse = $this->getGithubLatestVersion($owner, $repo);
            $newTech = $githubReponse['tech'];
            if($newTech->latest_tag !== null){
                $newTech->title = $repo;
                $newTech->github_owner = $owner;
                $newTech->github_repo = $repo;

                $githubReponse['tech']->save();
            }
            $response = $githubReponse;

        }else{
            $response['tech'] = $techExists;
            $response['error'] = 'The repository is already in our database';
        }

        return $response;
    }

    /**
     * @param Model $existingTech
     * @return array
     */
    public function updateTechWithGithubApiRequest(Model $existingTech){
        $githubReponse = $this->getGithubLatestVersion($existingTech->github_owner, $existingTech->github_repo);
        $changedTech = $githubReponse['tech'];
        if($changedTech->latest_tag !== null){
            if($changedTech->latest_tag != $existingTech->latest_tag){
                $existingTech->update($changedTech->toArray());
                return $githubReponse;
            }
        }
        return $githubReponse;


        /*$latestRelease = Http::get("https://api.github.com/repos/$tech->github_owner/$tech->github_repo/releases/latest");
        //TODO some repos doesn't have releases (Example flutter https://github.com/flutter/flutter/releases) so we have to grab tags and take latest
        if($latestRelease->ok()){
            //If the Https Status is 200 (ok)
            if($latestRelease->json()['tag_name'] != $tech->latest_tag){
                //If the tag is new lets update the database entry
                $tech->update(
                        [
                            "latest_tag" => $latestRelease->json()['tag_name'],
                            "github_release_id" =>  $latestRelease->json()['id'],
                            "github_link" => $latestRelease->json()['html_url'],
                            "release_published_at" => $latestRelease->json()['published_at'],
                            "body" => $latestRelease->json()['body']
                        ]
                    );
                return $tech;
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
                    $tech->update(
                            [
                                "latest_tag" => $latestTagObject['name'],
                                "github_link" => $githubLink,
                                "release_published_at" => $githubPublishedAt,
                                "body" => $body
                            ]
                        );

                    return $tech;
                }

            }

        }
*/
        //return false;
    }

    /**
     * @param string $owner
     * @param string $repo
     * @return array
     */
    public function getGithubLatestVersion(string $owner, string $repo)
    {
        $response = [
            'tech' =>  new Tech(),
            'error' => null,
            'detailError' => null
        ];
        $latestRelease = Http::get("https://api.github.com/repos/$owner/$repo/releases/latest");
        //TODO some repos doesn't have releases (Example flutter https://github.com/flutter/flutter/releases) so we have to grab tags and take latest
        if ($latestRelease->ok()) {
            //If the Https Status is 200 (ok)
            $response['tech']->latest_tag = $latestRelease->json()['tag_name'];
            $response['tech']->github_release_id = $latestRelease->json()['id'];
            $response['tech']->github_link = $latestRelease->json()['html_url'];
            $response['tech']->release_published_at = $latestRelease->json()['published_at'];
            $response['tech']->body = $latestRelease->json()['body'];

        } else {
            //The response is not ok in following scenarios
            //1. The repo does'nt have a latest release so we get "Method not found"

            //Case 1: We have to call the tags api and take the latest to update our database entry
            $tags = Http::get("https://api.github.com/repos/$owner/$repo/tags");
            if ($tags->ok()) {
                $latestTag = isset($tags->json()[0]) ? $tags->json()[0] : null;
                if($latestTag){
                    //If the tag is new lets update the database entry
                    $responseCommit = Http::get($latestTag['commit']['url']);

                    $response['tech']->latest_tag = $latestTag['name'];
                    $response['tech']->github_link = 'https://github.com/'.$owner.'/'.$repo.'/';
                    $response['tech']->release_published_at = Carbon::now();
                    $response['tech']->body = 'No body text found';
                    if($responseCommit->ok()){
                        //If we can get the commit data lets take it
                        $response['tech']->github_link = $responseCommit->json()['html_url'];
                        $response['tech']->release_published_at = $responseCommit->json()['commit']['author']['date'];
                        $response['tech']->body = $responseCommit->json()['commit']['message'];
                    }
                }
            }else{
                $response['error'] = 'Could not get github data';
                $response['detailError'] = $tags->json();
            }
        }
        return $response;
    }
}
