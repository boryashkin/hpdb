<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Profile\Builders;

use App\Web\Api\V1\Profile\Responses\WebsiteLightResponse;

class WebsiteLightResponseBuilder
{
    /** @deprecated */
    public function createOneFromArray(array $aggregatedData): WebsiteLightResponse
    {
        $response = new WebsiteLightResponse();
        $response->description = $aggregatedData['description'] ?? null;
        $response->homepage = $aggregatedData['homepage'] ?? null;
        $response->profile_id = $aggregatedData['profile_id'] ?? null;

        return $response;
    }

    /**
     * @param array $multiple
     * @return WebsiteLightResponse[]
     * @deprecated
     */
    public function createFromArray(array $multiple): array
    {
        $response = [];

        foreach ($multiple as $one) {
            $response[] = $this->createOneFromArray($one);
        }

        return $response;
    }
}
