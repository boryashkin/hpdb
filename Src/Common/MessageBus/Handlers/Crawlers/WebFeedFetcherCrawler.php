<?php

namespace App\Common\MessageBus\Handlers\Crawlers;

use App\Common\Exceptions\WebFeed\UnknownWebFeedType;
use App\Common\MessageBus\Messages\Crawlers\WebFeedToCrawlMessage;
use App\Common\MessageBus\Messages\Processors\XmlAtomContentToProcessMessage;
use App\Common\MessageBus\Messages\Processors\XmlRssContentToProcessMessage;
use App\Common\Services\Website\WebsiteFetcher;
use Symfony\Component\Messenger\MessageBusInterface;

class WebFeedFetcherCrawler implements CrawlerInterface
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

    public function __invoke(WebFeedToCrawlMessage $message)
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
        if ($message->getFeed()->isRss()) {
            $message = new XmlRssContentToProcessMessage(
                $message->getWebsiteId(),
                $result->content,
                $message->getUrl()
            );
        } elseif ($message->getFeed()->isAtom()) {
            throw new UnknownWebFeedType($message->getFeed()->getType());
            //todo: доделать парсер
            $message = new XmlAtomContentToProcessMessage(
                $message->getWebsiteId(),
                $result->content
            );
        } else {
            throw new UnknownWebFeedType($message->getFeed()->getType());
        }
        $this->processorsBus->dispatch($message);
    }
}
