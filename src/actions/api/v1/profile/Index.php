<?php

namespace app\actions\api\v1\profile;

use app\abstracts\BaseAction;
use app\modules\web\ProfileRepository;
use MongoDB\BSON\ObjectId;
use MongoDB\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();
        $query = isset($params['query']) && \is_string($params['query']) ? $params['query'] : null;
        $page = isset($params['page']) && \is_numeric($params['page']) ? (int)$params['page'] : 0;
        $group = isset($params['group']) && \is_string($params['group']) ? $params['group'] : null;
        $limit = isset($params['limit']) && \is_numeric($params['limit']) ? $params['limit'] : 30;

        try {
            if ($group) {
                $group = new ObjectId($group);
            } else {
                $group = null;
            }
        } catch (InvalidArgumentException | \Exception $e) {
            $group = null;
        }

        $repo = new ProfileRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO));
        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($repo->getList($page, $query, $group, $limit)));

        return $response;
    }
}
