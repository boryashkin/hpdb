<?php

declare(strict_types=1);

namespace App\Common\MessageBus\Handlers\Processors;

use App\Common\Dto\Parsers\WebFeedItemDto;
use App\Common\MessageBus\Messages\Crawlers\WebFeedToCrawlMessage;
use App\Common\MessageBus\Messages\Persistors\RssFeedMetaInfoToPersist;
use App\Common\MessageBus\Messages\Persistors\RssItemToPersist;
use App\Common\MessageBus\Messages\Persistors\ScheduledMessageToPersistMessage;
use App\Common\MessageBus\Messages\Processors\XmlRssContentToProcessMessage;
use App\Common\Services\Parsers\XmlRssParserService;
use Symfony\Component\Messenger\MessageBusInterface;

class RssFeedProcessor implements ProcessorInterface
{
    private $name;
    /** @var MessageBusInterface */
    private $persistorsBus;
    private $xmlRssParser;

    public function __construct(string $name, MessageBusInterface $persistorsBus, XmlRssParserService $xmlRssParser)
    {
        $this->name = $name;
        $this->persistorsBus = $persistorsBus;
        $this->xmlRssParser = $xmlRssParser;
    }

    public function __invoke(XmlRssContentToProcessMessage $message)
    {
        $channel = $this->xmlRssParser->extractChannel($message->getContent());
        $items = $this->xmlRssParser->extractItems($message->getContent());

        $rssPubDate = $channel->pubDate ? new \DateTime($channel->pubDate) : null;
        if (!$rssPubDate && $channel->lastBuildDate) {
            $rssPubDate = new \DateTime($channel->lastBuildDate);
        }

        if (!$items) {
            throw new \Exception('rss is empty; websiteId: ' . $message->getWebsiteId());
        }

        foreach ($items as $item) {
            try {
                $pubDate = (string)$item->pubDate ? new \DateTime((string)$item->pubDate) : null;
            } catch (\Exception $e) {
                unset($e);
                $pubDate = null;
            }

            $itemMessage = new RssItemToPersist(
                $message->getWebsiteId(),
                $item->title,
                $item->description,
                $item->link,
                $pubDate
            );
            $this->persistorsBus->dispatch($itemMessage);
        }
        if (!$rssPubDate) {
            $rssPubDate = $pubDate ?? new \DateTime();
        } else {
            $diff = $rssPubDate->diff(new \DateTime());
            $messageToSchedule = new WebFeedToCrawlMessage(
                $message->getWebsiteId(),
                new WebFeedItemDto(WebFeedItemDto::TYPE_RSS, (string)$message->getUrl()),
                $message->getUrl()
            );
            if ($diff->days < 1) {
                $scheduleDate = new \DateTime('+ 3 hours');
            } elseif ($diff->days <= 3) {
                $scheduleDate = new \DateTime('+ 1 day');
            } elseif ($diff->days <= 7) {
                $scheduleDate = new \DateTime('+ 7 days');
            } else {
                $scheduleDate = new \DateTime('+ 30 days');
            }
            $scheduled = new ScheduledMessageToPersistMessage($messageToSchedule, $scheduleDate);
            $this->persistorsBus->dispatch($scheduled);
        }
        $messageMeta = new RssFeedMetaInfoToPersist(
            $message->getWebsiteId(),
            $rssPubDate,
            $message->getUrl(),
            new WebFeedItemDto(WebFeedItemDto::TYPE_RSS, (string)$message->getUrl())
        );
        $this->persistorsBus->dispatch($messageMeta);
    }
}
