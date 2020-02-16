<?php

namespace app\messageBus\repositories;

use app\models\Website;
use Illuminate\Database\Eloquent\Model;
use MongoDB\BSON\ObjectId;

class WebsiteRepository extends AbstractMongoRepository
{
    /**
     * @param ObjectId $id
     * @return Website|Model
     */
    public function getOne(ObjectId $id)
    {
        return Website::query()
            ->where('_id', '=', $id)
            ->first();
    }

    public function getOneByHomepage(string $url)
    {
        return Website::query()
            ->where('_id', '=', $url)
            ->limit(1)
            ->first();
    }

    public function save(Website $website): bool
    {
        return $website->save();
    }
}
