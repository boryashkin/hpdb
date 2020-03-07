<?php

declare(strict_types=1);

namespace app\services\website;

use app\messageBus\repositories\WebsiteGroupRepository;
use app\models\WebsiteGroup;
use app\valueObjects\GithubRepo;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class WebsiteGroupService
{
    /** @var WebsiteGroupRepository */
    private $repository;
    /** @var CacheInterface */
    private $cache;

    public function __construct(WebsiteGroupRepository $repository, CacheInterface $cache)
    {
        $this->repository = $repository;
        $this->cache = $cache;
    }

    public function getGroupBySlug(string $slug, bool $cached = true): ?WebsiteGroup
    {
        if ($cached) {
            $repo = $this->repository;
            $group = $this->cache->get('group_slug_' . $slug, function (ItemInterface $item) use ($repo, $slug) {
                $item->expiresAfter(600);

                return $repo->getOneBySlug($slug);
            });
            if ($group) {
                return $group;
            }
        }

        return $this->repository->getOneBySlug($slug);
    }

    public function createGroupByGithubRepo(GithubRepo $repo): WebsiteGroup
    {
        $slug = $this->getSlugByGithubRepo($repo);
        $group = $this->repository->getOneBySlug($slug);
        if (!$group) {
            $group = new WebsiteGroup();
            $group->slug = $slug;
            $group->name = $this->getNameByGithubRepo($repo);
            $group->show_on_main = false;
            $this->repository->save($group);
        }

        return $group;
    }

    public function getSlugByGithubRepo(GithubRepo $repo): string
    {
        return 'github-contributors-' . $repo->getProfile() . '-' . $repo->getName();
    }

    private function getNameByGithubRepo(GithubRepo $repo): string
    {
        return 'github contributors to ' . $repo->getProfile() . '/' . $repo->getName();
    }
}
