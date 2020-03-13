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

    public function save(WebsiteIndexHistory $websiteHistory): bool
    {
        return $websiteHistory->save();
    }
}
