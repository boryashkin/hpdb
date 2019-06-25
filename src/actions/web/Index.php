<?php
namespace app\actions\web;

use app\abstracts\BaseAction;
use app\models\Website;
use app\models\WebsiteContent;
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
        $c = $mongo->getCollection('websiteReaction');
        $reactions = $c->aggregate([
            //todo: filter out ip duplicates
            [
                '$group' => [
                    '_id' => ['website_id' => '$website_id', 'reaction' => '$reaction'],
                    'count' => ['$sum' => 1]
                ]
            ],
            ['$sort' => ['count' => -1]],
            ['$limit' => 50],
        ]);

        $result = [];
        foreach ($reactions as $reaction) {
            $website = Website::query()->where('_id', '=', $reaction->_id->website_id)->first();
            if (!$website) {
                continue;
            }
            //damn you laravel
            $content = WebsiteContent::query()
                ->where('website_id', '=', new ObjectId($website->_id))
                ->orderBy('created_at', -1)->first();

            $result[] = [
                'profile_id' => $website->profile_id,
                'homepage' => $website->homepage,
                'reaction' => $reaction->_id->reaction,
                'title' => $content->title ? \substr(trim($content->title ?? ''), 0, 50) : 'No description yet',
                'count' => $reaction->count,
            ];
        }

        return $this->getView()->render($response, 'web/index.html', [
            'reactions' => $result,
        ]);
    }
}
