<?php

namespace App\Common\Repositories;

use App\Common\Models\WebsiteIndexHistory;
use Illuminate\Database\Eloquent\Model;
use MongoDB\BSON\ObjectId;

class WebsiteIndexHistoryRepository extends AbstractMongoRepository
{
    /**
     * @return Model|WebsiteIndexHistory
     */
    public function getOne(ObjectId $id)
    {
        return WebsiteIndexHistory::query()
            ->where('_id', '=', $id)
            ->first();
    }

    public function getLastByWebsiteId(ObjectId $websiteId): ?WebsiteIndexHistory
    {
        $query = WebsiteIndexHistory::query()->orderBy('_id', 'desc')
            ->where('website_id', '=', $websiteId)
            ->limit(1);

        return $query->get()->first();
    }

    public function save(WebsiteIndexHistory $websiteHistory): bool
    {
        return $websiteHistory->save();
    }
}
