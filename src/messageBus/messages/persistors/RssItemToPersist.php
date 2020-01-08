<?php

namespace app\messageBus\messages\persistors;

use app\messageBus\messages\MessageInterface;
use MongoDB\BSON\ObjectId;
use DateTime;

class RssItemToPersist implements MessageInterface
{
    /** @var ObjectId */
    private $websiteId;
    /** @var string|null */
    private $title;
    /** @var string|null */
    private $description;
    /** @var string|null */
    private $link;
    /** @var DateTime|null */
    private $date;

    public function __construct(ObjectId $websiteId, ?string $title, ?string $description, ?string $link, ?DateTime $date)
    {
        $this->websiteId = $websiteId;
        $this->title = $title;
        $this->description = $description;
        $this->link = $link;
        $this->date = $date;
    }

    public function getWebsiteId(): ObjectId
    {
        return $this->websiteId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }
}
