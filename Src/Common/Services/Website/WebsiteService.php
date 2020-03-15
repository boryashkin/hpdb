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
}
