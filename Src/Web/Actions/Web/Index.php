<?php

namespace App\Web\Actions\Web;

use App\Common\Abstracts\BaseAction;
use App\Common\Dto\WebFeed\WebFeedSearchQuery;
use App\Common\Repositories\WebFeedRepository;
use App\Web\Services\WebFeed\WebFeedResponseBuilder;
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
        $feed = $redis->get('mainFeed', function (ItemInterface $item) use ($container) {
            $item->expiresAfter(60);

            $repo = new WebFeedRepository($container->get(CONTAINER_CONFIG_ELASTIC));
            $lang = 'ru';
            $query = (new WebFeedSearchQuery())
                ->setFilter(['term' => ['language' => $lang]])
                ->setSort('date', WebFeedSearchQuery::SORT_DESC)
                ->setSize(5);
            $builder = new WebFeedResponseBuilder();

            return $builder->createList($repo->getSearchResults($query, false) ?? []);
        });


        return $this->getView()->render($response, 'web/index.html', [
            'webFeed' => $feed,
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
            $title = $reaction->website->content->title ?: $reaction->website->content->description;
            $result[] = [
                'profile_id' => (string)$reaction->website->_id,
                'homepage' => $reaction->website->homepage,
                'reaction' => $reaction->_id->reaction,
                'title' => $title ? \substr(trim($title), 0, 50) : 'No description yet',
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

            $title = $website->content->title ?: $website->content->description;
            $result[] = [
                'profile_id' => (string)$website->_id,
                'homepage' => $website->homepage,
                'reactions' => $reactions,
                'title' => $title ? \mb_substr(trim($title), 0, 50) : 'No description yet',
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
                'limit' => 20,
            ]
        )->toArray();
    }
}
