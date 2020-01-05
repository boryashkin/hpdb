<?php

namespace app\messageBus\messages\persistors;

use app\dto\website\WebsiteIndexDto;
use app\messageBus\messages\MessageInterface;
use app\valueObjects\Url;
use DateTime;
use MongoDB\BSON\ObjectId;

class WebsiteFetchedPageToPersistMessage implements MessageInterface
{
    /** @var ObjectId */
    private $websiteId;
    /** @var Url */
    private $url;
    /** @var WebsiteIndexDto */
    private $data;
    /** @var \DateTime */
    private $dateFound;

    public function __construct(ObjectId $websiteId, Url $url, WebsiteIndexDto $data, DateTime $dateFound)
    {
        $this->websiteId = $websiteId;
        $this->url = $url;
        $this->data = $data;
        $this->dateFound = $dateFound;
    }

    public function getWebsiteId(): ObjectId
    {
        return $this->websiteId;
    }

    public function getUrl(): Url
    {
        return $this->url;
    }

    public function getData(): WebsiteIndexDto
    {
        return $this->data;
    }

    public function getDateFound(): DateTime
    {
        return $this->dateFound;
    }
}
