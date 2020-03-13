<?php

namespace App\Common\MessageBus\Handlers\Persistors;

use App\Common\MessageBus\Messages\Persistors\GithubProfileRepoMetaForGroupToPersistMessage;
use App\Common\Services\Website\WebsiteGroupService;

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
