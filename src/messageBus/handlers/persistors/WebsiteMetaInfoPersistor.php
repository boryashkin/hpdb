<?php

namespace app\messageBus\handlers\persistors;

use app\messageBus\messages\persistors\WebsiteMetaInfoMessage;
use app\models\WebsiteContent;
use Jenssegers\Mongodb\Connection;

class WebsiteMetaInfoPersistor implements PersistorInterface
{
    private $name;
    /** @var Connection */
    private $mongo;

    public function __construct(string $name, Connection $mongo)
    {
        $this->name = $name;
        $this->mongo = $mongo;
    }

    public function __invoke(WebsiteMetaInfoMessage $message)
    {
        $content = $this->createWebsiteContent($message);
        if (!$this->saveWebsiteContent($content)) {
            throw new \Exception('Failed to save website content. WebsiteId: ' . $message->getWebsiteId());
        }
    }

    public function createWebsiteContent(WebsiteMetaInfoMessage $message): WebsiteContent
    {
        $content = new WebsiteContent();
        $content->website_id = $message->getWebsiteId();
        $content->title = $message->getTitle();
        $content->description = $message->getDescription();
        $content->fromWebsite_index_history_id = $message->getHistoryIndexId();

        return $content;
    }

    public function saveWebsiteContent(WebsiteContent $content): bool
    {
        return $content->save();
    }
}
