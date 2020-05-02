<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Profile\Builders;

use App\Common\Models\Website;
use App\Web\Api\V1\Profile\Responses\WebsiteLightResponse;

class WebsiteLightResponseBuilder
{
    public function createList(array $websites): array
    {
        $response = [];

        foreach ($websites as $website) {
            $response[] = $this->createOne($website);
        }

        return $response;
    }

    public function createOne(Website $website): WebsiteLightResponse
    {
        $response = new WebsiteLightResponse();
        $response->homepage = $website->homepage;
        $response->id = (string)$website->_id;
        $response->description = $website->content->description ?? 'No description';

        return $response;
    }
}
