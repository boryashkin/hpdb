<?php

namespace app\messageBus\handlers\crawlers;

use app\messageBus\messages\crawlers\NewGithubProfileToCrawlMessage;
use app\messageBus\messages\processors\GithubProfileParsedToProcessMessage;
use app\services\website\WebsiteFetcher;
use app\valueObjects\Url;
use Symfony\Component\Messenger\MessageBusInterface;
use DateTime;

class GithubProfileCrawler implements CrawlerInterface
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

    public function __invoke(NewGithubProfileToCrawlMessage $message)
    {
        $login = $message->getLogin();
        $parsedUrl = new Url("https://api.github.com/users/$login");
        $result = $this->fetcher->parseWebsiteInUtf8($parsedUrl);

        $message = new GithubProfileParsedToProcessMessage(
            $message->getGithubProfileId(),
            $result,
            new DateTime()
        );
        $this->processorsBus->dispatch($message);
    }
}
