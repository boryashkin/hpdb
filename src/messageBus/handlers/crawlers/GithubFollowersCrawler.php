<?php

namespace app\messageBus\handlers\crawlers;

use app\messageBus\messages\crawlers\GithubFollowersToCrawlMessage;
use app\messageBus\messages\processors\GithubFollowersToProcessMessage;
use app\services\website\WebsiteFetcher;
use Symfony\Component\Messenger\MessageBusInterface;
use DateTime;

class GithubFollowersCrawler implements CrawlerInterface
{
    /** @var string */
    private $name;
    /** @var WebsiteFetcher */
    private $fetcher;
    /** @var MessageBusInterface */
    private $processorsBus;

    public function __construct(string $name, WebsiteFetcher $fetcher, MessageBusInterface $processorsBus)
    {
        $this->name = $name;
        $this->fetcher = $fetcher;
        $this->processorsBus = $processorsBus;
    }

    public function __invoke(GithubFollowersToCrawlMessage $message)
    {
        $url = $message->getUrl();
        $result = $this->fetcher->parseWebsiteInUtf8($url);

        $message = new GithubFollowersToProcessMessage(
            $result,
            new DateTime()
        );
        $this->processorsBus->dispatch($message);
    }
}
