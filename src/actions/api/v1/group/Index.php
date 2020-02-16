<?php

namespace app\actions\api\v1\group;

use app\abstracts\BaseAction;
use app\models\WebsiteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();
        $name = isset($params['name']) && \is_string($params['name']) ? $params['name'] : null;
        $page = isset($params['page']) && \is_numeric($params['page']) ? (int)$params['page'] : 0;

        $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
        $groups = WebsiteGroup::query();
        if ($name) {
            $groups = $groups->where('name', 'like', "%$name%");
        }
        $groups = $groups->where('is_deleted', '=', false)
            ->limit(10)
            ->offset($page < 0 ? 0 : $page)->get();

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($groups));

        return $response;
    }
}

