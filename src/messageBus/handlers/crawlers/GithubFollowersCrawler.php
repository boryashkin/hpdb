<?php

namespace app\messageBus\handlers\crawlers;

use app\messageBus\messages\crawlers\GithubFollowersToCrawlMessage;
use app\messageBus\messages\processors\GithubFollowersToProcessMessage;
use app\services\github\GithubApiFetcher;
use DateTime;
use Symfony\Component\Messenger\MessageBusInterface;

class GithubFollowersCrawler implements CrawlerInterface
{
    /** @var string */
    private $name;
    /** @var GithubApiFetcher */
    private $fetcher;
    /** @var MessageBusInterface */
    private $processorsBus;

    public function __construct(string $name, GithubApiFetcher $fetcher, MessageBusInterface $processorsBus)
    {
        $this->name = $name;
        $this->fetcher = $fetcher;
        $this->processorsBus = $processorsBus;
    }

    public function __invoke(GithubFollowersToCrawlMessage $message)
    {
        $url = $message->getUrl();
        $result = $this->fetcher->parseApi($url);

        $message = new GithubFollowersToProcessMessage(
            $result,
            new DateTime()
        );
        $this->processorsBus->dispatch($message);
    }
}
