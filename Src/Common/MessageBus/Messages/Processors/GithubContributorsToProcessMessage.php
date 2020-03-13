<?php

namespace App\Common\MessageBus\Messages\Processors;

use App\Common\Dto\Website\WebsiteIndexDto;
use App\Common\MessageBus\Messages\MessageInterface;
use App\Common\ValueObjects\GithubRepo;

class GithubContributorsToProcessMessage implements MessageInterface
{
    private $repo;
    private $content;
    private $parsedDate;

    public function __construct(GithubRepo $repo, WebsiteIndexDto $content, \DateTimeInterface $parsedDate)
    {
        $this->repo = $repo;
        $this->parsedDate = $parsedDate;
        $this->content = $content;
    }

    public function getIndexDto(): WebsiteIndexDto
    {
        return $this->content;
    }

    public function getParsedDate(): \DateTimeInterface
    {
        return $this->parsedDate;
    }

    public function getRepo(): GithubRepo
    {
        return $this->repo;
    }
}
