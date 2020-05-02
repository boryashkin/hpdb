<?php

declare(strict_types=1);

namespace App\Common\Services\Website;

use App\Common\Dto\Website\WebsiteReactionDto;
use App\Common\Dto\Website\WebsiteWebFeedEmbedded;
use App\Common\Models\Website;
use App\Common\Repositories\Filters\WebsiteFilter;
use App\Common\Repositories\ProfileRepository;
use Illuminate\Support\LazyCollection;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Exception\ServerException;

class WebsiteService
{
    /** @var ProfileRepository */
    private $websiteRepository;

    public function __construct(ProfileRepository $websiteRepository)
    {
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * @throws ServerException
     */
    public function addWebFeedAndSave(Website $profile, WebsiteWebFeedEmbedded $webFeedDto): bool
    {
        if ($webFeedDto->pub_date instanceof \DateTimeInterface) {
            $webFeedDto->pub_date = new UTCDateTime($webFeedDto->pub_date->getTimestamp() * 1000);
        }
        $added = false;
        if ($profile->web_feeds && \is_array($profile->web_feeds)) {
            $feeds = $profile->web_feeds;
            foreach ($feeds as $key => $feed) {
                if ($feed['url'] === $webFeedDto->url) {
                    $feeds[$key] = $webFeedDto;
                    $added = true;
                    $profile->web_feeds = $feeds;
                    break;
                }
            }
        }
        if (!$added) {
            $profile->web_feeds = array_merge($profile->web_feeds ?? [], [$webFeedDto]);
        }

        return $profile->update(['web_feeds' => $profile->web_feeds]);
    }

    public function getOneById(ObjectId $id): ?Website
    {
        return $this->websiteRepository->getOneById($id);
    }

    public function addReaction(Website $profile, WebsiteReactionDto $dto): bool
    {
        $reactions = $profile->reactions ?? [];
        if (isset($reactions[$dto->reaction])) {
            $reactions[$dto->reaction]++;
        } else {
            $reactions[$dto->reaction] = 1;
        }
        $profile->reactions = $reactions;

        return $this->websiteRepository->save($profile);
    }

    public function assignReactions(Website $profile, array $reactions): bool
    {
        $profile->reactions = $reactions;

        return $this->websiteRepository->save($profile);
    }

    public function getAllCursor(
        ?ObjectId $startingFromId = null,
        int $sortDirection = SORT_ASC,
        int $limit = null): LazyCollection
    {
        return $this->websiteRepository->getAllCursor($startingFromId, $sortDirection, $limit);
    }

    public function find(WebsiteFilter $websiteFilter): array
    {
        return $this->websiteRepository->find($websiteFilter);
    }
}
