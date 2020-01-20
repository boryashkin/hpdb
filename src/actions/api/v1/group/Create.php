<?php

namespace app\actions\api\v1\group;

use app\abstracts\BaseAction;
use app\models\WebsiteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\SlimException;

class Create extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getParsedBody();
        $name = isset($params['name']) && \is_string($params['name']) ? $params['name'] : null;
        $description = isset($params['description']) && \is_string($params['description']) ? $params['description'] : null;
        $logo = isset($params['logo']) && \is_string($params['logo']) ? $params['logo'] : null;
        if (!$name) {
            throw new SlimException($request, $response);
        }
        $websiteGroup = new WebsiteGroup();
        $websiteGroup->name = $name;
        $websiteGroup->description = $description;
        $websiteGroup->logo = $logo;

        $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
        $websiteGroup->save();

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($websiteGroup));

        return $response;
    }
}

