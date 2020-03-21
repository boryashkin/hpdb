<?php

declare(strict_types=1);

namespace App\Common\Repositories;

use App\Common\Dto\WebFeed\WebFeedItem;
use App\Common\Dto\WebFeed\WebFeedSearchQuery;

class WebFeedRepository extends AbstractElasticRepository
{
    private const PIPELINE_LANG_DETECTION = 'langdetect-pipeline';

    public static function getIndex(): string
    {
        return 'web_feed_item';
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

    public function indexWithLanguageDetection(WebFeedItem $item): array
    {
        return $this->getClient()->index([
            'pipeline' => self::PIPELINE_LANG_DETECTION,
            'index' => self::getIndex(),
            'body' => [
                'title' => $item->title,
                'description' => $item->description,
                'date' => $item->date->format(DATE_ATOM),
                'link' => (string)$item->link,
                'website_id' => (string)$item->website_id,
            ],
        ]);
    }

    public function bulkIndex(): array
    {
        throw new \Exception('Not implemented yet');
    }

    private function getFormattedQuery(WebFeedSearchQuery $query): array
    {
        return [
            'index' => self::getIndex(),
            'from' => $query->getFrom(),
            'size' => $query->getSize(),
            'sort' => $query->getSort(),
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => $query->getFilter() ?? [],
                    ],
                ],
            ],
        ];
    }
}
