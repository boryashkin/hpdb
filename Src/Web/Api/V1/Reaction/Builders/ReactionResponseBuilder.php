<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Reaction\Builders;

use App\Common\Models\WebsiteReaction;
use App\Web\Api\V1\Reaction\Responses\ReactionResponse;

class ReactionResponseBuilder
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public function createOne(WebsiteReaction $reaction): ReactionResponse
    {
        $response = new ReactionResponse();
        $response->websiteId = (string)$reaction->website_id;
        $response->reaction = $reaction->reaction;
        $response->createdAt = $reaction->created_at ?
            $reaction->created_at->toDateTime()->format(self::DATE_FORMAT)
            : null;

        return $response;
    }
}
