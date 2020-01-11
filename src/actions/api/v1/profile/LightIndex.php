<?php

namespace app\actions\api\v1\profile;

use app\abstracts\BaseAction;
use app\modules\web\ProfileRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LightIndex extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();
        $query = isset($params['query']) && \is_string($params['query']) ? $params['query'] : null;
        $page = isset($params['page']) && \is_numeric($params['page']) ? (int)$params['page'] : 0;

        $repo = new ProfileRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO));
        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($repo->getListLightweight($page, $query)));

        return $response;
    }
}
