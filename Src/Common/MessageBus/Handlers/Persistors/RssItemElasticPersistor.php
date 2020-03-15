<?php

namespace App\Common\MessageBus\Handlers\Persistors;

use App\Common\MessageBus\Messages\Persistors\RssItemToPersist;
use Elasticsearch\Client;

class RssItemElasticPersistor implements PersistorInterface
{
    private $name;
    /** @var Client */
    private $elastic;

    public function __construct(string $name, Client $elastic)
    {
        $this->name = $name;
        $this->elastic = $elastic;
    }

    public function __invoke(RssItemToPersist $message)
    {
        $this->saveIndex($message);
    }

    public function saveIndex(RssItemToPersist $message): array
    {
        $params = [
            'index' => 'website_rss_item',
            'body' => [
                'title' => $message->getTitle(),
                'description' => $message->getDescription(),
                'date' => $message->getPubDate() ? $message->getPubDate()->format(DATE_ATOM) : null,
                'website_id' => (string)$message->getWebsiteId(),
                'link' => $message->getLink(),
            ],
        ];

        return $this->elastic->index($params);
    }
}
