<?php

namespace App\Common\MessageBus\Messages\Crawlers;

use App\Common\MessageBus\Messages\MessageInterface;
use App\Common\ValueObjects\Url;
use MongoDB\BSON\ObjectId;

class GithubFollowersToCrawlMessage implements MessageInterface
{
    private $originGithubProfileId;
    private $url;

    public function __construct(ObjectId $githubProfileId, Url $url)
    {
        $this->originGithubProfileId = $githubProfileId;
        $this->url = $url;
    }

    public function getUrl(): Url
    {
        return $this->url;
    }

    public function getOriginGithubProfileId(): ObjectId
    {
        return $this->originGithubProfileId;
    }
}
