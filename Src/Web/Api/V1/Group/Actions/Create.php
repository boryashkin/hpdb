<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Group\Actions;

use App\Common\Abstracts\BaseAction;
use App\Common\Models\WebsiteGroup;
use App\Web\Api\V1\Group\Builders\GroupResponseBuilder;
use App\Web\Api\V1\Group\Requests\GroupCreateRequest;
use MongoDB\Driver\Exception\ServerException;
use OpenApi\Annotations as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\SlimException;

/**
 * @OA\Post(
 *     path="/api/v1/group",
 *     tags={"group"},
 *     @OA\RequestBody(
 *         description="Group creation",
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/GroupCreateRequest")
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Group created",
 *         @OA\JsonContent(ref="#/components/schemas/GroupResponse")
 *     ),
 *     @OA\Response(
 *         response="400",
 *         description="Validation errors",
 *         @OA\JsonContent()
 *     )
 * )
 */
class Create extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getParsedBody();
        $groupCreate = new GroupCreateRequest();
        $groupCreate->name = isset($params['name']) && \is_string($params['name']) ? $params['name'] : null;
        $groupCreate->slug = isset($params['slug']) && \is_string($params['slug']) ? $params['slug'] : null;
        $groupCreate->description = isset($params['description']) && \is_string($params['description'])
            ? $params['description'] : null;
        $groupCreate->logo = isset($params['logo']) && \is_string($params['logo']) ? $params['logo'] : null;
        $groupCreate->showOnMain = isset($params['showOnMain']) && \is_bool($params['showOnMain'])
            ? $params['showOnMain'] : false;
        if (!$groupCreate->name || !$groupCreate->slug) {
            $response = $response->withStatus(400);
            $response->getBody()->write('{"errors": ["Slug and name fields are required"]}');

            throw new SlimException($request, $response);
        }
        if (!preg_match('/[a-zA-Z0-9\-]{0,20}/', $groupCreate->slug, $out) || $out[0] !== $groupCreate->slug) {
            $response->getBody()->write('{"errors": ["Slug should be an alphanumeric (en) string with \"-\" 20 symbols max"]}');
            $response = $response->withStatus(400);

            throw new SlimException($request, $response);
        }
        $websiteGroup = new WebsiteGroup();
        $websiteGroup->name = $groupCreate->name;
        $websiteGroup->slug = $groupCreate->slug;
        $websiteGroup->description = $groupCreate->description;
        $websiteGroup->logo = $groupCreate->logo;
        $websiteGroup->show_on_main = $groupCreate->showOnMain;
        $websiteGroup->is_deleted = false;

        $this->getContainer()->get(CONTAINER_CONFIG_MONGO);

        try {
            $websiteGroup->save();
        } catch (ServerException $e) {
            $response->getBody()->write('{"errors": ["Slug may be already exists"]}');
            $response = $response->withStatus(400);

            throw new SlimException($request, $response);
        }
        $responseBuilder = new GroupResponseBuilder();

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($responseBuilder->createOne($websiteGroup)));

        return $response;
    }
}
