<?php

namespace app\messageBus\handlers\persistors;

use app\messageBus\messages\persistors\GithubProfileParsedToPersistMessage;
use app\messageBus\repositories\GithubProfileRepository;
use Symfony\Component\Messenger\MessageBusInterface;

class GithubProfileParsedPersistor implements PersistorInterface
{
    /** @var string */
    private $name;
    /** @var GithubProfileRepository */
    private $githubProfileRepository;

    public function __construct(string $name, GithubProfileRepository $githubProfileRepository)
    {
        $this->name = $name;
        $this->githubProfileRepository = $githubProfileRepository;
    }

    public function __invoke(GithubProfileParsedToPersistMessage $message)
    {
        $profile = $this->githubProfileRepository->getOne($message->getGithubProfileId());
        $profile->fill((array)$message->getDto());

        if (!$this->githubProfileRepository->save($profile)) {
            throw new \Exception('Failed to save a github profile: ' . $message->getDto()->login);
        }
    }
}
