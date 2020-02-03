<?php

namespace app\messageBus\messages\crawlers;

use app\messageBus\messages\MessageInterface;
use MongoDB\BSON\ObjectId;

class NewGithubProfileToCrawlMessage implements MessageInterface
{
    private $githubProfileId;
    private $login;

    public function __construct(ObjectId $githubProfileId, string $login)
    {
        $this->githubProfileId = $githubProfileId;
        $this->login = $login;
    }

    public function getGithubProfileId(): ObjectId
    {
        return $this->githubProfileId;
    }

    public function getLogin(): string
    {
        return $this->login;
    }
}
