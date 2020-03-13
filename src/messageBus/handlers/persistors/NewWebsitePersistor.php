<?php

namespace app\messageBus\handlers\persistors;

use app\messageBus\messages\crawlers\NewWebsiteToCrawlMessage;
use app\messageBus\messages\persistors\NewWebsiteToPersistMessage;
use app\messageBus\repositories\WebsiteRepository;
use app\models\Website;
use app\modules\web\ProfileRepository;
use app\services\github\GithubProfileService;
use app\services\website\WebsiteGroupService;
use app\valueObjects\GithubRepo;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Messenger\MessageBusInterface;

class NewWebsitePersistor implements PersistorInterface
{
    /** @var string */
    private $name;
    /** @var ProfileRepository */
    private $websiteRepository;
    /** @var MessageBusInterface */
    private $crawlerBus;
    /** @var GithubProfileService */
    private $githubProfileService;
    /** @var WebsiteGroupService */
    private $groupService;

    public function __construct(
        string $name,
        ProfileRepository $websiteRepository,
        MessageBusInterface $crawlerBus,
        GithubProfileService $githubProfileService,
        WebsiteGroupService $groupService
    )
    {
        $this->name = $name;
        $this->websiteRepository = $websiteRepository;
        $this->crawlerBus = $crawlerBus;
        $this->githubProfileService = $githubProfileService;
        $this->groupService = $groupService;
    }

    public function __invoke(NewWebsiteToPersistMessage $message)
    {
        $website = $this->websiteRepository->getFirstOneByUrl($message->getUrl());
        $groups = [];
        if ($message->getGithubProfileId()) {
            $github = $this->githubProfileService->getOneById($message->getGithubProfileId());
            foreach ($github->contributor_to ?? [] as $repoName) {
                $repo = GithubRepo::createByRepoString($repoName);
                $group = $this->groupService->getGroupBySlug(
                    $this->groupService->getSlugByGithubRepo($repo)
                );
                if ($group) {
                    $groups[] = new ObjectId($group->_id);
                }
            }
        }
        if ($website) {
            foreach ($groups as $groupId) {
                WebsiteRepository::addGroupIdAndSave($website, $groupId);
            }
        } else {
            $website = new Website();
            $website->homepage = (string)$message->getUrl();
            $website->github_profile_id = $message->getGithubProfileId();
            $website->groups = $groups;

            if (!$this->websiteRepository->save($website)) {
                throw new \Exception('Failed to save a website: ' . $message->getUrl());
            }
        }

        $message = new NewWebsiteToCrawlMessage(new ObjectId($website->_id), $message->getUrl());
        $this->crawlerBus->dispatch($message);
    }
}
