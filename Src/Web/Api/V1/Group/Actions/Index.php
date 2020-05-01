<?php

namespace App\Web\Api\V1\Group\Actions;

use App\Common\Abstracts\BaseAction;
use App\Common\Models\WebsiteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();
        $name = isset($params['name']) && \is_string($params['name']) ? $params['name'] : null;
        $page = isset($params['page']) && \is_numeric($params['page']) ? (int)$params['page'] : 0;
        $page--;

        $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
        $groups = WebsiteGroup::query();
        if ($name) {
            $groups = $groups->where('name', 'like', "%{$name}%");
        }
        $groups = $groups->where('is_deleted', '=', false)
            ->limit(10)
            ->offset($page < 0 ? 0 : $page * 10)->get();

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($groups));

        return $response;
    }
}
