<?php

declare(strict_types=1);

namespace App\Common\MessageBus\Handlers\Persistors;

use App\Common\Dto\Website\WebsiteWebFeedEmbedded;
use App\Common\Exceptions\ProfileNotFoundException;
use App\Common\MessageBus\Messages\Persistors\RssFeedMetaInfoToPersist;
use App\Common\Services\Website\WebsiteService;

class RssFeedMetaInfoPersistor implements PersistorInterface
{
    private $name;
    private $websiteService;

    public function __construct(string $name, WebsiteService $websiteService)
    {
        $this->name = $name;
        $this->websiteService = $websiteService;
    }

    public function __invoke(RssFeedMetaInfoToPersist $message)
    {
        $website = $this->websiteService->getOneById($message->getWebsiteId());
        if (!$website) {
            throw new ProfileNotFoundException();
        }
        $dto = new WebsiteWebFeedEmbedded();
        $dto->type = $message->getFeedItemDto()->getType();
        $dto->url = (string)$message->getUrl();
        $dto->pub_date = $message->getPubDate();
        $dto->pub_date = $message->getPubDate();
        $this->websiteService->addWebFeedAndSave($website, $dto);
    }
}
