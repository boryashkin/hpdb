<?php

namespace App\Common\MessageBus\Messages\Processors;

use App\Common\Dto\Website\WebsiteIndexDto;
use App\Common\MessageBus\Messages\MessageInterface;
use App\Common\ValueObjects\GithubRepo;
use MongoDB\BSON\ObjectId;

class GithubProfileParsedToProcessMessage implements MessageInterface
{
    private $githubProfileId;
    private $content;
    private $parsedDate;
    private $contributedTo;
    private $repo;

    public function __construct(
        ObjectId $githubProfileId,
        WebsiteIndexDto $content,
        \DateTimeInterface $parsedDate,
        ?GithubRepo $contributedTo,
        ?GithubRepo $repo
    )
    {
        $this->githubProfileId = $githubProfileId;
        $this->parsedDate = $parsedDate;
        $this->content = $content;
        $this->contributedTo = $contributedTo;
        $this->repo = $repo;
    }

    public function getGithubProfileId(): ObjectId
    {
        return $this->githubProfileId;
    }

    public function getContent(): WebsiteIndexDto
    {
        return $this->content;
    }

    public function getParsedDate(): \DateTimeInterface
    {
        return $this->parsedDate;
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
