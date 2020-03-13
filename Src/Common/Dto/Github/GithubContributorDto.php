<?php

namespace App\Common\Dto\Github;

class GithubContributorDto
{
    public $total; // 17
    public $author; // [id, login, avatar, path, hovercard_url]
    public $weeks; // array

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        $this->author = new GithubContributorAuthorDto((array)$this->author);
    }
}
