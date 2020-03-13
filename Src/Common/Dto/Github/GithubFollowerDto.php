<?php

namespace App\Common\Dto\Github;

class GithubFollowerDto
{
    public $login; // "AevaOnline",
    public $id; // 806320,
    public $node_id; // "MDQ6VXNlcjgwNjMyMA==",
    public $avatar_url; // "https://avatars2.githubusercontent.com/u/806320?v=4",
    public $gravatar_id; // "",
    public $url; // "https://api.github.com/users/AevaOnline",
    public $html_url; // "https://github.com/AevaOnline",
    public $followers_url; // "https://api.github.com/users/AevaOnline/followers",
    public $following_url; // "https://api.github.com/users/AevaOnline/following{/other_user}",
    public $gists_url; // "https://api.github.com/users/AevaOnline/gists{/gist_id}",
    public $starred_url; // "https://api.github.com/users/AevaOnline/starred{/owner}{/repo}",
    public $subscriptions_url; // "https://api.github.com/users/AevaOnline/subscriptions",
    public $organizations_url; // "https://api.github.com/users/AevaOnline/orgs",
    public $repos_url; // "https://api.github.com/users/AevaOnline/repos",
    public $events_url; // "https://api.github.com/users/AevaOnline/events{/privacy}",
    public $received_events_url; // "https://api.github.com/users/AevaOnline/received_events",
    public $type; // "User",
    public $site_admin; // false

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
