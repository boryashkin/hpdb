<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Feed\Actions;

use App\Common\Abstracts\BaseAction;
use App\Common\Dto\WebFeed\WebFeedSearchQuery;
use App\Common\Repositories\WebFeedRepository;
use App\Web\Api\V1\Feed\Builders\WebFeed\WebFeedResponseBuilder;
use Elasticsearch\Client;
use OpenApi\Annotations as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @OA\Get(
 *     path="/api/v1/feed",
 *     tags={"feed"},
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Page number: 30 items per page",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64",
 *             minimum=1,
 *             maximum=100
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="lang",
 *         in="query",
 *         description="Language of items",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             enum={"en", "ru", "cn", "fr"}
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="preview",
 *         in="query",
 *         description="Get short descriptions",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64",
 *             default=0,
 *             minimum=0,
 *             maximum=1
 *         )
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="News feed",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 ref="#/components/schemas/WebFeedResponseItem"
 *             )
 *         )
 *     )
 * )
 */
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
