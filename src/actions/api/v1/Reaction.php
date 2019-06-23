<?php
namespace app\actions\api\v1;

use app\abstracts\BaseAction;
use app\models\WebsiteReaction;
use app\modules\web\ProfileRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\NotFoundException;

/**
 * Add a reaction to a website
 */
class Reaction extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getParsedBody();
        $page = isset($params['profile_id']) && \is_numeric($params['profile_id']) ? (int)$params['profile_id'] : -1;
        $reactionTag = isset($params['reaction']) && \is_string($params['reaction']) ? (string)$params['reaction'] : null;

        $repo = new ProfileRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO));
        $website = $repo->getOne($page);
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
