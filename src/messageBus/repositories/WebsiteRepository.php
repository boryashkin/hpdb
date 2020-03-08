<?php

namespace app\messageBus\repositories;

use app\models\Website;
use Illuminate\Database\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\ServerException;

/**
 * @todo: move repo to a Common namespace, make a concrete Service instead if needed
 */
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

    /**
     * @param string $url
     * @return Website|Model|object|null
     */
    public function getOneByHomepage(string $url)
    {
        return Website::query()
            ->where('homepage', '=', $url)
            ->limit(1)
            ->first();
    }

    /**
     * @param Website $profile
     * @param ObjectId $groupId
     * @return bool
     *
     * @throws ServerException
     * @todo: move to ProfileService (create one)
     */
    public static function addGroupIdAndSave(Website $profile, ObjectId $groupId): bool
    {
        $saved = false;
        if (!$profile->groups || (\is_array($profile->groups) && !\in_array($groupId, $profile->groups))) {
            $profile->groups = \array_merge($profile->groups ?? [], [$groupId]);
            $saved = $profile->update(['groups' => $profile->groups]);
        }

        return $saved;
    }

    public function save(Website $website): bool
    {
        return $website->save();
    }
}
