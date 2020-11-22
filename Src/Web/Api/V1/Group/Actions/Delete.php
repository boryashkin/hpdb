<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Group\Actions;

use App\Common\Abstracts\BaseAction;
use App\Common\Models\WebsiteGroup;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\NotFoundException;
use Slim\Exception\SlimException;

/**
 * @OA\Delete (
 *     path="/api/v1/group/{id}",
 *     tags={"group"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Group ID",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             example="5fa81efe60343c42e80b467f"
 *         )
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Group deleted",
 *         @OA\JsonContent(
 *             type="object"
 *         )
 *     )
 * )
 */
class Delete extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $id = $request->getAttribute('id', null);

        try {
            $id = new ObjectId($id);
        } catch (InvalidArgumentException $e) {
            throw new SlimException($request, $response);
        }
        $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
        $websiteGroup = WebsiteGroup::query()->find($id);
        if (!$websiteGroup) {
            throw new NotFoundException($request, $response);
        }
        $websiteGroup->is_deleted = true;
        $websiteGroup->save();

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode([]));

        return $response;
    }
}
