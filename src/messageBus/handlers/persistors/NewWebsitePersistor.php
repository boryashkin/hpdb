<?php

namespace app\messageBus\handlers\persistors;

use app\exceptions\WebsiteAlreadyExists;
use app\messageBus\messages\crawlers\NewWebsiteToCrawlMessage;
use app\messageBus\messages\persistors\NewWebsiteToPersistMessage;
use app\messageBus\repositories\WebsiteRepository;
use app\models\Website;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Query;
use Symfony\Component\Messenger\MessageBusInterface;

class NewWebsitePersistor implements PersistorInterface
{
    /** @var string */
    private $name;
    /** @var WebsiteRepository */
    private $websiteRepository;
    /** @var MessageBusInterface */
    private $crawlerBus;

    public function __construct(string $name, WebsiteRepository $websiteRepository, MessageBusInterface $crawlerBus)
    {
        $this->name = $name;
        $this->websiteRepository = $websiteRepository;
        $this->crawlerBus = $crawlerBus;
    }

    public function __invoke(NewWebsiteToPersistMessage $message)
    {
        $website = new Website();
        $website->homepage = (string)$message->getUrl();
        if ($this->websiteRepository->getOneByHomepage($website->homepage)) {
            throw new WebsiteAlreadyExists($website->homepage);
        }

        if (!$this->websiteRepository->save($website)) {
            throw new \Exception('Failed to save a website: ' . $message->getUrl());
        }

        $message = new NewWebsiteToCrawlMessage(new ObjectId($website->_id), $message->getUrl());
        $this->crawlerBus->dispatch($message);
    }
}
