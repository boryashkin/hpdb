<?php

namespace app\messageBus\handlers\crawlers;

use app\messageBus\messages\crawlers\WebsiteMessage;
use app\messageBus\messages\discoverers\GithubProfileMessage;

/**
 * Downloading planned websites and building exploring routes
 */
class PageFetcherCrawler implements CrawlerInterface
{
    /** @var string */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __invoke(GithubProfileMessage $message)
    {
        echo 'PAGE FETCHED!' . PHP_EOL;
    }
}
