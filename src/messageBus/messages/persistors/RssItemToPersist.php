<?php

namespace app\messageBus\messages\persistors;

use app\messageBus\messages\MessageInterface;
use DateTime;
use MongoDB\BSON\ObjectId;

class RssItemToPersist implements MessageInterface
{
    /** @var ObjectId */
    private $websiteId;
    /** @var null|string */
    private $title;
    /** @var null|string */
    private $description;
    /** @var null|string */
    private $link;
    /** @var null|DateTime */
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
