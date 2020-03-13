<?php

namespace App\Common\MessageBus\Messages\Persistors;

use App\Common\Dto\Github\GithubProfileDto;
use App\Common\MessageBus\Messages\MessageInterface;
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
