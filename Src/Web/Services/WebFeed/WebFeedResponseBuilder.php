<?php

declare(strict_types=1);

namespace App\Web\Services\WebFeed;

use App\Common\Dto\WebFeed\WebFeedItem;

class WebFeedResponseBuilder
{
    private const DATE_FORMAT = 'd M H:i';

    /**
     * @param WebFeedItem[]
     * @return WebFeedResponseItem[]
     */
    public function createList(array $webFeedItems): array
    {
        $response = [];
        foreach ($webFeedItems as $item) {
            $response[] = $this->createItem($item);
        }

        return $response;
    }

    public function createItem(WebFeedItem $item): WebFeedResponseItem
    {
        $response = new WebFeedResponseItem();
        $response->title = $item->title;
        $response->description = strip_tags(htmlspecialchars_decode(mb_substr($item->description ?? '', 0, 201)));
        $response->link = (string)$item->link;
        $response->host = $item->link->getHost();
        $response->website_id = (string)$item->website_id;
        $response->language = $item->language;
        $response->date = $item->date->format(self::DATE_FORMAT);

        return $response;
    }
}
