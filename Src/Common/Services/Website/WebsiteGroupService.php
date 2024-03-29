<?php

declare(strict_types=1);

namespace App\Common\Services\Website;

use App\Common\Repositories\WebsiteGroupRepository;
use App\Common\Models\WebsiteGroup;
use App\Common\ValueObjects\GithubRepo;
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
            $group->is_deleted = false;
            $this->repository->save($group);
        }

        return $group;
    }

    public function getSlugByGithubRepo(GithubRepo $repo): string
    {
        return 'github-contributors-' . $repo->getProfile() . '-' . $repo->getName();
    }

    public function save(WebsiteGroup $group): bool
    {
        return $this->repository->save($group);
    }

    private function getNameByGithubRepo(GithubRepo $repo): string
    {
        return 'GitHub contributors to ' . $repo->getProfile() . '/' . $repo->getName();
    }
}
