<?php

declare(strict_types=1);

namespace app\messageBus\repositories;

use app\models\WebsiteGroup;
use MongoDB\BSON\ObjectId;

class WebsiteGroupRepository extends AbstractMongoRepository
{
    /**
     * @return null|object|WebsiteGroup
     */
    public function getOneById(ObjectId $id)
    {
        return WebsiteGroup::query()
            ->where('_id', '=', $id)
            ->limit(1)
            ->first();
    }

    /**
     * @return null|object|WebsiteGroup
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
