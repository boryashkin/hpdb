<?php

declare(strict_types=1);

namespace App\Common\Repositories;

use App\Common\Models\Website;
use App\Common\Repositories\Filters\WebsiteFilter;
use App\Common\ValueObjects\Url;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\LazyCollection;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\Collection as MongoCollection;

class ProfileRepository
{
    private $connection;

    /**
     * This is a hack to get mongodb work.
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param WebsiteFilter $websiteFilter
     * @return Website[]
     */
    public function find(WebsiteFilter $websiteFilter): array
    {
        $query = Website::query();

        if ($websiteFilter->homepageLike) {
            $query->where('homepage', 'regex', new Regex($websiteFilter->homepageLike, 'i'));
        }
        if ($websiteFilter->group) {
            $query->where('groups', 'elemmatch', ['$in' => [$websiteFilter->group]]);
        }
        if ($websiteFilter->fromId) {
            if ($websiteFilter->sortDirection && $websiteFilter->sortDirection === $websiteFilter::SORT_DESC) {
                $query->where('_id', '<', $websiteFilter->fromId);
            } else {
                $query->where('_id', '>', $websiteFilter->fromId);
            }
        }
        if ($websiteFilter->getLimit()) {
            $query->limit($websiteFilter->getLimit());
        }
        if ($websiteFilter->sortDirection) {
            $query->orderBy($websiteFilter->sortField, $websiteFilter->sortDirection);
        }

        return $query->get()->all();
    }

    public function getList(int $page, $query = null, ObjectId $groupId = null, int $limit = 30)
    {
        if (isset($query)) {
            $query = \strip_tags($query);
            $query = \trim($query);
        }
        if ($page <= 0) {
            $page = 1;
        }

        $from = ($page - 1) * $limit;
        $websiteCollection = $this->connection->getCollection('website');

        /** @var MongoCollection $websiteCollection */
        $aggQuery = [
            ['$sort' => ['created_at' => -1]],
            ['$limit' => $from + $limit],
            ['$skip' => $from],
            ['$project' => [
                '_id' => 0,
                'profile_id' => '$_id',
                'homepage' => '$homepage',
                'description' => '$content.description',
            ]],
        ];
        if (isset($query)) {
            \array_unshift($aggQuery, [
                '$match' => [
                    'homepage' => ['$regex' => new Regex($query, 'i')],
                ],
            ]);
        }
        if ($groupId) {
            \array_unshift($aggQuery, [
                '$match' => [
                    'groups' => $groupId,
                ],
            ]);
        }
        $websites = $websiteCollection->aggregate($aggQuery);
        $websitesArr = [];
        foreach ($websites as $website) {
            $website->profile_id = (string)$website->profile_id;
            $websitesArr[] = (array)$website;
        }

        return $websitesArr;
    }

    public function getListByGroup(int $page, ObjectId $groupId)
    {
        if ($page <= 0) {
            $page = 1;
        }
        $step = 10;
        $from = ($page - 1) * $step;

        $req = Website::query()
            ->where('groups', 'in', $groupId)
            ->offset($from)->limit($step);
        $websites = $req->get();

        return $websites->toArray();
    }

    /**
     * @return Website
     */
    public function getOneById(ObjectId $id)
    {
        return Website::query()
            ->where('_id', '=', $id)
            ->get()->all()[0];
    }

    /**
     * @return Website
     */
    public function getOneByProfileId(int $profileId)
    {
        return Website::query()
            ->where('profile_id', '=', $profileId)
            ->get()->all()[0];
    }

    public function getFirstOneByUrl(Url $url): ?Website
    {
        $q = Website::query()->where('homepage', '=', $url->getNormalized());

        return $q->first();
    }

    public function save(Website $website): bool
    {
        return $website->save();
    }

    public function getAllCursor(
        ?ObjectId $startingFromId = null,
        int $sortDirection = SORT_ASC,
        int $limit = null
    ): LazyCollection
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
        if ($limit > 0) {
            $query->limit($limit);
        }
        if ($startingFromId) {
            $query->where('_id', $idOperator, $startingFromId);
        }
        $query->orderBy('_id', $sort);

        return $query->get()->lazy();
    }
}
