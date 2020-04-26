<?php

namespace App\Common\Repositories;

use App\Common\Models\Website;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\ServerException;

/**
 * @todo: move repo to a Common namespace, make a concrete Service instead if needed
 */
class WebsiteRepository extends AbstractMongoRepository
{
    /**
     * @return Model|Website
     */
    public function getOne(ObjectId $id)
    {
        return Website::query()
            ->where('_id', '=', $id)
            ->first();
    }

    /**
     * @return null|Model|object|Website
     */
    public function getOneByHomepage(string $url)
    {
        return Website::query()
            ->where('homepage', '=', $url)
            ->limit(1)
            ->first();
    }

    /**
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

    public function getAllCursor(?ObjectId $startingFromId = null, int $sortDirection = SORT_ASC): LazyCollection
    {
        $query = Website::query()
            ->useWritePdo();
        if ($sortDirection === SORT_ASC) {
            $sort = 'asc';
            $idOperator = '>=';
        } else {
            $sort = 'desc';
            $idOperator = '<=';
        }
        if ($startingFromId) {
            $query->where('_id', $idOperator, $startingFromId);
        }
        $query->orderBy('_id', $sort);

        return $query->get()->lazy();
    }
}
