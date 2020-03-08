<?php

namespace app\messageBus\handlers\crawlers;

use app\messageBus\messages\crawlers\GithubContributorsToCrawlMessage;
use app\messageBus\messages\processors\GithubContributorsToProcessMessage;
use app\services\github\GithubApiFetcher;
use app\valueObjects\Url;
use Symfony\Component\Messenger\MessageBusInterface;
use DateTime;

class GithubContributorsCrawler implements CrawlerInterface
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

    public function __invoke(GithubContributorsToCrawlMessage $message)
    {
        //todo: X-GitHub-Request-Id
        $url = new Url($message->getContributorsUrl());
        $result = $this->fetcher->parseApiAsAjax($url);

        $message = new GithubContributorsToProcessMessage(
            $message->getRepo(),
            $result,
            new DateTime()
        );
        $this->processorsBus->dispatch($message);
    }
}
