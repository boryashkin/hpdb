<?php

declare(strict_types=1);

namespace App\Common\Services\Parsers;

use App\Common\Dto\Parsers\WebFeedItemDto;
use Symfony\Component\DomCrawler\Crawler;

class HtmlParserService
{
    /**
     * @param string $htmlContent
     * @return WebFeedItemDto[]
     */
    public function extractWebFeeds(string $htmlContent): array
    {
        $crawler = new Crawler($htmlContent);

        $result = $crawler->filter('link[type*="/rss"], link[type*="/atom"]')->extract(['type', 'href']);
        $feeds = [];
        foreach ($result as $item) {
            $feeds[] = new WebFeedItemDto($item[0], $item[1]);
        }

        return $feeds;
    }
}
