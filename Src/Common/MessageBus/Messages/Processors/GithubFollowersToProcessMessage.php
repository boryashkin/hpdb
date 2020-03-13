<?php

namespace App\Common\MessageBus\Messages\Processors;

use App\Common\Dto\Website\WebsiteIndexDto;
use App\Common\MessageBus\Messages\MessageInterface;

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
