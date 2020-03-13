<?php

namespace App\Common\MessageBus\Handlers\Crawlers;

use App\Common\MessageBus\Messages\Crawlers\NewGithubProfileToCrawlMessage;
use App\Common\MessageBus\Messages\Processors\GithubProfileParsedToProcessMessage;
use App\Common\Services\Github\GithubApiFetcher;
use App\Common\ValueObjects\Url;
use DateTime;
use Symfony\Component\Messenger\MessageBusInterface;

class GithubProfileCrawler implements CrawlerInterface
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

    public function __invoke(NewGithubProfileToCrawlMessage $message)
    {
        $login = $message->getLogin();
        $parsedUrl = new Url("https://api.github.com/users/{$login}");
        $result = $this->fetcher->parseApi($parsedUrl);

        $message = new GithubProfileParsedToProcessMessage(
            $message->getGithubProfileId(),
            $result,
            new DateTime(),
            $message->getContributedTo(),
            $message->getRepo()
        );
        $this->processorsBus->dispatch($message);
    }
}
