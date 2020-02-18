<?php

namespace app\messageBus\handlers\persistors;

use app\messageBus\messages\persistors\WebsiteMetaInfoMessage;
use app\models\Website;
use app\modules\web\ProfileRepository;
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

    public function createWebsiteContent(WebsiteMetaInfoMessage $message): Website
    {
        $repo = new ProfileRepository($this->mongo);
        $profile = $repo->getOneById($message->getWebsiteId());
        if (!$profile->content) {
            $profile->content->title = $message->getTitle();
            $profile->content->description = $message->getDescription();
            $profile->content->from_website_index_history_id = $message->getHistoryIndexId();
        }

        return $profile;
    }

    public function saveWebsiteContent(Website $content): bool
    {
        return $content->save();
    }
}
