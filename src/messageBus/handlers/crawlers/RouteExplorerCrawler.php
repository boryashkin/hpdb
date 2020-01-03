<?php

namespace app\messageBus\handlers\crawlers;

/**
 * Downloading robots.txt and sitemap of discovered websites and building exploring routes
 */
class RouteExplorerCrawler implements CrawlerInterface
{
    private const ROBOTS_TXT_URL = '/robots.txt';
    private const SITEMAP_URLS = [
        '/sitemap.xml',
    ];

    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __invoke()
    {
        echo 'ROUTE EXPLORED!' . PHP_EOL;
    }
}
