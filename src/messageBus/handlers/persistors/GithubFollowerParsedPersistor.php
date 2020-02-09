<?php

namespace app\messageBus\handlers\persistors;

use app\messageBus\messages\persistors\GithubFollowerParsedToPersistMessage;
use app\messageBus\repositories\GithubProfileRepository;
use app\models\GithubProfile;

class GithubFollowerParsedPersistor implements PersistorInterface
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

    public function __invoke(GithubFollowerParsedToPersistMessage $message)
    {
        $profile = new GithubProfile();
        $profile->fill((array)$message->getDto());
        $profile->parsing_status = GithubProfile::PARSING_STATUS_NEW;

        if (!$this->githubProfileRepository->save($profile)) {
            throw new \Exception('Failed to save a github follower profile: ' . $message->getDto()->login);
        }
    }
}
