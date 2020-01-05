<?php

namespace app\messageBus\handlers\crawlers;

use app\messageBus\messages\crawlers\NewWebsiteToCrawlMessage;
use app\messageBus\messages\persistors\WebsiteFetchedPageToPersistMessage;
use app\services\website\WebsiteFetcher;
use Symfony\Component\Messenger\MessageBusInterface;
use DateTime;

/**
 * Downloading planned websites and building exploring routes
 */
class PageFetcherCrawler implements CrawlerInterface
{
    /** @var string */
    private $name;
    /** @var WebsiteFetcher */
    private $fetcher;
    /** @var MessageBusInterface */
    private $persistorsBus;

    public function __construct(string $name, WebsiteFetcher $fetcher, MessageBusInterface $persistorsBus)
    {
        $this->name = $name;
        $this->fetcher = $fetcher;
        $this->persistorsBus = $persistorsBus;
    }

    public function __invoke(NewWebsiteToCrawlMessage $message)
    {
        $result = $this->fetcher->parseWebsiteInUtf8($message->getUrl());

        $message = new WebsiteFetchedPageToPersistMessage(
            $message->getWebsiteId(),
            $message->getUrl(),
            $result,
            new DateTime()
        );
        $this->persistorsBus->dispatch($message);
    }
}
