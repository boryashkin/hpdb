<?php

namespace App\Web\Actions\Api\V1;

use App\Common\Abstracts\BaseAction;
use App\Common\Models\WebsiteReaction;
use App\Common\Repositories\ProfileRepository;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\NotFoundException;

/**
 * Add a reaction to a website.
 */
class Reaction extends BaseAction
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

        $repo = new ProfileRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO));
        $website = $repo->getOneById($id);
        if (!$website || !$reactionTag) {
            throw new NotFoundException($request, $response);
        }
        $userAgent = $request->getHeader('user-agent');
        if (\count($userAgent)) {
            $userAgent = \current($userAgent);
        }
        $reaction = new WebsiteReaction();
        $reaction->website_id = $website->getAttributes()['_id'];
        $reaction->reaction = $reactionTag;
        $reaction->ip = $request->getServerParams()['REMOTE_ADDR'];
        $reaction->user_agent = $userAgent;

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($reaction->save()));

        return $response;
    }
}
