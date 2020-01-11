<?php
namespace app\modules\web;

use app\models\Website;
use app\valueObjects\Url;
use Illuminate\Database\ConnectionInterface;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\Collection as MongoCollection;

class ProfileRepository
{
    private $connection;

    /**
     * This is a hack to get mongodb work
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function getListLightweight(int $page, $query = null)
    {
        $q = strip_tags($query);
        if ($page <= 0) {
            $page = 1;
        }
        $step = 10;
        $from = ($page - 1) * $step;

        $req = Website::query()
            ->select(['profile_id', 'homepage'])
            ->offset($from)->limit($step);
        if ($query) {
            $req->where('homepage', 'like', '%' . $q . '%');
        }
        $websites = $req->get();

        return $websites->toArray();
    }

    public function getList(int $page, $query = null)
    {
        if (isset($query)) {
            $query = \strip_tags($query);
            $query = \trim($query);
        }
        if ($page <= 0) {
            $page = 1;
        }
        $step = 30;
        $from = ($page - 1) * $step;
        $websiteCollection = $this->connection->getCollection('website');

        /** @var MongoCollection $websiteCollection */
        $aggQuery = [
            ['$sort' => ['created_at' => -1]],
            ['$limit' => $from + $step],
            ['$skip' => $from],
            [
                '$lookup' => [
                    'from' => 'websiteContent',
                    'as' => 'content',
                    'let' => ['wid' => '$_id'],
                    'pipeline' => [
                        [
                            '$match' => [
                                '$expr' => ['$eq' => ['$$wid', '$website_id']],
                            ],
                        ],
                        ['$sort' => ['created_at' => -1]],
                        ['$limit' => 1],
                    ],
                ],
            ],
            ['$unwind' => '$content'],
            ['$project' => [
                '_id' => 0,
                'profile_id' => '$_id',
                'homepage' => '$homepage',
                'description' => '$content.description'
            ]],
        ];
        if (isset($query)) {
            \array_unshift($aggQuery, [
                '$match' => [
                    'homepage' => ['$regex' =>  new Regex($query, "i")]
                ]
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

    /**
     * @param ObjectId $id
     * @return Website
     */
    public function getOneById(ObjectId $id)
    {
        return Website::query()
            ->where('_id', '=', $id)
            ->get()->all()[0];
    }


    /**
     * @param int $profileId
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
        $websiteUrl = (string)$url;
        $httpUrl = \str_replace('https://', 'http://', $websiteUrl);
        $httpsUrl = \str_replace('http://', 'https://', $websiteUrl);
        $q = Website::query()->where('homepage', '=', $httpsUrl)
            ->orWhere('homepage', '=', $httpUrl)
            ->orWhere('homepage', '=', $httpsUrl . '/')
            ->orWhere('homepage', '=', $httpUrl . '/');

        return $q->first();
    }
}
