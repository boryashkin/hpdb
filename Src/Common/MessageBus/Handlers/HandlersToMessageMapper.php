<?php

declare(strict_types=1);

namespace App\Common\MessageBus\Handlers;

use App\Common\MessageBus\Messages\Crawlers\GithubContributorsToCrawlMessage;
use App\Common\MessageBus\Messages\Crawlers\GithubFollowersToCrawlMessage;
use App\Common\MessageBus\Messages\Crawlers\NewGithubProfileToCrawlMessage;
use App\Common\MessageBus\Messages\Crawlers\NewWebsiteToCrawlMessage;
use App\Common\MessageBus\Messages\Crawlers\RssFeedToCrawlMessage;
use App\Common\MessageBus\Messages\Discoverers\GithubProfileMessage;
use App\Common\MessageBus\Messages\Persistors\GithubFollowerParsedToPersistMessage;
use App\Common\MessageBus\Messages\Persistors\GithubProfileParsedToPersistMessage;
use App\Common\MessageBus\Messages\Persistors\GithubProfileRepoMetaForGroupToPersistMessage;
use App\Common\MessageBus\Messages\Persistors\NewGithubProfileToPersistMessage;
use App\Common\MessageBus\Messages\Persistors\NewWebsiteToPersistMessage;
use App\Common\MessageBus\Messages\Persistors\RssItemToPersist;
use App\Common\MessageBus\Messages\Persistors\ScheduledMessageToPersistMessage;
use App\Common\MessageBus\Messages\Persistors\WebsiteFetchedPageToPersistMessage;
use App\Common\MessageBus\Messages\Persistors\WebsiteMetaInfoMessage;
use App\Common\MessageBus\Messages\Processors\GithubContributorsToProcessMessage;
use App\Common\MessageBus\Messages\Processors\GithubFollowersToProcessMessage;
use App\Common\MessageBus\Messages\Processors\GithubProfileParsedToProcessMessage;
use App\Common\MessageBus\Messages\Processors\WebsiteHistoryMessage;
use App\Common\MessageBus\Messages\Processors\XmlRssContentToProcessMessage;

class HandlersToMessageMapper
{
    public function getCrawlersMessages(): array
    {
        return [
            GithubContributorsToCrawlMessage::class,
            GithubFollowersToCrawlMessage::class,
            NewGithubProfileToCrawlMessage::class,
            NewWebsiteToCrawlMessage::class,
            RssFeedToCrawlMessage::class,
        ];
    }

    public function getDiscoverersMessages(): array
    {
        return [
            GithubProfileMessage::class,
        ];
    }

    public function getPersistorsMessages(): array
    {
        return [
            GithubFollowerParsedToPersistMessage::class,
            GithubProfileParsedToPersistMessage::class,
            GithubProfileRepoMetaForGroupToPersistMessage::class,
            NewGithubProfileToPersistMessage::class,
            NewWebsiteToPersistMessage::class,
            RssItemToPersist::class,
            ScheduledMessageToPersistMessage::class,
            WebsiteFetchedPageToPersistMessage::class,
            WebsiteMetaInfoMessage::class,
        ];
    }

    public function getProcessorsMessages(): array
    {
        return [
            GithubContributorsToProcessMessage::class,
            GithubFollowersToProcessMessage::class,
            GithubProfileParsedToProcessMessage::class,
            WebsiteHistoryMessage::class,
            XmlRssContentToProcessMessage::class,
        ];
    }
}
