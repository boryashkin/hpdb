<?php

namespace app\dto\github;

class GithubContributorAuthorDto
{
    public $id; // 17
    public $login; // "john"
    public $avatar; // "https://avatars1.githubusercontent.com/u/442991?s=60&v=4"
    public $path; // "/royopa"
    public $hovercard_url; // "/users/royopa/hovercard"

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
