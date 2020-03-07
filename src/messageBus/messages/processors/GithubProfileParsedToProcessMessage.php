<?php

namespace app\messageBus\messages\processors;

use app\dto\website\WebsiteIndexDto;
use app\messageBus\messages\MessageInterface;
use app\valueObjects\GithubRepo;
use MongoDB\BSON\ObjectId;

class GithubProfileParsedToProcessMessage implements MessageInterface
{
    private $githubProfileId;
    private $content;
    private $parsedDate;
    private $contributedTo;

    public function __construct(ObjectId $githubProfileId, WebsiteIndexDto $content, \DateTimeInterface $parsedDate, ?GithubRepo $contributedTo)
    {
        $this->githubProfileId = $githubProfileId;
        $this->parsedDate = $parsedDate;
        $this->content = $content;
        $this->contributedTo = $contributedTo;
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
}
