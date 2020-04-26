<?php

declare(strict_types=1);

namespace App\Admin\Category\Services\Website;

use App\Admin\Category\Dto\AdminUserDataDto;
use App\Admin\Category\Dto\WebsiteDto;
use App\Admin\Category\Entities\WebsiteCategoryMatch;
use App\Common\Helpers\Cache\CacheItemFactory;
use App\Common\Models\Website;
use App\Common\Repositories\WebsiteIndexHistoryRepository;
use App\Common\Repositories\WebsiteRepository;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\CacheItem;

class CategoryService
{
    private $redis;
    private $websiteRepository;
    private $websiteIndexRepository;

    public function __construct(
        WebsiteRepository $websiteRepository,
        WebsiteIndexHistoryRepository $websiteIndexRepository,
        RedisAdapter $redis
    )
    {
        $this->websiteRepository = $websiteRepository;
        $this->websiteIndexRepository = $websiteIndexRepository;
        $this->redis = $redis;
    }

    /**
     * @param ObjectId|null $fromId
     * @param int $count
     * @return WebsiteDto[]
     */
    public function getWebsites(ObjectId $fromId = null, int $count = 10): array
    {
        $cursor = $this->websiteRepository->getAllCursor($fromId, SORT_DESC);
        $websites = [];
        /** @var Website $website */
        foreach ($cursor as $website) {
            if ($count-- <= 0) {
                break;
            }
            $websites[(string)$website->_id] = $this->getWebsiteById(new ObjectId($website->_id));
        }

        return $websites;
    }

    public function getWebsiteById(ObjectId $id): WebsiteDto
    {
        $website = $this->websiteRepository->getOne($id);
        $history = $this->websiteIndexRepository->getOne($website->content['from_website_index_history_id']);

        $dto = new WebsiteDto();
        $dto->id = (string)$website->_id;
        $dto->homepage = $website->homepage;
        $dto->content = $history->content;

        return $dto;
    }

    public function addWebsiteCategoryMatch(AdminUserDataDto $userData, ObjectId $websiteId, int $categoryCode): WebsiteCategoryMatch
    {
        $catMatch = new WebsiteCategoryMatch();
        $catMatch->user_hash = $userData->getUserHash();
        $catMatch->voter_ip = $userData->getUserIp();
        $catMatch->category_code = $categoryCode;
        $catMatch->website_id = $websiteId;

        $this->save($catMatch);

        return $catMatch;
    }

    public function getLastWebsiteId(AdminUserDataDto $userData): ?ObjectId
    {
        $item = $this->redis->getItem('w_c_' . $userData->getUserHash());
        if (!$item) {
            return null;
        }

        return new ObjectId($item->get());
    }

    public function saveUserHashLastWebsiteId(AdminUserDataDto $userData, ?ObjectId $id = null): bool
    {
        if (!$id) {
            $this->redis->delete('w_c_' . $userData->getUserHash());

            return true;
        }
        $item = CacheItemFactory::createCacheItem('w_c_' . $userData->getUserHash());
        $item->set((string)$id);

        return $this->redis->save($item);
    }

    private function save(WebsiteCategoryMatch $categoryMatch): bool
    {
        return $categoryMatch->save();
    }

}
