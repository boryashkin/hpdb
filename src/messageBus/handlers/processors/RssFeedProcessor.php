<?php

namespace app\messageBus\handlers\processors;

use app\messageBus\messages\persistors\RssItemToPersist;
use app\messageBus\messages\processors\XmlRssContentToProcessMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class RssFeedProcessor implements ProcessorInterface
{
    private $name;
    /** @var MessageBusInterface */
    private $persistorsBus;

    public function __construct(string $name, MessageBusInterface $persistorsBus)
    {
        $this->name = $name;
        $this->persistorsBus = $persistorsBus;
        libxml_disable_entity_loader(true);
    }

    public function __invoke(XmlRssContentToProcessMessage $message)
    {
        $xml = simplexml_load_string(trim($message->getContent()), 'SimpleXMLElement', LIBXML_NOCDATA);
        $items = $xml->xpath('//item');
        if (!$items) {
            $items = $xml->xpath('//entry');
        }
        if (!$items) {
            throw new \Exception('rss is empty; websiteId: ' . $message->getWebsiteId());
        }

        foreach ($items as $item) {
            //todo: explore all existing attributes to know what to expect
            //$keys = array_keys((array)$item);

            $title = (string)$item->title ?: null;
            //todo: clear from possible html tags
            $description = (string)$item->description ?: null;
            $link = (string)$item->link ?: null;

            try {
                $pubDate = (string)$item->pubDate ? new \DateTime((string)$item->pubDate) : null;
            } catch (\Exception $e) {
                unset($e);
                $pubDate = null;
            }

            $itemMessage = new RssItemToPersist($message->getWebsiteId(), $title, $description, $link, $pubDate);
            $this->persistorsBus->dispatch($itemMessage);
        }
    }
}
