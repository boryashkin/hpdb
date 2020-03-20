<?php

declare(strict_types=1);

namespace App\Common\Repositories;

use App\Common\Dto\WebFeed\WebFeedItem;
use App\Common\Dto\WebFeed\WebFeedSearchQuery;

class WebFeedRepository extends AbstractElasticRepository
{
    public static function getIndex(): string
    {
        return 'website_rss_item';
    }

    /**
     * @param WebFeedSearchQuery $query
     * @return WebFeedItem[]
     */
    public function getSearchResults(WebFeedSearchQuery $query, bool $asArray = false): array
    {
        $rawResult = $this->getClient()->search($this->getFormattedQuery($query));
        $result = [];
        if (isset($rawResult['hits']['hits']) && $rawResult['hits']['hits']) {
            foreach ($rawResult['hits']['hits'] as $key => $value) {
                $result[$key] = new WebFeedItem($value['_source']);
                if ($asArray) {
                    $result[$key] = $result[$key]->toArray();
                }
            }
        }

        return $result;
    }

    private function getFormattedQuery(WebFeedSearchQuery $query): array
    {
        return [
            'index' => self::getIndex(),
            'from' => $query->getFrom(),
            'size' => $query->getSize(),
            'sort' => $query->getSort(),
        ];
    }
}
