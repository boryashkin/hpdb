<?php

namespace App\Common\MessageBus\Handlers\Processors;

use App\Common\MessageBus\Messages\Crawlers\RssFeedToCrawlMessage;
use App\Common\MessageBus\Messages\Processors\WebsiteHistoryMessage;
use App\Common\ValueObjects\Url;
use MongoDB\BSON\ObjectId;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Messenger\MessageBusInterface;

class RssFeedSeekerProcessor implements ProcessorInterface
{
    /** @var MessageBusInterface */
    private $crawlersBus;
    private $name;

    public function __construct(string $name, MessageBusInterface $crawlersBus)
    {
        $this->name = $name;
        $this->crawlersBus = $crawlersBus;
    }

    public function __invoke(WebsiteHistoryMessage $historyMessage)
    {
        $this->sendRssToCrawlMessage(
            new ObjectId($historyMessage->getWebsiteId()),
            $historyMessage->getUrl(),
            $historyMessage->getInitialEncoding(),
            $historyMessage->getContent()
        );
    }

    private function sendRssToCrawlMessage(
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
        $crawler = new Crawler($content);
        $links = $crawler->filter('link[type*="/rss"], link[type*="/atom"]')->extract(['type', 'href']);
        foreach ($links as $link) {
            if (\substr($link[1], 0, 4) !== 'http') {
                $url->addPath($link[1]);
            } else {
                $url = new Url($link[1]);
            }
            $message = new RssFeedToCrawlMessage($websiteId, $url);
            $this->crawlersBus->dispatch($message);
        }
    }
}
