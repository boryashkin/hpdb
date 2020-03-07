<?php

namespace app\actions\api\v1\rpc;

use app\abstracts\BaseAction;
use app\messageBus\repositories\WebsiteRepository;
use app\models\WebsiteGroup;
use app\modules\web\ProfileRepository;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\ServerException;
use MongoDB\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\SlimException;

class AddWebsiteToGroup extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getParsedBody();
        $websiteId = isset($params['website_id']) && \is_string($params['website_id']) ? $params['website_id'] : null;
        $groupId = isset($params['group_id']) && \is_string($params['group_id']) ? $params['group_id'] : null;
        if (!$websiteId || !$groupId) {
            throw new SlimException($request, $response);
        }
        try {
            $websiteId = new ObjectId($websiteId);
            $groupId = new ObjectId($groupId);
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
            $saved = WebsiteRepository::addGroupIdAndSave($profile, $groupId);
        } catch (ServerException $e) {
            $response->getBody()->write('{"errors": ["failed to save"]}');
            $response = $response->withStatus(400);
            throw new SlimException($request, $response);
        }

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode(['saved' => $saved, 'website' => $profile]));

        return $response;
    }
}

