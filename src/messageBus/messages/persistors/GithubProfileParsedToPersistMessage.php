<?php

namespace app\messageBus\messages\persistors;

use app\dto\github\GithubProfileDto;
use app\messageBus\messages\MessageInterface;
use MongoDB\BSON\ObjectId;

class GithubProfileParsedToPersistMessage implements MessageInterface
{
    /** @var ObjectId */
    private $githubProfileId;
    /** @var GithubProfileDto */
    private $dto;

    public function __construct(ObjectId $githubProfileId, GithubProfileDto $dto)
    {
        $this->githubProfileId = $githubProfileId;
        $this->dto = $dto;
    }

    public function getGithubProfileId(): ObjectId
    {
        return $this->githubProfileId;
    }

    public function getDto(): GithubProfileDto
    {
        return $this->dto;
    }
}
