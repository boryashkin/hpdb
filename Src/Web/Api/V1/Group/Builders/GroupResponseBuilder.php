<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Group\Builders;


use App\Common\Models\WebsiteGroup;
use App\Web\Api\V1\Group\Responses\GroupResponse;

class GroupResponseBuilder
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @param WebsiteGroup[] $groups
     * @return GroupResponse[]
     */
    public function createList(array $groups): array
    {
        $response = [];

        foreach ($groups as $group) {
            $response[] = $this->createOne($group);
        }

        return $response;
    }

    public function createOne(WebsiteGroup $group): GroupResponse
    {
        $response = new GroupResponse();
        $response->id = (string)$group->_id;
        $response->name = $group->name;
        $response->description = $group->description;
        $response->logo = $group->logo;
        $response->showOnMain = $group->show_on_main;
        $response->slug = $group->slug;
        $response->updatedAt = $group->updated_at ?
            $group->updated_at->toDateTime()->format(self::DATE_FORMAT)
            : null;

        return $response;
    }
}
