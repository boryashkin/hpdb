<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Group\Actions;

use App\Common\Abstracts\BaseAction;
use App\Common\Exceptions\InvalidUrlException;
use App\Common\Repositories\WebsiteGroupRepository;
use App\Common\ValueObjects\Url;
use App\Web\Api\V1\Group\Builders\GroupResponseBuilder;
use App\Web\Api\V1\Group\Requests\GroupMutationRequest;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\ServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\SlimException;

/**
 * @OA\Patch(
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
 *     @OA\RequestBody(
 *         description="Group editing",
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/GroupMutationRequest")
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Group updated",
 *         @OA\JsonContent(ref="#/components/schemas/GroupResponse")
 *     ),
 *     @OA\Response(
 *         response="400",
 *         description="Validation errors",
 *         @OA\JsonContent()
 *     )
 * )
 */
class Update extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getParsedBody();

        $groupMutation = new GroupMutationRequest();
        $groupMutation->id = $request->getAttribute('id', null);
        $groupMutation->showOnMain = $params['showOnMain'] ?? null;
        $groupMutation->name = $params['name'] ?? null;
        $groupMutation->description = $params['description'] ?? null;
        $groupMutation->logo = $params['logo'] ?? null;

        try {
            $id = new ObjectId($groupMutation->id);
        } catch (\Exception $e) {
            $response->getBody()->write('{"errors": ["Id is not valid"]}');
            $response = $response->withStatus(400);

            throw new SlimException($request, $response);
        }
        if (!\is_bool($groupMutation->showOnMain)) {
            $response->getBody()->write('{"errors": ["showOnMain must be bool"]}');
            $response = $response->withStatus(400);

            throw new SlimException($request, $response);
        }
        if (isset($groupMutation->name) && !\is_string($groupMutation->name)) {
            $response->getBody()->write('{"errors": ["name must be string"]}');
            $response = $response->withStatus(400);

            throw new SlimException($request, $response);
        }
        if (isset($groupMutation->description) && !\is_string($groupMutation->description)) {
            $response->getBody()->write('{"errors": ["description must be string"]}');
            $response = $response->withStatus(400);

            throw new SlimException($request, $response);
        }
        if (isset($groupMutation->logo)) {
            if (!\is_string($groupMutation->logo)) {
                $response->getBody()->write('{"errors": ["url must be string"]}');
                $response = $response->withStatus(400);

                throw new SlimException($request, $response);
            }
            try {
                $logoUrl = new Url($groupMutation->logo);
            } catch (InvalidUrlException $e) {
                $response->getBody()->write('{"errors": ["url is not invalid"]}');
                $response = $response->withStatus(400);

                throw new SlimException($request, $response);
            }
        }
        $repo = new WebsiteGroupRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO));
        $group = $repo->getOneById($id);
        if (!$group) {
            $response->getBody()->write('{"errors": ["Not Found"]}');
            $response = $response->withStatus(404, 'Not Found');

            throw new SlimException($request, $response);
        }

        try {
            $group->fill([
                'show_on_main' => $groupMutation->showOnMain,
                'name' => $groupMutation->name ?? $group->name,
                'description' => $groupMutation->description ?? $group->description,
                'logo' => $groupMutation->logo ?? $group->logo,
            ]);
            $repo->save($group);
        } catch (ServerException $e) {
            $response->getBody()->write('{"errors": ["Slug may be already exists"]}');
            $response = $response->withStatus(400);

            throw new SlimException($request, $response);
        }
        $group = $repo->getOneById($id);
        $responseBuilder = new GroupResponseBuilder();

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($responseBuilder->createOne($group)));

        return $response;
    }
}
