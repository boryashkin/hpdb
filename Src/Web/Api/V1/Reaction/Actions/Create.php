<?php

namespace App\Web\Api\V1\Reaction\Actions;

use App\Common\Abstracts\BaseAction;
use App\Common\Dto\Website\WebsiteReactionDto;
use App\Common\Repositories\ProfileRepository;
use App\Common\Repositories\ReactionRepository;
use App\Common\Services\Website\ProfileReactionService;
use App\Common\Services\Website\WebsiteService;
use App\Web\Api\V1\Reaction\Builders\ReactionResponseBuilder;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\NotFoundException;

/**
 * Add a reaction to a website.
 */
class Create extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getParsedBody();
        if (!isset($params['profile_id']) || !\is_string($params['profile_id'])) {
            throw new NotFoundException($request, $response);
        }

        try {
            $id = new ObjectId($params['profile_id']);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundException($request, $response);
        }
        $reactionTag = isset($params['reaction']) && \is_string($params['reaction']) ? (string)$params['reaction'] : null;
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
        $reaction = $reactionService->addReaction($website, $reactionDto);


        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($responseBuilder->createOne($reaction)));

        return $response;
    }
}
