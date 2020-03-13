<?php

namespace App\Web\Actions\Web;

use App\Common\Abstracts\BaseAction;
use Jenssegers\Mongodb\Collection;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection as MongoCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        /** @var RedisAdapter $redis */
        $redis = $this->getContainer()->get(CONTAINER_CONFIG_REDIS_CACHE);
        $container = $this->getContainer();

        $reactions = $redis->get('mainTopReactions', function (ItemInterface $item) use ($container) {
            $item->expiresAfter(60);

            /**
             * @var MongoCollection $c
             */
            $mongo = $container->get(CONTAINER_CONFIG_MONGO);
            $c = $mongo->getCollection('websiteReaction');

            return $this->getTopReactions($c);
        });

        $newWebsites = $redis->get('mainNewWebsites', function (ItemInterface $item) use ($container) {
            $item->expiresAfter(60);

            /**
             * @var MongoCollection $c
             */
            $mongo = $container->get(CONTAINER_CONFIG_MONGO);
            $wc = $mongo->getCollection('website');
            $c = $mongo->getCollection('websiteReaction');

            return $this->getNewWebsites($wc, $c);
        });
        $websiteGroups = $redis->get('mainWebsiteGroups', function (ItemInterface $item) use ($container) {
            $item->expiresAfter(60);

            /**
             * @var MongoCollection $c
             */
            $mongo = $container->get(CONTAINER_CONFIG_MONGO);
            $gc = $mongo->getCollection('websiteGroup');

            return $this->getWebsiteGroups($gc);
        });

        return $this->getView()->render($response, 'web/index.html', [
            'reactions' => $reactions,
            'newWebsites' => $newWebsites,
            'websiteGroups' => $websiteGroups,
        ]);
    }

    private function getTopReactions(Collection $c)
    {
        $reactions = $c->aggregate([
            //todo: filter out ip duplicates
            [
                '$group' => [
                    '_id' => ['website_id' => '$website_id', 'reaction' => '$reaction'],
                    'count' => ['$sum' => 1],
                ],
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
            ['$sort' => ['count' => -1]],
            ['$limit' => 10],
        ]);

        $result = [];
        foreach ($reactions as $reaction) {
            //damn you laravel
            $result[] = [
                'profile_id' => (string)$reaction->website->_id,
                'homepage' => \str_replace(['http://', 'https://'], '', $reaction->website->homepage),
                'reaction' => $reaction->_id->reaction,
                'title' => $reaction->website->content->title ? \substr(trim($reaction->website->content->title ?? ''), 0, 50) : 'No description yet',
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
                    ],
                ],
            ]);

            $result[] = [
                'profile_id' => (string)$website->_id,
                'homepage' => \str_replace(['http://', 'https://'], '', $website->homepage),
                'reactions' => $reactions,
                'title' => $website->content->title ? \mb_substr(trim($website->content->title ?? ''), 0, 50) : 'No description yet',
            ];
        }

        return $result;
    }

    private function getWebsiteGroups(Collection $websiteGroupCollection): array
    {
        /** @var MongoCollection $websiteGroupCollection */
        return $websiteGroupCollection->find(
            [
                'is_deleted' => false,
                'show_on_main' => true,
            ],
            [
                'limit' => 5,
            ]
        )->toArray();
    }
}
