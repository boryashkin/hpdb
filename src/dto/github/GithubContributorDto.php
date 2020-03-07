<?php

namespace app\dto\github;

class GithubContributorDto
{
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        $this->author = new GithubContributorAuthorDto((array)$this->author);
    }

    public $total;// 17
    public $author;// [id, login, avatar, path, hovercard_url]
    public $weeks;// array
}
