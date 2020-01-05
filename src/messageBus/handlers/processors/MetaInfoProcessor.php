<?php

namespace app\messageBus\handlers\processors;

use app\messageBus\messages\persistors\WebsiteMetaInfoMessage;
use app\messageBus\messages\processors\WebsiteHistoryMessage;
use MongoDB\BSON\ObjectId;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Extracting title, description and og fields
 */
class MetaInfoProcessor implements ProcessorInterface
{
    /** @var MessageBusInterface */
    private $persistorsBus;
    private $name;

    public function __construct(string $name, MessageBusInterface $persistorsBus)
    {
        $this->name = $name;
        $this->persistorsBus = $persistorsBus;
    }

    public function __invoke(WebsiteHistoryMessage $historyMessage)
    {
        $message = $this->createMetaInfoMessage(new ObjectId($historyMessage->getWebsiteId()), new ObjectId($historyMessage->getIndexHistoryId()), $historyMessage->getContent());
        $this->persistorsBus->dispatch($message);
    }

    private function createMetaInfoMessage(ObjectId $websiteId, ObjectId $historyIndexId, string $content): WebsiteMetaInfoMessage
    {
        $crawler = new Crawler($content);
        $titlePath = $crawler->filterXPath('//title');
        $title = null;
        $description = null;
        if ($titlePath->count()) {
            $title = $titlePath->text();
        }
        foreach ($crawler->filterXPath("//meta[@name='description']/@content") as $t) {
            $description = $t->textContent;
        }

        return new WebsiteMetaInfoMessage($websiteId, $historyIndexId, $title, $description);
    }
}
