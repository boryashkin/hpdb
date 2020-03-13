<?php

declare(strict_types=1);

namespace App\Common\ValueObjects;

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

    public function __toString()
    {
        return $this->getProfile() . '/' . $this->getName();
    }

    public function getProfile(): string
    {
        return $this->profile;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function createByRepoString(string $repo): self
    {
        $arr = explode('/', $repo);

        return new GithubRepo($arr[0], $arr[1]);
    }
}
