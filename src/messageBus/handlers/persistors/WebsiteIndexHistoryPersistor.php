<?php

namespace app\messageBus\handlers\persistors;

use app\messageBus\messages\persistors\WebsiteFetchedPageToPersistMessage;
use app\messageBus\messages\processors\WebsiteHistoryMessage;
use app\messageBus\repositories\WebsiteIndexHistoryRepository;
use app\models\WebsiteIndexHistory;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Messenger\MessageBusInterface;

class WebsiteIndexHistoryPersistor implements PersistorInterface
{
    /** @var string */
    private $name;
    /** @var WebsiteIndexHistoryRepository */
    private $websiteIndexRepository;
    /** @var MessageBusInterface */
    private $processorsBus;

    public function __construct(
        string $name,
        WebsiteIndexHistoryRepository $websiteRepository,
        MessageBusInterface $processorsBus
    )
    {
        $this->name = $name;
        $this->websiteIndexRepository = $websiteRepository;
        $this->processorsBus = $processorsBus;
    }

    public function __invoke(WebsiteFetchedPageToPersistMessage $message)
    {
        $websiteIndex = new WebsiteIndexHistory();
        $websiteIndex->website_id = $message->getWebsiteId();
        $websiteIndex->initial_encoding = $message->getData()->initialEncoding;
        $websiteIndex->content = $message->getData()->content;
        $websiteIndex->http_headers = $message->getData()->httpHeaders;
        $websiteIndex->http_status = $message->getData()->httpStatus;
        $websiteIndex->available = $message->getData()->available;
        $websiteIndex->redirects = $message->getData()->redirects;
        $websiteIndex->time = $message->getData()->time;


        if (!$this->websiteIndexRepository->save($websiteIndex)) {
            throw new \Exception('Failed to save a website: ' . $message->getUrl());
        }

        $processMessage = new WebsiteHistoryMessage(
            $message->getWebsiteId(),
            new ObjectId($websiteIndex->_id),
            $message->getData()->content,
            $message->getData()->initialEncoding
        );
        $this->processorsBus->dispatch($processMessage);
    }
}
