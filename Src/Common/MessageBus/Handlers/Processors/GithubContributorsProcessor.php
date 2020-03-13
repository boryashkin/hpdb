<?php

namespace App\Common\MessageBus\Handlers\Processors;

use App\Common\Dto\Github\GithubContributorDto;
use App\Common\Exceptions\Github\GithubContributorsPollingException;
use App\Common\Exceptions\UnexpectedContentException;
use App\Common\MessageBus\Messages\Persistors\NewGithubProfileToPersistMessage;
use App\Common\MessageBus\Messages\Processors\GithubContributorsToProcessMessage;
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
