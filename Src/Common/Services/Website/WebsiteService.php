<?php

declare(strict_types=1);

namespace App\Common\Services\Website;

use App\Common\Dto\Website\WebsiteWebFeedEmbedded;
use App\Common\Models\Website;
use App\Common\Repositories\ProfileRepository;
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
    public function addWebFeedIdAndSave(Website $profile, WebsiteWebFeedEmbedded $webFeedDto): bool
    {
        if ($webFeedDto->pub_date instanceof \DateTimeInterface) {
            $webFeedDto->pub_date = new UTCDateTime($webFeedDto->pub_date->getTimestamp() * 1000);
        }
        $saved = false;
        if (!$profile->web_feeds || (\is_array($profile->web_feeds) && !\in_array($webFeedDto, $profile->web_feeds))) {
            $profile->web_feeds = \array_merge($profile->web_feeds ?? [], [$webFeedDto]);
            $saved = $profile->update(['web_feeds' => $profile->web_feeds]);
        }

        return $saved;
    }

    public function getOneById(ObjectId $id): ?Website
    {
        return $this->websiteRepository->getOneById($id);
    }
}
