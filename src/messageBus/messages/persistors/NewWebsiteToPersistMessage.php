<?php

namespace app\messageBus\messages\persistors;

use app\messageBus\messages\MessageInterface;
use app\valueObjects\Url;
use DateTime;

class NewWebsiteToPersistMessage implements MessageInterface
{
    /** @var Url */
    private $url;
    /** @var string */
    private $source;
    /** @var \DateTime */
    private $dateFound;

    public function __construct(Url $url, string $source, DateTime $dateFound)
    {
        $this->url = $url;
        $this->source = $source;
        $this->dateFound = $dateFound;
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
}
