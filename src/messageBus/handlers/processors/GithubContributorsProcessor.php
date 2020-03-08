<?php

namespace app\messageBus\handlers\processors;

use app\dto\github\GithubContributorDto;
use app\exceptions\GithubContributorsPollingException;
use app\exceptions\UnexpectedContentException;
use app\messageBus\messages\persistors\NewGithubProfileToPersistMessage;
use app\messageBus\messages\processors\GithubContributorsToProcessMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class GithubContributorsProcessor implements ProcessorInterface
{
    /** @var MessageBusInterface */
    private $persistorsBus;
    private $name;

    public function __construct(string $name, MessageBusInterface $persistorsBus)
    {
        $this->name = $name;
        $this->persistorsBus = $persistorsBus;
    }

    public function __invoke(GithubContributorsToProcessMessage $message)
    {
        if ($message->getIndexDto()->httpStatus === 202) {
            //todo: make a deferred task to repeat crawling
            throw new GithubContributorsPollingException((string)$message->getRepo());
        }
        $contributors = \json_decode($message->getIndexDto()->content, true);
        if (!is_array($contributors)) {
            throw new UnexpectedContentException(substr($message->getIndexDto()->content, 0, 100));
        }
        foreach ($contributors as $contributorArr) {
            $contributor = new GithubContributorDto($contributorArr);
            $messageToPersist = new NewGithubProfileToPersistMessage(
                $contributor->author->login,
                new \DateTime(),
                $message->getRepo(),
                null
            );
            $this->persistorsBus->dispatch($messageToPersist);
        }
    }
}
