<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Reaction\Actions;

use App\Common\Abstracts\BaseAction;
use App\Common\CommonProvider;
use App\Common\Dto\Website\WebsiteReactionDto;
use App\Common\Repositories\ProfileRepository;
use App\Common\Repositories\ReactionRepository;
use App\Common\Services\Website\ProfileReactionService;
use App\Common\Services\Website\WebsiteService;
use App\Web\Api\V1\Reaction\Builders\ReactionResponseBuilder;
use App\Web\Api\V1\Reaction\Requests\ReactionCreateRequest;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\NotFoundException;

/**
 * @OA\Post(
 *     path="/api/v1/reaction",
 *     tags={"reaction"},
 *     @OA\RequestBody(
 *         description="Profile creation",
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/ReactionCreateRequest")
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Profile created",
 *         @OA\JsonContent(ref="#/components/schemas/ReactionResponse")
 *     ),
 *     @OA\Response(
 *         response="404",
 *         description="Website/reaction not found",
 *         @OA\JsonContent()
 *     )
 * )
 */
class Create extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getParsedBody();
        $reactionCreate = new ReactionCreateRequest();
        if (!isset($params['websiteId']) || !\is_string($params['websiteId'])) {
            throw new NotFoundException($request, $response);
        }
        $reactionCreate->websiteId = $params['websiteId'];
        $reactionCreate->reaction = $params['reaction'];

        try {
            $id = new ObjectId($reactionCreate->websiteId);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundException($request, $response);
        }
        $reactionTag = isset($reactionCreate->reaction) && \is_string($reactionCreate->reaction)
            ? (string)$reactionCreate->reaction : null;
        $userAgent = $request->getHeader('user-agent');
        if (\count($userAgent)) {
            $userAgent = \current($userAgent);
        }

        $mongo = $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
        $reactionService = new ProfileReactionService(
            new ReactionRepository($mongo),
            new WebsiteService(new ProfileRepository($mongo))
        );
        $responseBuilder = new ReactionResponseBuilder();
        $repo = new ProfileRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO));
        $website = $repo->getOneById($id);
        if (!$website || !$reactionTag) {
            throw new NotFoundException($request, $response);
        }

        $reactionDto = new WebsiteReactionDto();
        $reactionDto->reaction = $reactionTag;
        $reactionDto->websiteId = $website->_id;
        $reactionDto->ip = $request->getServerParams()['REMOTE_ADDR'];
        $reactionDto->userAgent = $userAgent;
        $userId = CommonProvider::getAuthService($this->getContainer())->getCurrentUserId();
        if ($userId) {
            $reactionDto->user_id = $userId;
        }
        $reaction = $reactionService->addReaction($website, $reactionDto);


        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($responseBuilder->createOne($reaction)));

        return $response;
    }
}
