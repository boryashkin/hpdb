<?php

namespace app\messageBus\messages\processors;

use app\dto\website\WebsiteIndexDto;
use app\messageBus\messages\MessageInterface;

class GithubFollowersToProcessMessage implements MessageInterface
{
    private $content;
    private $parsedDate;

    public function __construct(WebsiteIndexDto $content, \DateTimeInterface $parsedDate)
    {
        $this->parsedDate = $parsedDate;
        $this->content = $content;
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
