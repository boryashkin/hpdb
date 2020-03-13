<?php

namespace App\Common\MessageBus\Messages\Crawlers;

use App\Common\MessageBus\Messages\MessageInterface;
use App\Common\ValueObjects\GithubRepo;
use MongoDB\BSON\ObjectId;

class NewGithubProfileToCrawlMessage implements MessageInterface
{
    private $githubProfileId;
    private $login;
    private $contributedTo;
    private $repo;

    public function __construct(
        ObjectId $githubProfileId,
        string $login,
        ?GithubRepo $contributedTo,
        ?GithubRepo $repo
    )
    {
        $this->githubProfileId = $githubProfileId;
        $this->login = $login;
        $this->contributedTo = $contributedTo;
        $this->repo = $repo;
    }

    public function getGithubProfileId(): ObjectId
    {
        return $this->githubProfileId;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getContributedTo(): ?GithubRepo
    {
        return $this->contributedTo;
    }

    public function getRepo(): ?GithubRepo
    {
        return $this->repo;
    }
}
