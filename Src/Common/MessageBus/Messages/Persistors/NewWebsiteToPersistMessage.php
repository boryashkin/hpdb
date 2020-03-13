<?php

declare(strict_types=1);

namespace App\Common\MessageBus\Messages\Persistors;

use App\Common\MessageBus\Messages\MessageInterface;
use App\Common\ValueObjects\Url;
use DateTime;
use MongoDB\BSON\ObjectId;

class NewWebsiteToPersistMessage implements MessageInterface
{
    /** @var Url */
    private $url;
    /** @var string */
    private $source;
    /** @var \DateTime */
    private $dateFound;
    /** @var ObjectId */
    private $githubProfileId;

    public function __construct(Url $url, string $source, DateTime $dateFound, ObjectId $githubProfileId = null)
    {
        $this->url = $url;
        $this->source = $source;
        $this->dateFound = $dateFound;
        $this->githubProfileId = $githubProfileId;
    }

    public function getUrl(): Url
    {
        return $this->url;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getDateFound(): DateTime
    {
        return $this->dateFound;
    }

    public function getGithubProfileId(): ?ObjectId
    {
        return $this->githubProfileId;
    }
}
