<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Rpc\Actions;

use App\Common\Abstracts\BaseAction;
use App\Common\Models\WebsiteGroup;
use App\Common\Repositories\ProfileRepository;
use App\Common\Repositories\WebsiteRepository;
use App\Web\Api\V1\Rpc\Builders\RpcWebsiteResponseBuilder;
use App\Web\Api\V1\Rpc\Requests\AddWebsiteToGroupRequest;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\ServerException;
use OpenApi\Annotations as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\SlimException;

/**
 * @OA\Put(
 *     path="/api/v1/rpc/add-website-to-group",
 *     tags={"rpc"},
 *     @OA\RequestBody(
 *         description="Add website to group",
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/AddWebsiteToGroupRequest")
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Profile added into the group",
 *         @OA\JsonContent(ref="#/components/schemas/WebsiteGroupsResponse")
 *     ),
 *     @OA\Response(
 *         response="400",
 *         description="Validation errors",
 *         @OA\JsonContent()
 *     )
 * )
 */
class AddWebsiteToGroup extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getParsedBody();
        $addWebsiteRequest = new AddWebsiteToGroupRequest();
        $addWebsiteRequest->websiteId = isset($params['websiteId']) && \is_string($params['websiteId']) ? $params['websiteId'] : null;
        $addWebsiteRequest->groupId = isset($params['groupId']) && \is_string($params['groupId']) ? $params['groupId'] : null;
        if (!$addWebsiteRequest->websiteId || !$addWebsiteRequest->groupId) {
            throw new SlimException($request, $response);
        }

        try {
            $websiteId = new ObjectId($addWebsiteRequest->websiteId);
            $groupId = new ObjectId($addWebsiteRequest->groupId);
        } catch (InvalidArgumentException | \Exception $e) {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['errors' => ['website_id and group_id must be a mongoId']]));

            throw new SlimException($request, $response);
        }

        $repo = new ProfileRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO));
        $profile = $repo->getOneById($websiteId);
        $group = WebsiteGroup::query()->where('_id', '=', $groupId)->first();
        if (!$profile || !$group) {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['errors' => ['No website or group found']]));

            throw new SlimException($request, $response);
        }

        try {
            WebsiteRepository::addGroupIdAndSave($profile, $groupId);
        } catch (ServerException $e) {
            $response->getBody()->write('{"errors": ["failed to save"]}');
            $response = $response->withStatus(400);

            throw new SlimException($request, $response);
        }

        $responseBuilder = new RpcWebsiteResponseBuilder();

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($responseBuilder->createOneWebsiteGroupsResponse($profile)));

        return $response;
    }
}
