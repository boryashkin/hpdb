<?php

declare(strict_types=1);

namespace app\messageBus\repositories;

use app\models\WebsiteGroup;

class WebsiteGroupRepository extends AbstractMongoRepository
{
    /**
     * @param string $slug
     * @return WebsiteGroup|object|null
     */
    public function getOneBySlug(string $slug)
    {
        return WebsiteGroup::query()
            ->where('slug', '=', $slug)
            ->limit(1)
            ->first();
    }

    public function save(WebsiteGroup $websiteGroup): bool
    {
        return $websiteGroup->save();
    }
}
