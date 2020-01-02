<?php

namespace app\commands\daemons\crawlers;

/**
 * Downloading robots.txt and sitemap of discovered websites and building exploring routes
 */
class RouteExplorerCrawler implements CrawlerInterface
{
    private const ROBOTS_TXT_URL = '/robots.txt';
    private const SITEMAP_URLS = [
        '/sitemap.xml',
    ];

    public function __construct()
    {

    }
}
