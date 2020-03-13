<?php

namespace App\Web\Actions\Api\V1\Reaction;

use App\Common\Abstracts\BaseAction;
use App\Common\Models\Website;
use Jenssegers\Mongodb\Connection;
use MongoDB\Collection as MongoCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        /** @var Connection $mongo */
        $mongo = $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
        /**
         * @var MongoCollection $c
         */
        $c = $mongo->getCollection('websiteReaction');
        $reactions = $c->aggregate([
            ['$group' => [
                '_id' => ['website_id' => '$website_id', 'reaction' => '$reaction'],
                'count' => ['$sum' => 1],
            ]],
            ['$sort' => ['count' => -1]],
            ['$limit' => 50],
        ]);
        $result = [];
        foreach ($reactions as $reaction) {
            $website = Website::query()->where('_id', '=', $reaction->_id->website_id)->first();
            if (!$website) {
                continue;
            }
            $result[] = [
                'profile_id' => (string)$reaction->_id->website_id,
                'homepage' => $website->homepage,
                'reaction' => $reaction->_id->reaction,
                'count' => $reaction->count,
            ];
        }
        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($result));

        return $response;
    }
}
