<?php

namespace App\Common\MessageBus\Handlers\Persistors;

use App\Common\MessageBus\Messages\Crawlers\NewWebsiteToCrawlMessage;
use App\Common\MessageBus\Messages\Persistors\NewWebsiteToPersistMessage;
use App\Common\Repositories\WebsiteRepository;
use App\Common\Models\Website;
use App\Common\Repositories\ProfileRepository;
use App\Common\Services\Github\GithubProfileService;
use App\Common\Services\Website\WebsiteGroupService;
use App\Common\ValueObjects\GithubRepo;
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
            $website->homepage = $message->getUrl()->getNormalized();
            $website->scheme = $message->getUrl()->getScheme();
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
