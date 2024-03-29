<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Profile\Builders;

use App\Common\Models\Website;
use App\Web\Api\V1\Profile\Responses\WebsiteResponse;

class WebsiteResponseBuilder
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public function createOne(Website $website): WebsiteResponse
    {
        $response = new WebsiteResponse();
        $response->id = (string)$website->_id;
        $response->homepage = $website->homepage;
        $response->title = $website->content['title'] ?? null;
        $response->description = $website->content['description'] ?? null;
        $response->updatedAt = $website->updated_at ?
            $website->updated_at->toDateTime()->format(self::DATE_FORMAT)
            : null;
        $response->reactions = $website->reactions ? $website->reactions : new \stdClass();

        return $response;
    }

    /**
     * @param Website[] $websites
     * @return WebsiteResponse[]
     */
    public function createList(array $websites): array
    {
        $response = [];

        foreach ($websites as $website) {
            $response[] = $this->createOne($website);
        }

        return $response;
    }
}
