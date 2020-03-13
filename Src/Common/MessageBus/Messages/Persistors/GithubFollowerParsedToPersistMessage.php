<?php

namespace App\Common\MessageBus\Messages\Persistors;

use App\Common\Dto\Github\GithubFollowerDto;
use App\Common\MessageBus\Messages\MessageInterface;

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
