<?php

namespace app\messageBus\messages\processors;

use app\dto\website\WebsiteIndexDto;
use app\messageBus\messages\MessageInterface;
use MongoDB\BSON\ObjectId;

class GithubProfileParsedToProcessMessage implements MessageInterface
{
    private $githubProfileId;
    private $content;
    private $parsedDate;

    public function __construct(ObjectId $githubProfileId, WebsiteIndexDto $content, \DateTimeInterface $parsedDate)
    {
        $this->githubProfileId = $githubProfileId;
        $this->parsedDate = $parsedDate;
        $this->content = $content;
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
}
