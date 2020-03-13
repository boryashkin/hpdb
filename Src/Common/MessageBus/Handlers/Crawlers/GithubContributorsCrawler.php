<?php

namespace App\Common\MessageBus\Handlers\Crawlers;

use App\Common\MessageBus\Messages\Crawlers\GithubContributorsToCrawlMessage;
use App\Common\MessageBus\Messages\Processors\GithubContributorsToProcessMessage;
use App\Common\Services\Github\GithubApiFetcher;
use App\Common\ValueObjects\Url;
use DateTime;
use Symfony\Component\Messenger\MessageBusInterface;

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
