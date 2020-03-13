<?php

namespace app\messageBus\handlers\crawlers;

use app\messageBus\messages\crawlers\RssFeedToCrawlMessage;
use app\messageBus\messages\processors\XmlRssContentToProcessMessage;
use app\services\website\WebsiteFetcher;
use Symfony\Component\Messenger\MessageBusInterface;

class RssFeedFetcherCrawler implements CrawlerInterface
{
    /** @var string */
    private $name;
    /** @var WebsiteFetcher */
    private $fetcher;
    /** @var MessageBusInterface */
    private $processorsBus;

    public function __construct(string $name, WebsiteFetcher $fetcher, MessageBusInterface $processorsBus)
    {
        $this->name = $name;
        $this->fetcher = $fetcher;
        $this->processorsBus = $processorsBus;
    }

    public function __invoke(RssFeedToCrawlMessage $message)
    {
        $result = $this->fetcher->parseWebsiteInUtf8($message->getUrl());

        $firstEncodingPos = stripos($result->content, $result->initialEncoding);
        if ($firstEncodingPos !== false) {
            $posOfEncodingProperty = $firstEncodingPos - 10;
            if (strcasecmp(substr($result->content, $posOfEncodingProperty, 9), 'encoding=') === 0) {
                $result->content = substr($result->content, 0, $firstEncodingPos)
                    . 'UTF-8'
                    . substr($result->content, $firstEncodingPos + strlen($result->initialEncoding));
            }
        }
        $message = new XmlRssContentToProcessMessage(
            $message->getWebsiteId(),
            $result->content
        );
        $this->processorsBus->dispatch($message);
    }
}
