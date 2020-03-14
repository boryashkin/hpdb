<?php

namespace App\Common\MessageBus\Handlers\Crawlers;

use App\Common\Exceptions\Github\GithubContributorsPollingException;
use App\Common\MessageBus\Messages\Crawlers\GithubContributorsToCrawlMessage;
use App\Common\MessageBus\Messages\Persistors\ScheduledMessageToPersistMessage;
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
    /** @var MessageBusInterface */
    private $persistorsBus;

    public function __construct(
        string $name,
        GithubApiFetcher $fetcher,
        MessageBusInterface $processorsBus,
        MessageBusInterface $persistorsBus
    )
    {
        $this->name = $name;
        $this->fetcher = $fetcher;
        $this->processorsBus = $processorsBus;
        $this->persistorsBus = $persistorsBus;
    }

    public function __invoke(GithubContributorsToCrawlMessage $message)
    {
        $url = new Url($message->getContributorsUrl());
        $result = $this->fetcher->parseApiAsAjax($url);
        if ($result->httpStatus === 202 && !$result->content) {
            $this->persistorsBus->dispatch(
                new ScheduledMessageToPersistMessage($message, new DateTime('+ 5 min'))
            );

            throw new GithubContributorsPollingException('Scheduled a new message ' . $message->getContributorsUrl());
        }

        $message = new GithubContributorsToProcessMessage(
            $message->getRepo(),
            $result,
            new DateTime()
        );
        $this->processorsBus->dispatch($message);
    }
}
