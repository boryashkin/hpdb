<?php

namespace app\messageBus\handlers\processors;

use app\messageBus\messages\persistors\WebsiteMetaInfoMessage;
use app\messageBus\messages\processors\WebsiteHistoryMessage;
use app\messageBus\repositories\WebsiteIndexHistoryRepository;
use app\models\WebsiteIndexHistory;
use MongoDB\BSON\ObjectId;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Extracting title, description and og fields
 */
class MetaInfoProcessor implements ProcessorInterface
{
    /** @var WebsiteIndexHistoryRepository */
    private $historyRepository;
    /** @var MessageBusInterface */
    private $persistorsBus;
    private $name;

    public function __construct(
        string $name,
        WebsiteIndexHistoryRepository $historyRepository,
        MessageBusInterface $persistorsBus
    )
    {
        $this->name = $name;
        $this->historyRepository = $historyRepository;
        $this->persistorsBus = $persistorsBus;
    }

    public function __invoke(WebsiteHistoryMessage $historyMessage)
    {
        $item = $this->getHistoryIndexItem($historyMessage->getIndexHistoryId());
        if ($item->content === null) {
            return;
        }
        $message = $this->createMetaInfoMessage(new ObjectId($item->website_id), new ObjectId($item->_id), $item->content);
        $this->persistorsBus->dispatch($message);
    }

    public function getHistoryIndexItem(ObjectId $historyItem): ?WebsiteIndexHistory
    {
        return $this->historyRepository->getOne($historyItem);
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
