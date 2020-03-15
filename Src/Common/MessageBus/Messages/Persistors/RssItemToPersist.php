<?php

namespace App\Common\MessageBus\Messages\Persistors;

use App\Common\MessageBus\Messages\MessageInterface;
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
    private $pubDate;

    public function __construct(ObjectId $websiteId, ?string $title, ?string $description, ?string $link, ?DateTime $pubDate)
    {
        $this->websiteId = $websiteId;
        $this->title = $title;
        $this->description = $description;
        $this->link = $link;
        $this->pubDate = $pubDate;
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

    public function getPubDate(): ?DateTime
    {
        return $this->pubDate;
    }
}
