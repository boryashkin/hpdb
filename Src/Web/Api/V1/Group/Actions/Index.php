<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Group\Actions;

use App\Common\Abstracts\BaseAction;
use App\Common\Models\WebsiteGroup;
use App\Web\Api\V1\Group\Builders\GroupResponseBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @OA\Get (
 *     path="/api/v1/group",
 *     tags={"group"},
 *     @OA\Parameter(
 *         name="name",
 *         in="query",
 *         description="Name part to find",
 *         required=false,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
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
 *     @OA\Response(
 *         response="200",
 *         description="Groups list",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 ref="#/components/schemas/GroupResponse"
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
        $name = isset($params['name']) && \is_string($params['name']) ? $params['name'] : null;
        $page = isset($params['page']) && \is_numeric($params['page']) ? (int)$params['page'] : 0;
        $page--;

        $responseBuilder = new GroupResponseBuilder();
        $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
        $groups = WebsiteGroup::query();
        if ($name) {
            $groups = $groups->where('name', 'like', "%{$name}%");
        }
        $groups = $groups->where('is_deleted', '=', false)
            ->limit(10)
            ->offset($page < 0 ? 0 : $page * 10)->get()->all();
        /** @var WebsiteGroup[] $groups */

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($responseBuilder->createList($groups)));

        return $response;
    }
}
