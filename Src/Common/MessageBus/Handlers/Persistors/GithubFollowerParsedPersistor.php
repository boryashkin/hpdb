<?php

namespace App\Common\MessageBus\Handlers\Persistors;

use App\Common\MessageBus\Messages\Persistors\GithubFollowerParsedToPersistMessage;
use App\Common\Repositories\GithubProfileRepository;
use App\Common\Models\GithubProfile;

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
        $profile = $this->githubProfileRepository->getOneByLogin($message->getDto()->login);
        if (!$profile) {
            $profile = new GithubProfile();
            $profile->fill((array)$message->getDto());
        }
        $profile->parsing_status = GithubProfile::PARSING_STATUS_NEW;

        if (!$this->githubProfileRepository->save($profile)) {
            throw new \Exception('Failed to save a github follower profile: ' . $message->getDto()->login);
        }
    }
}
