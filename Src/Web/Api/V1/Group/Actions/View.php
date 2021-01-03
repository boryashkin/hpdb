<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Group\Actions;

use App\Common\Abstracts\BaseAction;
use App\Common\CommonProvider;
use App\Web\Api\V1\Group\Builders\GroupResponseBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\NotFoundException;
use Slim\Exception\SlimException;

/**
 * @OA\Get (
 *     path="/api/v1/group/{slug}",
 *     tags={"group"},
 *     @OA\Parameter(
 *         name="slug",
 *         in="path",
 *         description="Group's slug",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             example="AIUyda8sdogaidAsiuhas7d6as9diu"
 *         )
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Group created",
 *         @OA\JsonContent(ref="#/components/schemas/GroupResponse")
 *     )
 * )
 */
class View extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $slug = $request->getAttribute('slug', null);

        if (!is_string($slug)) {
            throw new SlimException($request, $response);
        }

        $responseBuilder = new GroupResponseBuilder();

        $ws = CommonProvider::getWebsiteGroupService($this->getContainer());
        $group = $ws->getGroupBySlug($slug);
        if (!$group) {
            throw new NotFoundException($request, $response);
        }

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($responseBuilder->createOne($group)));

        return $response;
    }
}
