<?php

namespace app\dto\github;

class GithubProfileDto
{
    public $id;
    public $node_id;
    public $avatar_url;
    public $gravatar_id;
    public $url;
    public $html_url;
    public $gists_url;
    public $starred_url;
    public $subscriptions_url;
    public $organizations_url;
    public $repos_url;
    public $events_url;
    public $received_events_url;
    public $type;
    public $site_admin;
    public $name;
    public $company;
    public $location;
    public $email;
    public $hireable;
    public $bio;
    public $public_repos;
    public $public_gists;
    public $followers;
    public $following;
    public $login;
    public $blog;
    public $followers_url;
    public $following_url;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
