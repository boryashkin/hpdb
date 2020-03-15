<?php

namespace App\Common\MessageBus\Handlers\Processors;

use App\Common\MessageBus\Messages\Crawlers\WebFeedToCrawlMessage;
use App\Common\MessageBus\Messages\Processors\WebsiteHistoryMessage;
use App\Common\Services\Parsers\HtmlParserService;
use App\Common\ValueObjects\Url;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Messenger\MessageBusInterface;

class WebFeedSeekerProcessor implements ProcessorInterface
{
    /** @var MessageBusInterface */
    private $crawlersBus;
    private $name;
    private $parserService;

    public function __construct(string $name, MessageBusInterface $crawlersBus, HtmlParserService $parserService)
    {
        $this->name = $name;
        $this->crawlersBus = $crawlersBus;
        $this->parserService = $parserService;
    }

    public function __invoke(WebsiteHistoryMessage $historyMessage)
    {
        $this->sendFeedToCrawlMessage(
            new ObjectId($historyMessage->getWebsiteId()),
            $historyMessage->getUrl(),
            $historyMessage->getInitialEncoding(),
            $historyMessage->getContent()
        );
    }

    private function sendFeedToCrawlMessage(
        ObjectId $websiteId,
        Url $url,
        ?string $initialEncoding,
        string $content
    ): void
    {
        if ($initialEncoding) {
            $content = \iconv('UTF-8', $initialEncoding, $content);
            //awful hack to handle the Crawler encoding conversions based on an absence of meta-charset
            if (!preg_match('/\<meta[^\>]+charset *= *["\']?([a-zA-Z\-0-9_:.]+)/i', $content, $matches)) {
                $pos = stripos($content, '<head>');
                if ($pos !== false) {
                    $endOfHeadTag = $pos + 6;
                    $content = substr($content, 0, $endOfHeadTag)
                        . "<meta charset=\"{$initialEncoding}\">"
                        . substr($content, $endOfHeadTag);
                }
            }
        }
        $feeds = $this->parserService->extractWebFeeds($content);
        foreach ($feeds as $feed) {
            $feedUrl = clone $url;
            if (\substr($feed->getUrl(), 0, 4) !== 'http') {
                $feedUrl->addPath($feed->getUrl());
            } else {
                $feedUrl = new Url($feed->getUrl());
            }
            $message = new WebFeedToCrawlMessage($websiteId, $feed, $feedUrl);
            $this->crawlersBus->dispatch($message);
        }
    }
}
