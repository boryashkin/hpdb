<?php
namespace app\actions\web;

use app\abstracts\BaseAction;
use app\models\Website;
use app\models\WebsiteContent;
use Jenssegers\Mongodb\Collection;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection as MongoCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $mongo = $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
        /**
         * @var MongoCollection $c
         */
        $wc = $mongo->getCollection('website');
        $c = $mongo->getCollection('websiteReaction');
        $reactions = $this->getTopReactions($c);
        $newWebsites = $this->getNewWebsites($wc, $c);

        return $this->getView()->render($response, 'web/index.html', [
            'reactions' => $reactions,
            'newWebsites' => $newWebsites,
        ]);
    }

    private function getTopReactions(Collection $c)
    {
        $reactions = $c->aggregate([
            //todo: filter out ip duplicates
            [
                '$group' => [
                    '_id' => ['website_id' => '$website_id', 'reaction' => '$reaction'],
                    'count' => ['$sum' => 1]
                ]
            ],
            [
                '$lookup' => [
                    'from' => 'website',
                    'localField' => '_id.website_id',
                    'foreignField' => '_id',
                    'as' => 'website',
                ],

            ],
            ['$unwind' => '$website'],
            [
                '$lookup' => [
                    'from' => 'websiteContent',
                    'as' => 'websiteContent',
                    'let' => ['wid' => '$_id.website_id'],
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
            ['$unwind' => '$websiteContent'],
            ['$sort' => ['count' => -1]],
            ['$limit' => 50],
        ]);

        $result = [];
        foreach ($reactions as $reaction) {
            //damn you laravel
            $result[] = [
                'profile_id' => $reaction->website->profile_id,
                'homepage' => $reaction->website->homepage,
                'reaction' => $reaction->_id->reaction,
                'title' => $reaction->websiteContent->title ? \substr(trim($reaction->websiteContent->title ?? ''), 0, 50) : 'No description yet',
                'count' => $reaction->count,
            ];
        }

        return $result;
    }

    private function getNewWebsites(Collection $websiteCollection, Collection $reactionCollection)
    {
        /** @var MongoCollection $websiteCollection */
        $websites = $websiteCollection->aggregate([
            ['$sort' => ['created_at' => -1]],
            ['$limit' => 5],
            [
                '$lookup' => [
                    'from' => 'websiteContent',
                    'localField' => '_id',
                    'foreignField' => 'website_id',
                    'as' => 'content',
                ],

            ],
            ['$unwind' => '$content'],
        ]);

        $result = [];
        foreach ($websites as $website) {
            $reactions = $reactionCollection->aggregate([
                [
                    '$match' => ['website_id' => new ObjectId($website->_id)],
                ],
                [
                    '$group' => [
                        '_id' => ['website_id' => '$website_id', 'reaction' => '$reaction'],
                        'count' => ['$sum' => 1],
                    ]
                ],
            ]);

            $result[] = [
                'profile_id' => $website->profile_id,
                'homepage' => $website->homepage,
                'reactions' => $reactions,
                'title' => $website->content->title ? \substr(trim($website->content->title ?? ''), 0, 50) : 'No description yet',
            ];
        }

        return $result;
    }
}
