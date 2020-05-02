<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Rpc\Builders;

use App\Common\Models\Website;
use App\Web\Api\V1\Rpc\Responses\WebsiteGroupsResponse;

class RpcWebsiteResponseBuilder
{
    public function createOneWebsiteGroupsResponse(Website $website): WebsiteGroupsResponse
    {
        $response = new WebsiteGroupsResponse();
        $response->id = (string)$website->_id;
        foreach ($website->groups as $groupId) {
            $response->groups[] = (string)$groupId;
        }

        return $response;
    }
}
