<?php

namespace app\messageBus\handlers\persistors;

use app\messageBus\messages\crawlers\NewGithubProfileToCrawlMessage;
use app\messageBus\messages\persistors\NewGithubProfileToPersistMessage;
use app\messageBus\repositories\GithubProfileRepository;
use app\models\GithubProfile;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Messenger\MessageBusInterface;

class NewGithubProfilePersistor implements PersistorInterface
{
    /** @var string */
    private $name;
    /** @var GithubProfileRepository */
    private $githubProfileRepository;
    /** @var MessageBusInterface */
    private $crawlerBus;

    public function __construct(string $name, GithubProfileRepository $websiteRepository, MessageBusInterface $crawlerBus)
    {
        $this->name = $name;
        $this->githubProfileRepository = $websiteRepository;
        $this->crawlerBus = $crawlerBus;
    }

    public function __invoke(NewGithubProfileToPersistMessage $message)
    {
        $profile = new GithubProfile();
        $profile->login = (string)$message->getLogin();

        if (!$this->githubProfileRepository->save($profile)) {
            throw new \Exception('Failed to save a github profile: ' . $message->getLogin());
        }

        $message = new NewGithubProfileToCrawlMessage(new ObjectId($profile->_id), $message->getLogin());
        $this->crawlerBus->dispatch($message);
    }
}
