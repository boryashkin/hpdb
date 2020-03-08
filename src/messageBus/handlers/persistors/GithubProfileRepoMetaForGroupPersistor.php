<?php

namespace app\messageBus\handlers\persistors;

use app\messageBus\messages\persistors\GithubProfileRepoMetaForGroupToPersistMessage;
use app\services\website\WebsiteGroupService;

class GithubProfileRepoMetaForGroupPersistor implements PersistorInterface
{
    /** @var string */
    private $name;
    /** @var WebsiteGroupService */
    private $groupService;

    public function __construct(string $name, WebsiteGroupService $groupService)
    {
        $this->name = $name;
        $this->groupService = $groupService;
    }

    public function __invoke(GithubProfileRepoMetaForGroupToPersistMessage $message)
    {
        $slug = $this->groupService->getSlugByGithubRepo($message->getRepo());
        $group = $this->groupService->getGroupBySlug($slug, false);
        if ($group) {
            if ($message->getAvatarUrl()) {
                $group->logo = (string)$message->getAvatarUrl();
            }
            if ($message->getBio()) {
                $group->description = $message->getBio();
            }
            $this->groupService->save($group);
        }
    }
}
