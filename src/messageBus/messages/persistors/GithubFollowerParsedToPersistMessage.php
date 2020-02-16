<?php

namespace app\messageBus\messages\persistors;

use app\dto\github\GithubFollowerDto;
use app\messageBus\messages\MessageInterface;

class GithubFollowerParsedToPersistMessage implements MessageInterface
{
    /** @var GithubFollowerDto */
    private $dto;

    public function __construct(GithubFollowerDto $dto)
    {
        $this->dto = $dto;
    }

    public function getDto(): GithubFollowerDto
    {
        return $this->dto;
    }
}
