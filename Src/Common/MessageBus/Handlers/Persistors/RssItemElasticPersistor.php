<?php

namespace App\Common\MessageBus\Handlers\Persistors;

use App\Common\Dto\WebFeed\WebFeedItem;
use App\Common\MessageBus\Messages\Persistors\RssItemToPersist;
use App\Common\Repositories\WebFeedRepository;

class RssItemElasticPersistor implements PersistorInterface
{
    private $name;
    /** @var WebFeedRepository */
    private $repository;

    public function __construct(string $name, WebFeedRepository $repository)
    {
        $this->name = $name;
        $this->repository = $repository;
    }

    public function __invoke(RssItemToPersist $message)
    {
        $this->saveIndex($message);
    }

    public function saveIndex(RssItemToPersist $message): array
    {
        $item = new WebFeedItem([
            'title' => $message->getTitle(),
            'description' => $message->getDescription(),
            'date' => $message->getPubDate(),
            'website_id' => $message->getWebsiteId(),
            'link' => $message->getLink(),
        ]);

        return $this->repository->indexWithLanguageDetection($item);
    }
}
