<?php

declare(strict_types=1);

namespace app\valueObjects;

class GithubRepo
{
    /** @var string */
    private $profile;
    /** @var string */
    private $name;

    public function __construct(string $profile, string $repoName)
    {
        $this->profile = $profile;
        $this->name = $repoName;
    }

    public function getProfile(): string
    {
        return $this->profile;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->getProfile() . '/' . $this->getName();
    }

    public static function createByRepoString(string $repo): self
    {
        $arr = explode('/', $repo);

        return new GithubRepo($arr[0], $arr[1]);
    }
}
