<?php

namespace App\Common\MessageBus\Handlers\Persistors;

use App\Common\Exceptions\Github\UnableToSaveGithubProfile;
use App\Common\MessageBus\Messages\Crawlers\NewGithubProfileToCrawlMessage;
use App\Common\MessageBus\Messages\Persistors\NewGithubProfileToPersistMessage;
use App\Common\Services\Github\GithubProfileService;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Messenger\MessageBusInterface;

class NewGithubProfilePersistor implements PersistorInterface
{
    /** @var string */
    private $name;
    /** @var GithubProfileService */
    private $githubService;
    /** @var MessageBusInterface */
    private $crawlerBus;

    public function __construct(string $name, GithubProfileService $githubService, MessageBusInterface $crawlerBus)
    {
        $this->name = $name;
        $this->githubService = $githubService;
        $this->crawlerBus = $crawlerBus;
    }

    public function __invoke(NewGithubProfileToPersistMessage $message)
    {
        try {
            $profile = $this->githubService->upsertByLogin($message->getLogin(), $message->getContributorTo());
        } catch (UnableToSaveGithubProfile | \Exception $e) {
            throw new \Exception('Failed to save a github profile: ' . $message->getLogin());
        }

        $message = new NewGithubProfileToCrawlMessage(
            new ObjectId($profile->_id),
            $message->getLogin(),
            $message->getContributorTo(),
            $message->getRepo()
        );
        $this->crawlerBus->dispatch($message);
    }
}
