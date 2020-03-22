<?php

declare(strict_types=1);

namespace App\Web\Actions\Api\V1\Feed;

use App\Common\Abstracts\BaseAction;
use App\Common\Dto\WebFeed\WebFeedSearchQuery;
use App\Common\Repositories\WebFeedRepository;
use App\Web\Services\WebFeed\WebFeedResponseBuilder;
use Elasticsearch\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();
        $page = isset($params['page']) && \is_numeric($params['page']) ? (int)$params['page'] : 0;
        $lang = isset($params['lang']) && \is_string($params['lang']) ? $params['lang'] : null;
        $previewResponse = isset($params['preview']) ? (int)$params['preview'] : 0;
        $page = $page > 0 ? $page - 1 : $page;

        /** @var Client $client */
        $client = $this->getContainer()->get(CONTAINER_CONFIG_ELASTIC);
        $repo = new WebFeedRepository($client);
        $query = new WebFeedSearchQuery();
        if ($lang) {
            $query->setFilter([
                'term' => ['language' => $lang],
            ]);
        }
        $query->setSort('date', $query::SORT_DESC);
        $query->setSize(30);
        $query->setFrom($page * $query->setSize());

        $feed = $repo->getSearchResults($query, !$previewResponse);
        if ($previewResponse) {
            $builder = new WebFeedResponseBuilder();
            $feed = $builder->createList($feed ?? []);
        }

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($feed));

        return $response;
    }

}
